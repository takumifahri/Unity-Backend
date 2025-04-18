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
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('nama_keuangan');
            $table->integer('nominal');
            $table->date('tanggal');
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
