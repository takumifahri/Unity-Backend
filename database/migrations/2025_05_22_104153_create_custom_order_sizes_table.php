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
        Schema::create('custom_order_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_order_id')->nullable();
            $table->unsignedBigInteger('custom_order_color_id');
            $table->string('size');
            $table->integer('jumlah');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_order_sizes');
    }
};
