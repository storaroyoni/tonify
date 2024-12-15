<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('songs', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->foreignId('artist_id')->constrained()->onDelete('cascade');
        $table->foreignId('album_id')->constrained()->onDelete('cascade');
        $table->string('cover_image')->nullable();  // cover image
        $table->integer('rating')->default(0);  // rating
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('songs');
}

};
