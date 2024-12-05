<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ArtistUser extends Pivot
{
    protected $table = 'artist_user';
    protected $fillable = ['play_count'];
}
