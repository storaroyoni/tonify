<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AlbumUser extends Pivot
{
    protected $table = 'album_user';
    protected $fillable = ['play_count'];
}
