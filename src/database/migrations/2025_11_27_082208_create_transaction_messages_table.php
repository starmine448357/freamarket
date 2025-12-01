<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_messages', function (Blueprint $table) {
            $table->id();

            // ER図：purchase_id, user_id, message
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('user_id');
            $table->text('message');

            $table->timestamps();

            // 外部キー設定（ER図準拠）
            $table->foreign('purchase_id')
                ->references('id')->on('purchases')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_messages');
    }
};
