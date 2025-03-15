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
            $table->string('order_id'); // ID dari Order Service
            $table->enum('status', ['pending', 'success', 'failure', 'expired', 'canceled']);
            $table->string('tujuan_transfer');
            $table->integer('amount');
            $table->enum('payment_method', ['credit_card', 'bank_transfer', 'gopay', 'ovo', 'dana']);
            $table->string('bukti_transfer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
