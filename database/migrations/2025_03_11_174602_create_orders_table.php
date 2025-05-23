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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('catalog_id')->nullable();
            $table->foreign('catalog_id')->references('id')->on('catalogs')->onDelete('cascade');
            $table->unsignedBigInteger('custom_order_id')->nullable();
            $table->foreign('custom_order_id')->references('id')->on('custom_orders')->onDelete('cascade');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->integer('jumlah');
            $table->integer('total_harga');
            // $table->string('alamat');
            $table->enum('type', ['Pembelian', 'Pemesanan']);
            $table->enum('status', ['Menunggu_Pembayaran', 'Menunggu_Konfirmasi', 'Diproses', 'Sedang_Dikirim', 'Sudah_Terkirim', 'Selesai']);
            $table->string('bukti_pembayaran')->nullable();
            $table->boolean('isReviewed')->default(false);
            $table->foreignId('ulasan_id')->nullable()->constrained('reviews_products')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangans', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::dropIfExists('orders');
    }
};
