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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fn')) {
                $table->string('fn')->nullable();
            }
            if (!Schema::hasColumn('users', 'ln')) {
                $table->string('ln')->nullable();
            }
            if (!Schema::hasColumn('users', 'lastfm_username')) {
                $table->string('lastfm_username')->nullable();
            }
            if (!Schema::hasColumn('users', 'lastfm_session_key')) {
                $table->string('lastfm_session_key')->nullable();
            }
            if (!Schema::hasColumn('users', 'lastfm_connected_at')) {
                $table->timestamp('lastfm_connected_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable();
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fn',
                'ln',
                'lastfm_username',
                'lastfm_session_key',
                'lastfm_connected_at',
                'profile_picture',
                'bio'
            ]);
        });
    }
};