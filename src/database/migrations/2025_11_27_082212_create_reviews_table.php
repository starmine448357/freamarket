<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // どの取引へのレビューか
            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->cascadeOnDelete();

            // 評価したユーザー（購入者 or 出品者）
            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 評価されたユーザー（出品者 or 購入者）
            $table->foreignId('target_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 評価（0〜5）
            $table->unsignedTinyInteger('rating');

            // コメント（任意）
            $table->text('comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
