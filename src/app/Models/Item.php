<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'brand',
        'price',
        'description',
        'image_path',
        'condition',
    ];

    protected $casts = [
        'user_id' => 'int',
        'price'   => 'int',
    ];

    protected $appends = [
        'image_url',
        'is_sold',
        'condition_label',
    ];

    /* ========================
          リレーション
       ======================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_category');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderByDesc('id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function isLikedBy(?User $user): bool
    {
        return $user
            ? $this->likes()->where('user_id', $user->id)->exists()
            : false;
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class, 'item_id');
    }

    /* ========================
          アクセサ
       ======================== */

    /** 売り切れ判定 */
    public function getIsSoldAttribute(): bool
    {
        return $this->purchase()->exists();
    }

    /** 状態ラベル */
    public function getConditionLabelAttribute(): string
    {
        $map = [
            'new'      => '新品',
            'like_new' => '未使用に近い',
            'used'     => '中古',
            'bad'      => '状態が悪い',
        ];

        return $map[$this->condition] ?? '—';
    }

    /**
     * 画像URL（プレースホルダー込み）
     */
    public function getImageUrlAttribute(): string
    {
        $placeholder = asset('images/placeholder.png');

        // 画像なし → プレースホルダー
        if (!$this->image_path) {
            return $placeholder;
        }

        // 外部URL → そのまま返す
        if (preg_match('#^https?://#', $this->image_path)) {
            return $this->image_path;
        }

        // ローカルストレージ
        $path = $this->image_path;

        // Storage に存在 → storage/... URL を返す
        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        // なければプレースホルダー
        return $placeholder;
    }
}
