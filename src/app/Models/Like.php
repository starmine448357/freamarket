<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'item_id',
    ];

    /**
     * いいねしたユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * いいねされた商品
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
