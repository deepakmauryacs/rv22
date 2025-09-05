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
        Schema::create('technical_approvals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rfq_no', 100)->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('description', 500)->nullable();
            $table->enum('technical_approval', ['Yes', 'No'])->default('Yes');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_approvals');
    }
};
