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
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->enum('status', ['pending', 'success', 'failure', 'expired', 'canceled']);
            $table->string('tujuan_transfer');
            $table->integer('amount');
            $table->enum('payment_method', ['credit_card', 'bank_transfer', 'gopay', 'ovo', 'dana']);
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
