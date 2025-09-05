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
        Schema::create('orders_pi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('order_number', 255);
            $table->string('pi_attachment', 500);
            $table->dateTime('pi_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('order_date')->nullable()->comment('comes from order table');
            $table->unsignedBigInteger('buyer_branch_id');
            $table->unsignedBigInteger('vendor_user_id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('PI uploaded by, user id');
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_pi');
    }
};
