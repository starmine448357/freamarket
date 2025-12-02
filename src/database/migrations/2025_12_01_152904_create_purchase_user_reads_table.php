<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_user_reads', function (Blueprint $table) {
            $table->id();

            // どの取引か
            $table->foreignId('purchase_id')
                ->constrained()
                ->onDelete('cascade');

            // どのユーザーか（購入者・出品者どちらも）
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // 最後にチャットを見た時間
            $table->timestamp('last_read_at')->nullable();

            $table->timestamps();

            // purchase_id × user_id で1レコードにする
            $table->unique(['purchase_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_user_reads');
    }
};
