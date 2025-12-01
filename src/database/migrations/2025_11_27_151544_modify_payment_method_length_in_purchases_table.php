<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_method', 2)->change(); // 元の定義に戻す
        });
    }
};
