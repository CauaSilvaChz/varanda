<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // O link do e-mail aponta para a página de redefinição do front-end, não para o Laravel.
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return config('app.frontend_url').'/redefinir-senha?'.http_build_query([
                'token' => $token,
                'email' => $user->email,
            ]);
        });
    }
}
