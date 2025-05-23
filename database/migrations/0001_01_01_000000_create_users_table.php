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
        // First create users table without the foreign key
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('name');
            $table->enum('role', ['admin', 'user', 'owner', 'developer'])->default('user');
            $table->enum('gender',['male', 'female'])->nullable();
            $table->string('email')->unique();
            $table->integer('total_order')->default(0);
            $table->string('phone')->nullable();
            $table->string('profile_photo', 2048)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->boolean('isActive')->default(false);
            $table->boolean('isAgree')->default(false);
            // Remove location_id from here initially
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Then create locations table
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('label');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            // Add address fields for geocoding/display purposes
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable(); // This will be useful for your polygon map
            $table->string('postal_code')->nullable();
            $table->timestamps();
        });

        // // Finally add the address_id to users table
        // Schema::table('users', function (Blueprint $table) {
        //     $table->unsignedBigInteger('address_id')->nullable();
        //     $table->foreign('address_id')->references('id')->on('locations')->onDelete('cascade');
        // });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Important: Drop in the reverse order of creation
        // First remove the foreign key in users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });

        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('users');
    }
};