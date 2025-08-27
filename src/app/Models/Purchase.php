<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'price', // カラム構成に合わせて
    ];

    // 購入したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 購入した商品
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
