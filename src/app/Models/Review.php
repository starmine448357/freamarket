<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'reviewer_id',
        'target_id',
        'rating',
        'comment',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
