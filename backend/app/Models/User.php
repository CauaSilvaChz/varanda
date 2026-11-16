<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'bio', 'profile_photo'];

    // Campos que nunca aparecem no JSON da API.
    protected $hidden = ['password', 'email', 'profile_photo'];

    // Campos calculados que aparecem no JSON da API.
    protected $appends = ['profile_photo_url'];

    protected $casts = ['password' => 'hashed'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Pessoas que seguem este usuário.
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    // Pessoas que este usuário segue.
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    // Adiciona "is_following" (true/false): se quem está logado segue cada usuário da consulta.
    public function scopeWithFollowStatus($query, User $viewer)
    {
        $query->withExists([
            'followers as is_following' => fn ($q) => $q->where('follows.follower_id', $viewer->id),
        ]);
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo ? Storage::disk('public')->url($this->profile_photo) : null;
    }
}
