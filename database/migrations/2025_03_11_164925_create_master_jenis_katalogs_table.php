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
        Schema::create('master_jenis_katalogs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenis_katalog');
            $table->text('deskripsi');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->dropForeign(['jenis_katalog_id']);
        });
        Schema::dropIfExists('master_jenis_katalogs');
    }
};
