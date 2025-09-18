<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'postal_code',
        'address',
        'building',
        'profile_image_path', // プロフィール画像
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ユーザーが購入した履歴
    public function purchases()
    {
        return $this->hasMany(\App\Models\Purchase::class);
    }

    /**
     * プロフィール画像のURLを返すアクセサ
     */
    protected $appends = ['profile_image_url'];

    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image_path) {
            return Storage::disk('public')->url($this->profile_image_path);
        }
        // デフォルト画像（public/images/avatar-default.png を置いておくと安心）
        return asset('images/avatar-default.png');
    }
}
