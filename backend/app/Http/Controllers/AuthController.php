<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->merge([
            'username' => strtolower((string) $request->username),
            'email' => strtolower((string) $request->email),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-z0-9_]+$/', 'unique:users'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], [
            'username.regex' => 'O nome de usuário pode conter apenas letras minúsculas, números e _.',
            'username.unique' => 'Este nome de usuário já está em uso.',
            'email.unique' => 'Já existe uma conta com este e-mail.',
        ]);

        $user = User::create($data);

        return response()->json($this->session($user), 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.']);
        }

        return $this->session($user);
    }

    public function me(Request $request)
    {
        return $request->user()->loadCount(['posts', 'followers', 'following']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink(['email' => strtolower($request->email)]);

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        // Mesma resposta exista ou não a conta, para não revelar quais e-mails estão cadastrados.
        return ['message' => 'Se existir uma conta com este e-mail, enviaremos um link para redefinir a senha.'];
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');
        $credentials['email'] = strtolower($credentials['email']);

        $status = Password::reset($credentials, function (User $user, string $password) {
            $user->update(['password' => $password]);
            $user->tokens()->delete(); // encerra as sessões abertas com a senha antiga
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return ['message' => 'Senha redefinida.'];
    }

    // Resposta de login e cadastro: o token de acesso e os dados do usuário.
    private function session(User $user): array
    {
        return [
            'token' => $user->createToken('web')->plainTextToken,
            'user' => $user->loadCount(['posts', 'followers', 'following']),
        ];
    }
}
