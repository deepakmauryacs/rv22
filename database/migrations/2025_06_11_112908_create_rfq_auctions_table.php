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
        Schema::create('rfq_auctions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_no', 100);
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('buyer_user_id');
            $table->string('auction_date', 20);
            $table->time('auction_start_time');
            $table->time('auction_end_time');
            $table->decimal('min_bid_decrement', 25, 2)->default(0.00);
            $table->string('currency', 20)->nullable();

            // Matches enum('1','2') with 1 => Yes, 2 => No
            $table->enum('is_rfq_price_map', ['1', '2'])->default('2')->comment('1=>Yes, 2=>No');
            $table->timestamp('price_map_time')->useCurrent();

            $table->enum('is_forcestop', ['1', '2'])->default('2')->comment('1=>Yes, 2=>No');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_auctions');
    }
};
