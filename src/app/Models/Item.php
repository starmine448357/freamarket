<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 

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

    // 画像URL（外部URL or storage or プレースホルダー）を返す
public function getImageUrlAttribute(): string
{
    if ($this->image_path) {
        // SeederのフルURL（http/https）はそのまま返す
        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }
        // 出品時に public ディスクへ保存した相対パス（例: items/xxx.jpg）
        return asset('storage/'.$this->image_path);
    }
    // 何もなければダミー画像
    return asset('img/placeholder.png');
}

}
