<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add columns without unique constraint
        Schema::table('custom_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_orders', 'order_unique_id')) {
                $table->uuid('custom_order_unique_id')->nullable()->after('id'); // Unique ID for the order
            }
            if (!Schema::hasColumn('custom_orders', 'order_id')) {
                $table->string('order_id')->nullable()->after('id'); // Kolom order_id
            }
        });

        // Generate unique UUIDs for existing records
        DB::table('custom_orders')->whereNull('order_unique_id')
            ->update(['order_unique_id' => DB::raw('(UUID())')]);
    }
    
    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropUnique(['order_unique_id']);
            $table->dropColumn(['order_unique_id', 'order_id']);
        });
    }

};
