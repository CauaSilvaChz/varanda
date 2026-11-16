<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        return $post->comments()->with('user')->oldest()->orderBy('id')->paginate(20);
    }

    public function store(Request $request, Post $post)
    {
        $request->validate(
            ['content' => ['required', 'string', 'max:500']],
            ['content.required' => 'O comentário não pode ficar vazio.']
        );

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'comment' => $comment->load('user'),
            'comments_count' => $post->comments()->count(),
        ], 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user()->isNot($request->user())) {
            abort(403, 'Você só pode excluir seus próprios comentários.');
        }

        $comment->delete();

        return ['comments_count' => Comment::where('post_id', $comment->post_id)->count()];
    }
}
