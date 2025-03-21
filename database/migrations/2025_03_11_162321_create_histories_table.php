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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('items_id');
            $table->string('item_type');  // Untuk membedakan jenis item (Catalog, Transaction, Material, dll)
            $table->unsignedBigInteger('user_id');
            $table->string('action');     // create, update, delete, restore, dll
            $table->string('reason')->nullable();
            $table->json('new_value')->nullable();
            $table->json('old_value')->nullable();
            $table->timestamps();
            
            // Foreign key ke users
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
