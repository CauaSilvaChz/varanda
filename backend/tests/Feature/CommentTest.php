<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_a_post(): void
    {
        $post = Post::factory()->create();
        $me = User::factory()->create();
        Sanctum::actingAs($me);

        $this->postJson("/api/posts/{$post->id}/comments", ['content' => 'Que legal!'])
            ->assertCreated()
            ->assertJsonPath('comment.content', 'Que legal!')
            ->assertJsonPath('comment.user.id', $me->id)
            ->assertJsonPath('comments_count', 1);
    }

    public function test_empty_comment_is_rejected(): void
    {
        $post = Post::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/posts/{$post->id}/comments", ['content' => '    '])
            ->assertJsonValidationErrors(['content']);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comments_are_listed_in_chronological_order(): void
    {
        $post = Post::factory()->create();
        $second = Comment::factory()->for($post)->create(['created_at' => now()->subMinutes(5)]);
        $first = Comment::factory()->for($post)->create(['created_at' => now()->subMinutes(30)]);
        $third = Comment::factory()->for($post)->create(['created_at' => now()]);
        Sanctum::actingAs(User::factory()->create());

        $ids = array_column($this->getJson("/api/posts/{$post->id}/comments")->json('data'), 'id');

        $this->assertSame([$first->id, $second->id, $third->id], $ids);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $me = User::factory()->create();
        $comment = Comment::factory()->for($me)->create();
        Sanctum::actingAs($me);

        $this->deleteJson("/api/comments/{$comment->id}")
            ->assertOk()
            ->assertJsonPath('comments_count', 0);

        $this->assertModelMissing($comment);
    }

    public function test_user_cannot_delete_someone_elses_comment(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->for($author)->create();
        $comment = Comment::factory()->for($post)->create();

        // Nem quem comentou em outra publicação, nem o autor da publicação podem apagar.
        Sanctum::actingAs(User::factory()->create());
        $this->deleteJson("/api/comments/{$comment->id}")->assertForbidden();

        Sanctum::actingAs($author);
        $this->deleteJson("/api/comments/{$comment->id}")->assertForbidden();

        $this->assertModelExists($comment);
    }
}
