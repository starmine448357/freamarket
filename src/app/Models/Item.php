<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'brand',        // 無ければ nullable でOK
        'price',
        'description',
        'image_path',
        'condition',    // カラム名が state / item_condition なら読み替え
    ];

    /** 出品者 */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** カテゴリ（多対多） */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_category');
    }

    /** コメント */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /** いいね */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /** 購入（purchases テーブルに item_id がある前提） */
    public function purchase()
    {
        return $this->hasOne(Purchase::class, 'item_id');
    }

    /** 購入済み判定フラグ ($item->is_sold で true/false が取れる) */
    public function getIsSoldAttribute(): bool
    {
        return $this->purchase()->exists();
    }
}
