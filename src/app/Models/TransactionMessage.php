<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'user_id',
        'message',
        'image_path',
    ];

    /**
     * どの取引（purchases）に紐づくメッセージか
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * メッセージを送ったユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
