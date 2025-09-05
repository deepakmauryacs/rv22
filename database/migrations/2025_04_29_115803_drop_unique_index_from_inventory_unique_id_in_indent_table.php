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
        Schema::table('indent', function (Blueprint $table) {
            $table->dropUnique('indent_inventory_unique_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indent', function (Blueprint $table) {
            $table->unique('inventory_unique_id');
        });
    }
};
