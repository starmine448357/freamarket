<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            // 出品者
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // 購入者
            $table->foreignId('buyer_id')
                ->constrained('users')
                ->onDelete('cascade');

            // 商品
            $table->foreignId('item_id')
                ->constrained()
                ->onDelete('cascade');

            // 支払い方法
            $table->enum('payment_method', [
                'credit_card',
                'convenience_store',
                'bank_transfer',
            ]);

            // 金額
            $table->integer('amount');

            // 配送先
            $table->string('shipping_postal_code', 20);
            $table->string('shipping_address', 255);
            $table->string('shipping_building', 255)->nullable();

            /**
             * ▼ 取引ステータス（コントローラー仕様と完全一致）
             *
             * pending         … 購入直後（初期）
             * buyer_reviewed  … 購入者レビュー済み
             * completed       … 双方レビュー済み（取引完了）
             */
            $table->enum('status', [
                'pending',
                'buyer_reviewed',
                'completed',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
