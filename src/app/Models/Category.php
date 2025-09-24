<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * カテゴリモデル
     *
     * items テーブルとの多対多リレーションを定義
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_category');
    }
}
