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
        
        Schema::disableForeignKeyConstraints();
    
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_katalog');
            $table->text('deskripsi')->nullable();
            $table->integer('stok');
            $table->unsignedBigInteger('tipe_bahan_id');
            $table->unsignedBigInteger('jenis_katalog_id');
            $table->integer('harga');
            $table->string('gambar')->nullable();
            $table->timestamps();
        
            // Foreign Keys
            $table->foreign('tipe_bahan_id')->references('id')->on('master_bahans')->onDelete('cascade');
            $table->foreign('jenis_katalog_id')->references('id')->on('master_jenis_katalogs')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
    public function afterAllMigrationsRan(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->foreign('tipe_bahan_id')->references('id')->on('master_bahans')->onDelete('cascade');
            $table->foreign('jenis_katalog_id')->references('id')->on('master_jenis_katalogs')->onDelete('cascade');
        });
    }
};
