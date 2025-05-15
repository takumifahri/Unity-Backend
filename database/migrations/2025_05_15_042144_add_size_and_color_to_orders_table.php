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

            $table->uuid('order_unique_id')->unique()->after('id'); // Unique ID for the order
            $table->unsignedBigInteger('size')->nullable()->after('catalog_id'); // Kolom size
            $table->unsignedBigInteger('color')->nullable()->after('size'); // Kolom color
            $table->string('catatan')->nullable()->after('color'); // Kolom catatan
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
