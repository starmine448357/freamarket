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

    /* ==========================================
        リレーション
       ========================================== */

    /**
     * 紐づく取引（Purchase）
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * レビューを書いたユーザー（reviewer）
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * レビューの対象ユーザー（target）
     */
    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
