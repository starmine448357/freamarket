<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',              // 出品者
        'buyer_id',             // 購入者
        'item_id',
        'amount',
        'payment_method',
        'status',
        'paid_at',
        'shipping_postal_code',
        'shipping_address',
        'shipping_building',
    ];

    /**
     * 出品者（seller）
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 購入者（buyer）
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * 商品
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
