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
        Schema::create('forward_auction_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('auction_id', 50);
            $table->string('product_name', 500);
            $table->string('specs', 500)->nullable();
            $table->float('quantity');
            $table->integer('uom');
            $table->decimal('start_price', 15, 2);
            $table->string('file_attachment', 255)->nullable();
            $table->float('min_bid_increment_amount')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forward_auction_products');
    }
};
