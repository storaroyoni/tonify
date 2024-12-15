<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastfmFieldsToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'lastfm_username')) {
                $table->string('lastfm_username')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'lastfm_session_key')) {
                $table->string('lastfm_session_key')->nullable()->after('lastfm_username');
            }
            if (!Schema::hasColumn('users', 'lastfm_connected_at')) {
                $table->timestamp('lastfm_connected_at')->nullable()->after('lastfm_session_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lastfm_username', 'lastfm_session_key', 'lastfm_connected_at']);
        });
    }
}
