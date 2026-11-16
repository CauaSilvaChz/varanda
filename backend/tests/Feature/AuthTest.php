<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Clara',
            'username' => 'AnaClara',
            'email' => 'Ana@Example.com',
            'password' => 'senhaforte1',
            'password_confirmation' => 'senhaforte1',
        ], $overrides);
    }

    public function test_user_can_register_and_receives_a_token(): void
    {
        $this->postJson('/api/register', $this->registrationData())
            ->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'username']])
            ->assertJsonPath('user.username', 'anaclara');

        $user = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('senhaforte1', $user->password));
    }

    public function test_email_and_username_must_be_unique(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'username' => 'anaclara']);

        $this->postJson('/api/register', $this->registrationData())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'username']);
    }

    public function test_password_must_be_confirmed_and_contain_letters_and_numbers(): void
    {
        $this->postJson('/api/register', $this->registrationData(['password_confirmation' => 'outrasenha1']))
            ->assertJsonValidationErrors(['password']);

        $this->postJson('/api/register', $this->registrationData(['password' => 'somenteletras', 'password_confirmation' => 'somenteletras']))
            ->assertJsonValidationErrors(['password']);

        $this->postJson('/api/register', $this->registrationData(['password' => 'abc1', 'password_confirmation' => 'abc1']))
            ->assertJsonValidationErrors(['password']);
    }

    public function test_username_only_accepts_letters_numbers_and_underscore(): void
    {
        $this->postJson('/api/register', $this->registrationData(['username' => 'ana clara!']))
            ->assertJsonValidationErrors(['username']);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'senhaforte1']);

        $this->postJson('/api/login', ['email' => 'ANA@example.com', 'password' => 'senhaforte1'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'username']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'senhaforte1']);

        $this->postJson('/api/login', ['email' => 'ana@example.com', 'password' => 'senhaerrada1'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_guest_cannot_access_private_routes(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/feed')->assertUnauthorized();
        $this->postJson('/api/posts', ['content' => 'Olá'])->assertUnauthorized();
        $this->putJson('/api/profile', ['name' => 'Invasor'])->assertUnauthorized();
    }

    public function test_me_returns_the_authenticated_user_without_private_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonMissingPath('email')
            ->assertJsonMissingPath('password');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('web')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_reset_link_points_to_the_frontend(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;

            return str_starts_with($url, config('app.frontend_url').'/redefinir-senha?token=');
        });
    }

    public function test_unknown_email_receives_the_same_response(): void
    {
        Notification::fake();

        $this->postJson('/api/forgot-password', ['email' => 'ninguem@example.com'])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $user->createToken('web');

        $this->postJson('/api/reset-password', [
            'token' => Password::createToken($user),
            'email' => $user->email,
            'password' => 'novasenha9',
            'password_confirmation' => 'novasenha9',
        ])->assertOk();

        $this->assertTrue(Hash::check('novasenha9', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/reset-password', [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'novasenha9',
            'password_confirmation' => 'novasenha9',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }
}
