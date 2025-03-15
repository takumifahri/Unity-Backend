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
        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('image_path');                              // Path gambar bukti pengiriman
            $table->text('description')->nullable();                   // Deskripsi tambahan
            $table->timestamp('delivery_date')->nullable();            // Tanggal pengiriman
            $table->string('receiver_name')->nullable();               // Nama penerima barang
            $table->text('notes')->nullable();                         // Catatan tambahan
            $table->enum('status', ['delivered', 'failed', 'pending'])->default('delivered'); // Status pengiriman
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_proofs');
    }
};
