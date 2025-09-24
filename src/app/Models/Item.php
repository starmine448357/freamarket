<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'title',
        'brand',
        'price',
        'description',
        'image_path',  // 例: images/xxx.jpg（publicディスク相対）
        'condition',   // 'new' | 'like_new' | 'used' | 'bad'
        // 'status',   // カラムがある場合: 'selling' / 'sold' 等
    ];

    /**
     * 型変換
     */
    protected $casts = [
        'user_id' => 'int',
        'price'   => 'int',
    ];

    /**
     * 配列/JSON 変換時に含めるアクセサ
     */
    protected $appends = [
        'image_url',
        'is_sold',
        'condition_label',
    ];

    /**
     * 出品者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * カテゴリ（多対多）
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_category');
    }

    /**
     * コメント（新しい順）
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderByDesc('id');
    }

    /**
     * いいね
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * 現在のユーザーがこの商品をいいね済みか
     */
    public function isLikedBy(?User $user): bool
    {
        return $user
            ? $this->likes()->where('user_id', $user->id)->exists()
            : false;
    }

    /**
     * 購入情報（1:1）
     */
    public function purchase()
    {
        return $this->hasOne(Purchase::class, 'item_id');
    }

    /**
     * 売り切れフラグ
     */
    public function getIsSoldAttribute(): bool
    {
        return $this->purchase()->exists();
    }

    /**
     * 状態のラベル（日本語）
     */
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
     * 画像の公開URL
     * - 外部URLならそのまま返す
     * - storage:link 済みを前提に /storage/... を返す
     * - ファイルが無ければプレースホルダーを返す
     */
    public function getImageUrlAttribute(): string
    {
        $placeholder = asset('images/placeholder.png');

        $path = $this->image_path;
        if (!$path) {
            return $placeholder;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        $path = preg_replace('#^(public/|/?)storage/#', '', ltrim($path, '/'));

        return Storage::disk('public')->exists($path)
            ? Storage::url($path)   // 例: /storage/images/xxx.jpg
            : $placeholder;
    }

    /*
    // 必要に応じて利用できるスコープ例
    public function scopeSelling($q)
    {
        return $q->where('status', 'selling');
    }
    */
}
