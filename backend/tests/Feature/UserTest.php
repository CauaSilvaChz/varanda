<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // Pesquisa e perfil

    public function test_search_finds_users_by_name_or_username(): void
    {
        $byName = User::factory()->create(['name' => 'Mariana Costa']);
        $byUsername = User::factory()->create(['name' => 'Pedro Lima', 'username' => 'costa_pedro']);
        User::factory()->create(['name' => 'João Silva']);
        Sanctum::actingAs(User::factory()->create(['name' => 'Visitante']));

        $ids = array_column($this->getJson('/api/users?q=costa')->assertOk()->json('data'), 'id');

        $this->assertEqualsCanonicalizing([$byName->id, $byUsername->id], $ids);
    }

    public function test_search_results_say_if_the_viewer_follows_each_user(): void
    {
        $me = User::factory()->create(['name' => 'Visitante']);
        $followed = User::factory()->create(['name' => 'Bia Ramos']);
        User::factory()->create(['name' => 'Bia Torres']);
        $me->following()->attach($followed);
        Sanctum::actingAs($me);

        $results = collect($this->getJson('/api/users?q=bia')->json('data'))->keyBy('id');

        $this->assertTrue($results[$followed->id]['is_following']);
        $this->assertCount(1, $results->where('is_following', true));
    }

    public function test_profile_shows_counters_and_hides_private_fields(): void
    {
        $me = User::factory()->create();
        $profile = User::factory()->create();
        User::factory()->count(2)->create()->each(fn ($fan) => $fan->following()->attach($profile));
        $me->following()->attach($profile);
        $profile->following()->attach($me);
        Post::factory()->count(4)->for($profile)->create();
        Sanctum::actingAs($me);

        $this->getJson("/api/users/{$profile->username}")
            ->assertOk()
            ->assertJsonPath('posts_count', 4)
            ->assertJsonPath('followers_count', 3)
            ->assertJsonPath('following_count', 1)
            ->assertJsonPath('is_following', true)
            ->assertJsonMissingPath('email')
            ->assertJsonMissingPath('password');
    }

    public function test_user_posts_are_listed_on_the_profile(): void
    {
        $profile = User::factory()->create();
        Post::factory()->count(3)->for($profile)->create();
        Post::factory()->count(2)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/users/{$profile->username}/posts")->assertJsonCount(3, 'data');
    }

    public function test_unknown_username_returns_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/users/nao_existe')->assertNotFound();
    }

    // Seguir

    public function test_user_can_follow_only_once(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($me);

        $this->postJson("/api/users/{$other->username}/follow")
            ->assertOk()
            ->assertJsonPath('following', true)
            ->assertJsonPath('followers_count', 1);

        $this->postJson("/api/users/{$other->username}/follow")->assertJsonPath('followers_count', 1);

        $this->assertDatabaseCount('follows', 1);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $me = User::factory()->create();
        Sanctum::actingAs($me);

        $this->postJson("/api/users/{$me->username}/follow")->assertUnprocessable();

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_user_can_unfollow(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $me->following()->attach($other);
        Sanctum::actingAs($me);

        $this->deleteJson("/api/users/{$other->username}/follow")
            ->assertOk()
            ->assertJsonPath('following', false)
            ->assertJsonPath('followers_count', 0);
    }

    public function test_followers_and_following_lists(): void
    {
        $me = User::factory()->create();
        $profile = User::factory()->create();
        $fan = User::factory()->create();
        $idol = User::factory()->create();

        $fan->following()->attach($profile);
        $profile->following()->attach($idol);
        $me->following()->attach($fan);
        Sanctum::actingAs($me);

        $this->getJson("/api/users/{$profile->username}/followers")
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $fan->id)
            ->assertJsonPath('data.0.is_following', true);

        $this->getJson("/api/users/{$profile->username}/following")
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $idol->id)
            ->assertJsonPath('data.0.is_following', false);
    }

    // Editar perfil

    // Mesmo formato usado pelo front-end: multipart com _method=PUT.
    private function updateProfile(array $data)
    {
        return $this->post('/api/profile', ['_method' => 'PUT', ...$data], ['Accept' => 'application/json']);
    }

    public function test_user_can_update_name_and_bio_but_not_username(): void
    {
        $user = User::factory()->create(['username' => 'ana']);
        $other = User::factory()->create(['name' => 'Outra Pessoa']);
        Sanctum::actingAs($user);

        $this->putJson('/api/profile', ['name' => 'Ana Paula', 'bio' => 'Gosto de café.', 'username' => 'outro', 'id' => $other->id])
            ->assertOk()
            ->assertJsonPath('name', 'Ana Paula')
            ->assertJsonPath('bio', 'Gosto de café.')
            ->assertJsonPath('username', 'ana');

        $this->assertSame('Outra Pessoa', $other->fresh()->name);
    }

    public function test_new_profile_photo_replaces_and_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->updateProfile(['name' => $user->name, 'photo' => UploadedFile::fake()->create('foto.jpg', 120, 'image/jpeg')])
            ->assertOk();
        $firstPhoto = $user->fresh()->profile_photo;
        Storage::disk('public')->assertExists($firstPhoto);

        $this->updateProfile(['name' => $user->name, 'photo' => UploadedFile::fake()->create('nova.png', 120, 'image/png')])
            ->assertJsonPath('profile_photo_url', Storage::disk('public')->url($user->fresh()->profile_photo));

        Storage::disk('public')->assertMissing($firstPhoto);
    }

    public function test_user_can_remove_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->updateProfile(['name' => $user->name, 'photo' => UploadedFile::fake()->create('foto.jpg', 120, 'image/jpeg')]);
        $photo = $user->fresh()->profile_photo;

        $this->updateProfile(['name' => $user->name, 'remove_photo' => '1'])
            ->assertOk()
            ->assertJsonPath('profile_photo_url', null);

        Storage::disk('public')->assertMissing($photo);
    }

    public function test_profile_photo_must_be_a_small_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $this->updateProfile(['name' => 'Ana', 'photo' => UploadedFile::fake()->create('curriculo.pdf', 100, 'application/pdf')])
            ->assertJsonValidationErrors(['photo']);

        $this->updateProfile(['name' => 'Ana', 'photo' => UploadedFile::fake()->create('enorme.jpg', 3000, 'image/jpeg')])
            ->assertJsonValidationErrors(['photo']);
    }
}
