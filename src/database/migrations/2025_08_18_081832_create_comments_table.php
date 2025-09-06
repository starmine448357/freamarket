<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // 外部キー：ユーザー（削除時はコメントも削除）
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // 外部キー：商品（削除時はコメントも削除）
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->onDelete('cascade');

            // コメント本文
            $table->string('content', 255);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
