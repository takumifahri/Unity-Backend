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
        Schema::create('master_bahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bahan');
            $table->integer('harga');
            $table->integer('stok');
            $table->string('satuan');
            $table->string('gambar_bahan')->nullable();
            $table->text('deskripsi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_bahans');
    }
};
