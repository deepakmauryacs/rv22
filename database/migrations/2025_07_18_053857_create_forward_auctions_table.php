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
        Schema::create('forward_auctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('auction_id', 50);
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('buyer_user_id');
            $table->date('schedule_date')->nullable();
            $table->time('schedule_start_time');
            $table->time('schedule_end_time');
            $table->string('buyer_branch', 255)->nullable();
            $table->string('branch_address', 1500)->nullable();
            
            $table->string('remarks', 7000)->nullable();
            $table->string('price_basis', 255)->nullable();
            $table->string('payment_terms', 255)->nullable();
            $table->integer('delivery_period')->nullable();

            $table->enum('is_forcestop', ['1', '2'])->default('2')->comment('1=>Yes, 2=>No');
            $table->string('currency', 20)->nullable();
            
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forward_auctions');
    }
};
