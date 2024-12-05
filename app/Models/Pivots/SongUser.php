<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SongUser extends Pivot
{
    protected $table = 'song_user';
    protected $fillable = ['play_count'];
}
