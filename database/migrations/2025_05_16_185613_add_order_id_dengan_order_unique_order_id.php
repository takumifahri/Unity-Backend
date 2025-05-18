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
    // Langkah 1: Tambahkan kolom terlebih dahulu tanpa constraint unique
        if (!Schema::hasColumn('transactions', 'transaction_unique_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('transaction_unique_id')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('delivery_proofs', 'delivery_proof_unique_id')) {
            Schema::table('delivery_proofs', function (Blueprint $table) {
                $table->string('delivery_proof_unique_id')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
