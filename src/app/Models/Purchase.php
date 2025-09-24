<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'amount',               // 金額（price ではなく amount）
        'payment_method',
        'status',
        'paid_at',
        'shipping_postal_code',
        'shipping_address',
        'shipping_building',
    ];

    /**
     * 購入者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 購入された商品
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
