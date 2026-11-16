<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    // Envia como multipart (igual ao front-end), para o upload de imagem funcionar.
    private function createPost(array $data)
    {
        return $this->post('/api/posts', $data, ['Accept' => 'application/json']);
    }

    private function fakeImage(string $name = 'foto.jpg', int $kilobytes = 150): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'image/jpeg');
    }

    // Publicações

    public function test_user_can_create_a_text_only_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->createPost(['content' => 'Bom dia, Varanda!'])
            ->assertCreated()
            ->assertJsonPath('content', 'Bom dia, Varanda!')
            ->assertJsonPath('image_url', null)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('likes_count', 0)
            ->assertJsonPath('comments_count', 0);
    }

    public function test_user_can_create_an_image_only_post(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->createPost(['image' => $this->fakeImage()])->assertCreated();

        $post = Post::firstOrFail();
        $this->assertNull($post->content);
        Storage::disk('public')->assertExists($post->image);
        $response->assertJsonPath('image_url', Storage::disk('public')->url($post->image));
    }

    public function test_user_can_create_a_post_with_text_and_image(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->createPost(['content' => 'Olha esse pôr do sol', 'image' => $this->fakeImage()])
            ->assertCreated()
            ->assertJsonPath('content', 'Olha esse pôr do sol');

        Storage::disk('public')->assertExists(Post::firstOrFail()->image);
    }

    public function test_post_needs_text_or_image(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->createPost(['content' => '   '])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content', 'image']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_post_cannot_have_more_than_one_image(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->createPost([
            'content' => 'Duas fotos',
            'image' => [$this->fakeImage('a.jpg'), $this->fakeImage('b.jpg')],
        ])->assertJsonValidationErrors(['image']);

        $this->assertDatabaseCount('posts', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_image_must_have_a_valid_format_and_size(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->createPost(['image' => UploadedFile::fake()->create('video.mp4', 500, 'video/mp4')])
            ->assertJsonValidationErrors(['image']);

        $this->createPost(['image' => $this->fakeImage('grande.jpg', 2500)])
            ->assertJsonValidationErrors(['image']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_owner_can_edit_the_text_of_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['content' => 'Texto antigo']);
        Sanctum::actingAs($user);

        $this->putJson("/api/posts/{$post->id}", ['content' => 'Texto novo'])
            ->assertOk()
            ->assertJsonPath('content', 'Texto novo');
    }

    public function test_text_post_cannot_be_left_empty_when_edited(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->putJson("/api/posts/{$post->id}", ['content' => ''])
            ->assertJsonValidationErrors(['content']);
    }

    public function test_user_cannot_edit_someone_elses_post(): void
    {
        $post = Post::factory()->create(['content' => 'Original']);
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/posts/{$post->id}", ['content' => 'Alterado'])->assertForbidden();

        $this->assertSame('Original', $post->fresh()->content);
    }

    public function test_deleting_a_post_removes_its_image_likes_and_comments(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->createPost(['content' => 'Vai sumir', 'image' => $this->fakeImage()])->assertCreated();
        $post = Post::firstOrFail();
        $post->likes()->create(['user_id' => User::factory()->create()->id]);
        Comment::factory()->for($post)->create();

        $this->deleteJson("/api/posts/{$post->id}")->assertNoContent();

        $this->assertModelMissing($post);
        $this->assertDatabaseCount('likes', 0);
        $this->assertDatabaseCount('comments', 0);
        Storage::disk('public')->assertMissing($post->image);
    }

    public function test_user_cannot_delete_someone_elses_post(): void
    {
        $post = Post::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/posts/{$post->id}")->assertForbidden();

        $this->assertModelExists($post);
    }

    public function test_single_post_shows_author_and_counters(): void
    {
        $viewer = User::factory()->create();
        $post = Post::factory()->create();
        $post->likes()->create(['user_id' => $viewer->id]);
        Comment::factory()->count(2)->for($post)->create();
        Sanctum::actingAs($viewer);

        $this->getJson("/api/posts/{$post->id}")
            ->assertOk()
            ->assertJsonPath('user.id', $post->user_id)
            ->assertJsonPath('likes_count', 1)
            ->assertJsonPath('comments_count', 2)
            ->assertJsonPath('liked_by_me', true);
    }

    // Feed

    public function test_feed_shows_own_posts_and_posts_from_followed_users_newest_first(): void
    {
        $me = User::factory()->create();
        $friend = User::factory()->create();
        $stranger = User::factory()->create();
        $me->following()->attach($friend);

        $oldFriendPost = Post::factory()->for($friend)->create(['created_at' => now()->subDays(2)]);
        $myPost = Post::factory()->for($me)->create(['created_at' => now()->subDay()]);
        $newFriendPost = Post::factory()->for($friend)->create(['created_at' => now()->subHour()]);
        Post::factory()->for($stranger)->create();

        Sanctum::actingAs($me);

        $ids = array_column($this->getJson('/api/feed')->assertOk()->json('data'), 'id');

        $this->assertSame([$newFriendPost->id, $myPost->id, $oldFriendPost->id], $ids);
    }

    public function test_feed_is_paginated(): void
    {
        $me = User::factory()->create();
        Post::factory()->count(13)->for($me)->create();
        Sanctum::actingAs($me);

        $this->getJson('/api/feed')
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 2);

        $this->getJson('/api/feed?page=2')->assertJsonCount(3, 'data');
    }

    // Curtidas

    public function test_user_can_like_a_post_only_once(): void
    {
        $post = Post::factory()->create();
        $me = User::factory()->create();
        Sanctum::actingAs($me);

        $this->postJson("/api/posts/{$post->id}/like")
            ->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes_count', 1);

        $this->postJson("/api/posts/{$post->id}/like")->assertJsonPath('likes_count', 1);

        $this->assertDatabaseCount('likes', 1);
        $this->assertDatabaseHas('likes', ['user_id' => $me->id, 'post_id' => $post->id]);
    }

    public function test_like_always_belongs_to_the_authenticated_user(): void
    {
        $post = Post::factory()->create();
        $someoneElse = User::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/posts/{$post->id}/like", ['user_id' => $someoneElse->id])->assertOk();

        $this->assertDatabaseMissing('likes', ['user_id' => $someoneElse->id]);
    }

    public function test_removing_a_like_only_removes_my_like(): void
    {
        $post = Post::factory()->create();
        $me = User::factory()->create();
        $other = User::factory()->create();
        $post->likes()->create(['user_id' => $me->id]);
        $post->likes()->create(['user_id' => $other->id]);
        Sanctum::actingAs($me);

        $this->deleteJson("/api/posts/{$post->id}/like")
            ->assertOk()
            ->assertJsonPath('liked', false)
            ->assertJsonPath('likes_count', 1);

        $this->assertDatabaseHas('likes', ['user_id' => $other->id, 'post_id' => $post->id]);
    }
}
