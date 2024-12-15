<?php

namespace App\Models;

use App\Models\Pivots\ArtistUser;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = ['name'];

   
    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->using(ArtistUser::class)
                    ->withPivot('play_count');
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }

    public function topSongs()
    {
        return $this->hasMany(Song::class)->orderBy('rating', 'desc')->limit(5);
    }
}
