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
    Schema::create('artists', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('bio')->nullable();  // artist bio
        $table->string('profile_picture')->nullable();  // profile picture for the artist
        $table->timestamps();
    });
}

    public function down()
{
    Schema::dropIfExists('artists');
}

};
