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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fn');  // First Name
            $table->string('ln');  // Last Name
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // LastFM Integration Fields
            $table->string('lastfm_username')->nullable();
            $table->string('lastfm_session_key')->nullable();
            $table->timestamp('lastfm_connected_at')->nullable();

            // Optional: Additional user preference fields
            $table->string('profile_picture')->nullable();
            $table->text('bio')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};