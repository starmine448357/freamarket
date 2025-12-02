<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseUserRead extends Model
{
    protected $fillable = [
        'purchase_id',
        'user_id',
        'last_read_at',
    ];
}
