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
        'amount',            // ← price ではなく amount
        'payment_method',
        'status',
        'paid_at',
        // もしテーブルにあれば下も追加
        'shipping_postal_code',
        'shipping_address',
        'shipping_building',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function item()  { return $this->belongsTo(Item::class); }
}
