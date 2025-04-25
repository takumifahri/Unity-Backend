<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     **/
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');  // Hanya sebagai referensi, bukan relasi one-to-one
            $table->enum('status', ['pending', 'success', 'failure', 'expired', 'canceled']);
            $table->string('tujuan_transfer');
            $table->integer('amount');
            $table->enum('payment_method', ['Cash', 'BCA', 'E-Wallet_Dana']);
            $table->string('bukti_transfer')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });
        Schema::dropIfExists('transactions');
    }
};
