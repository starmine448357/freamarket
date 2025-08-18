<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->string('brand', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('price');
            $table->enum('condition', ['new', 'like_new', 'used']);
            $table->string('image_path', 255)->nullable();
            $table->enum('status', ['selling', 'sold'])->default('selling');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('items');
    }
};
