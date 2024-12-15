<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastfmDataTable extends Migration
{
    public function up()
    {
        Schema::create('lastfm_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('access_token');
            $table->json('top_tracks')->nullable();
            $table->json('top_artists')->nullable();
            $table->json('top_albums')->nullable();
            $table->timestamps();
        });

        Schema::create('top_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lastfm_data_id')->constrained('lastfm_data')->onDelete('cascade');
            $table->string('title');
            $table->string('artist');
            $table->timestamps();
        });

        Schema::create('top_artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lastfm_data_id')->constrained('lastfm_data')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('top_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lastfm_data_id')->constrained('lastfm_data')->onDelete('cascade');
            $table->string('title');
            $table->string('artist');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('top_albums');
        Schema::dropIfExists('top_artists');
        Schema::dropIfExists('top_songs');
        Schema::dropIfExists('lastfm_data');
    }
}