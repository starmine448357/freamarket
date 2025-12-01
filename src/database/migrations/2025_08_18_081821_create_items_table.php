<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->string('brand', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('price');
            $table->enum('condition', ['new', 'like_new', 'used']);
            $table->string('image_path', 255)->nullable();

            /**
             * ▼ 商品ステータス（コントローラー仕様と統一）
             *
             * selling … 出品中（初期状態）
             * sold    … 購入後（取引中 or 完了に関係なく sold のまま）
             */
            $table->enum('status', [
                'selling',
                'sold',
            ])->default('selling');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
