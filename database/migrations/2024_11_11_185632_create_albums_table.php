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
    Schema::create('albums', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->foreignId('artist_id')->constrained()->onDelete('cascade');
        $table->string('cover_image')->nullable();  // cover image for the album
        $table->date('release_date');  // release date of the album
        $table->timestamps();
    });
}

    public function down()
{
    Schema::dropIfExists('albums');
}

};
