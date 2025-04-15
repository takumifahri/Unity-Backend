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
        Schema::create('catalog_colors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_id');
            // $table->unsignedBigInteger('catalog_size_id');
            $table->string('color_name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_colors');
    }
};
