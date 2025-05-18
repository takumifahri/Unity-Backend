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
        Schema::table('custom_orders', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('custom_orders', 'order_unique_id')) {
                $table->uuid('order_unique_id')->unique()->after('id'); // Unique ID for the order
            }
            if (!Schema::hasColumn('custom_orders', 'order_id')) {
                $table->string('order_id')->nullable()->after('id'); // Kolom order_id
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            //
        });
    }
};
