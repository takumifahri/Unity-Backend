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
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('no_telp');
            $table->string('email');
            $table->string('jenis_baju');
            $table->string('ukuran');
            $table->string('jumlah')->nullable();
            $table->integer('total_harga')->nullable();
            $table->enum('sumber_kain', ['konveksi', 'sendiri'])->default('sendiri');
            $table->string('detail_bahan')->nullable();
            // $table->unsignedBigInteger('master_bahan_id')->nullable()->onDelete('cascade');
            $table->enum('status', ['pending', 'disetujui', 'proses', 'selesai', 'ditolak', 'dibatalkan'])->default('pending');  
            $table->enum('status_pembayaran', ['belum_bayar', 'sudah_bayar'])->default('belum_bayar'); 
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
