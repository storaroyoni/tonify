<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LastfmData extends Model
{
    protected $fillable = [
        'user_id',
        'access_token',
        'top_tracks',
        'top_artists',
        'top_albums',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topTracks()
    {
        return $this->hasMany(TopSong::class);
    }

    public function topArtists()
    {
        return $this->hasMany(TopArtist::class);
    }

    public function topAlbums()
    {
        return $this->hasMany(TopAlbum::class);
    }
}
