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
            $table->text('deskripsi');
            $table->string('details');
            $table->integer('stok');
            $table->unsignedBigInteger('tipe_bahan_id');
            $table->unsignedBigInteger('jenis_katalog_id');
            $table->integer('price');
            $table->json('feature')->nullable();
            $table->enum('size', ["S", "M", "L", "XL"])->nullable();
            $table->enum('size_guide', [
                'S' => 'LD: 96cm, Panjang: 135cm',
                'M' => 'LD: 100cm, Panjang: 137cm',
                'L' => 'LD: 104cm, Panjang: 139cm',
                'XL' => 'LD: 108cm, Panjang: 141cm',
            ])->nullable();
            $table->enum('colors', ["Brown", "Black", "Navy", "Red", "Green"])->nullable();
            $table->string('gambar')->nullable();
            $table->timestamps();
            $table->softDeletes();

        
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('catalogs');
        Schema::enableForeignKeyConstraints();
    }
    public function afterAllMigrationsRan(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->foreign('tipe_bahan_id')->references('id')->on('master_bahans')->onDelete('cascade');
            $table->foreign('jenis_katalog_id')->references('id')->on('master_jenis_katalogs')->onDelete('cascade');
        });
    }
};
