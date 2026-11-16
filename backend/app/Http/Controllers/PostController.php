<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Feed: minhas publicações e as de quem eu sigo, das mais novas para as mais antigas.
    public function feed(Request $request)
    {
        $user = $request->user();
        $followingIds = $user->following()->pluck('users.id');

        return Post::whereIn('user_id', $followingIds->push($user->id))
            ->withViewerData($user)
            ->latest()
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => ['nullable', 'required_without:image', 'string', 'max:1000'],
            'image' => ['nullable', 'required_without:content', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'content.required_without' => 'Escreva algo ou adicione uma imagem.',
            'image.required_without' => 'Escreva algo ou adicione uma imagem.',
            'image.max' => 'A imagem deve ter no máximo 2 MB.',
        ]);

        $post = $request->user()->posts()->create([
            'content' => $request->input('content'),
            'image' => $request->hasFile('image') ? $request->file('image')->store('posts', 'public') : null,
        ]);

        return response()->json($this->findForViewer($post->id, $request), 201);
    }

    public function show(Request $request, Post $post)
    {
        return $this->findForViewer($post->id, $request);
    }

    // Só o texto pode ser editado. Uma publicação sem imagem precisa continuar com texto.
    public function update(Request $request, Post $post)
    {
        if ($post->user()->isNot($request->user())) {
            abort(403, 'Você só pode editar suas próprias publicações.');
        }

        $request->validate(
            ['content' => [$post->image ? 'nullable' : 'required', 'string', 'max:1000']],
            ['content.required' => 'Uma publicação sem imagem precisa ter texto.']
        );

        $post->update(['content' => $request->input('content')]);

        return $this->findForViewer($post->id, $request);
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user()->isNot($request->user())) {
            abort(403, 'Você só pode excluir suas próprias publicações.');
        }

        // Curtidas e comentários são apagados pelo banco (ON DELETE CASCADE).
        // A imagem é apagada no evento "deleted" do model Post.
        $post->delete();

        return response()->noContent();
    }

    public function like(Request $request, Post $post)
    {
        // O usuário vem sempre do token, então ninguém curte em nome de outra pessoa.
        $post->likes()->firstOrCreate(['user_id' => $request->user()->id]);

        return ['liked' => true, 'likes_count' => $post->likes()->count()];
    }

    public function unlike(Request $request, Post $post)
    {
        $post->likes()->where('user_id', $request->user()->id)->delete();

        return ['liked' => false, 'likes_count' => $post->likes()->count()];
    }

    private function findForViewer(int $id, Request $request): Post
    {
        return Post::withViewerData($request->user())->findOrFail($id);
    }
}
