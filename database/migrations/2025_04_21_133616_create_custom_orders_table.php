<?php

use Illuminate\Database\Eloquent\SoftDeletes;
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
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('no_telp');
            $table->string('email');
            $table->string('jenis_baju');
            $table->string('ukuran');
            $table->enum('sumber_kain', ['konveksi', 'sendiri'])->default('konveksi');
            $table->unsignedBigInteger('master_bahan_id')->nullable()->onDelete('cascade');
            $table->enum('status', ['pending', 'proses', 'selesai'])->default('pending');   
            $table->string('gambar_referensi')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('estimasi_waktu')->nullable();
            $table->SoftDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_orders');
    }
};
