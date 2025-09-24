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

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'postal_code',
        'address',
        'building',
        'profile_image_path', // プロフィール画像
    ];

    /**
     * 非表示にする属性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 型キャスト
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * リレーション：購入履歴
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * 追加アクセサ
     */
    protected $appends = ['profile_image_url'];

    /**
     * プロフィール画像のURL
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image_path) {
            return Storage::disk('public')->url($this->profile_image_path);
        }

        // デフォルト画像（public/images/avatar-default.png を用意）
        return asset('images/avatar-default.png');
    }
}
