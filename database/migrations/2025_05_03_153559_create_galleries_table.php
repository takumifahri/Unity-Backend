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
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by'); // Foreign key column
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('image_path')->nullable(); // Path gambar
            $table->string('title')->nullable(); // Judul gambar
            $table->text('description')->nullable(); // Deskripsi gambar
            $table->string('bahan')->nullable(); // Bahan gambar
            $table->string('ukuran')->nullable(); // Ukuran gambar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
