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
        Schema::create('keuangans', function (Blueprint $table) {
            
            $table->id();
            // $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('catalog_id')->nullable();
            $table->foreign('catalog_id')->references('id')->on('catalogs')->onDelete('set null');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('keterangan');
            $table->enum('jenis_pembayaran', ['Cash', 'Transfer', 'E-Wallet'])->default('Cash');
            $table->integer('nominal');
            $table->timestamp('tanggal');
            $table->enum('jenis_keuangan', ['pemasukan', 'pengeluaran']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('keuangans');
    }
};
