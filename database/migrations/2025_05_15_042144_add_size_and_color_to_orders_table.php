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
        Schema::table('orders', function (Blueprint $table) {
            //

            if (!Schema::hasColumn('orders', 'order_unique_id')) {
                $table->uuid('order_unique_id')->unique()->after('id'); // Unique ID for the order
            }
            if (!Schema::hasColumn('orders', 'size')) {
                $table->unsignedBigInteger('size')->nullable()->after('catalog_id'); // Kolom size
            }
            if (!Schema::hasColumn('orders', 'color')) {
                $table->unsignedBigInteger('color')->nullable()->after('size'); // Kolom color
            }
            if (!Schema::hasColumn('orders', 'catatan')) {
                $table->string('catatan')->nullable()->after('color'); // Kolom catatan
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
