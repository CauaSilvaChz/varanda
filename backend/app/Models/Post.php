<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'image'];

    protected $hidden = ['image'];

    protected $appends = ['image_url'];

    protected static function booted()
    {
        // Quando a publicação é excluída, o arquivo da imagem também é.
        static::deleted(function (Post $post) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Carrega o autor, os contadores e se quem está logado já curtiu, tudo na mesma consulta.
    public function scopeWithViewerData($query, User $viewer)
    {
        $query->with('user')
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as liked_by_me' => fn ($q) => $q->where('user_id', $viewer->id)]);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
