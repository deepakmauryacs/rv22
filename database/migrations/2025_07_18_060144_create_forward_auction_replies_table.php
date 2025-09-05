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
        Schema::create('forward_auction_replies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('auction_id', 50);
            $table->unsignedBigInteger('auction_product_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('buyer_user_id');
            $table->decimal('price', 15, 2);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forward_auction_replies');
    }
};
