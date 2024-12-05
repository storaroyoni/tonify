<?php

namespace App\Models;

use App\Models\Pivots\SongUser;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = ['title', 'artist_id', 'album_id'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->using(SongUser::class)
                    ->withPivot('play_count');
    }
}
