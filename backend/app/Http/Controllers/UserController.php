<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Pesquisa por nome ou username. Sem termo de busca, lista quem entrou por último.
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $query = User::withFollowStatus($request->user());

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })->orderBy('name');
        } else {
            $query->where('id', '!=', $request->user()->id)->latest();
        }

        return $query->paginate(15)->withQueryString();
    }

    public function show(Request $request, User $user)
    {
        return User::withCount(['posts', 'followers', 'following'])
            ->withFollowStatus($request->user())
            ->findOrFail($user->id);
    }

    public function posts(Request $request, User $user)
    {
        return Post::where('user_id', $user->id)
            ->withViewerData($request->user())
            ->latest()
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function followers(Request $request, User $user)
    {
        return $user->followers()
            ->withFollowStatus($request->user())
            ->orderByDesc('follows.created_at')
            ->paginate(20);
    }

    public function following(Request $request, User $user)
    {
        return $user->following()
            ->withFollowStatus($request->user())
            ->orderByDesc('follows.created_at')
            ->paginate(20);
    }

    public function follow(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            abort(422, 'Você não pode seguir a si mesmo.');
        }

        // syncWithoutDetaching não cria uma segunda linha se a pessoa já for seguida.
        $request->user()->following()->syncWithoutDetaching([$user->id]);

        return ['following' => true, 'followers_count' => $user->followers()->count()];
    }

    public function unfollow(Request $request, User $user)
    {
        $request->user()->following()->detach($user->id);

        return ['following' => false, 'followers_count' => $user->followers()->count()];
    }

    // Atualiza o perfil de quem está logado. O username não pode ser alterado.
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'bio' => ['nullable', 'string', 'max:160'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.max' => 'A foto de perfil deve ter no máximo 2 MB.',
        ]);

        $user = $request->user();
        $oldPhoto = $user->profile_photo;

        $user->fill($request->only('name', 'bio'));

        if ($request->hasFile('photo')) {
            $user->profile_photo = $request->file('photo')->store('avatars', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $user->profile_photo = null;
        }

        $user->save();

        // Apaga a foto antiga do disco se ela foi trocada ou removida.
        if ($oldPhoto && $oldPhoto !== $user->profile_photo) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return $user->loadCount(['posts', 'followers', 'following']);
    }
}
