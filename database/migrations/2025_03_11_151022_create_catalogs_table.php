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
            $table->text('details');
            $table->integer('stok');
            $table->string('bahan');
            $table->integer('price');
            $table->json('feature')->nullable();
            $table->integer('sold')->nullable()->default(0);
            $table->enum('colors', ["Brown", "Black", "Navy", "Red", "Green"])->nullable();
            $table->json('gambar')->nullable()->comment('Stores multiple photo URLs as JSON array');
            $table->timestamps();
            $table->softDeletes();

        
            // Foreign Keys
            // $table->foreign('tipe_bahan_id')->references('id')->on('master_bahans')->onDelete('cascade');
            // $table->foreign('jenis_katalog_id')->references('id')->on('master_jenis_katalogs')->onDelete('cascade');
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
