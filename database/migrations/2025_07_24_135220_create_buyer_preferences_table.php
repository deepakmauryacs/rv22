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
        Schema::create('buyer_preferences', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->unsignedBigInteger('buyer_user_id')->nullable();
            $table->unsignedBigInteger('vend_user_id')->nullable();
            $table->tinyInteger('fav_or_black')->comment('1- Favourite, 2- Blacklisted');
            $table->timestamp('created_date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_preferences');
    }
};
