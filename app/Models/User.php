<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'lastfm_username',
        'profile_picture',
        'bio',
        'lastfm_session_key',
        'lastfm_connected_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'lastfm_session_key'
    ];

    protected $dates = [
        'lastfm_connected_at' 
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lastfm_connected_at' => 'datetime', 
        ];
    }

    public function topAlbums()
    {
        return $this->belongsToMany(Album::class)
                    ->using(AlbumUser::class)
                    ->withPivot('play_count')
                    ->orderByDesc('pivot_play_count');
    }

    public function topArtists()
    {
        return $this->belongsToMany(Artist::class)
                    ->using(ArtistUser::class)
                    ->withPivot('play_count')
                    ->orderByDesc('pivot_play_count');
    }

    public function topSongs()
    {
        return $this->belongsToMany(Song::class)
                    ->using(SongUser::class)
                    ->withPivot('play_count')
                    ->orderByDesc('pivot_play_count');
    }

    public function isLastfmConnected(): bool
    {
        return !is_null($this->lastfm_username) && !is_null($this->lastfm_session_key);
    }

    public function lastfmData()
    {
        return $this->hasOne(LastfmData::class);
    }

    public function getRecentTracks($limit = 10)
    {
        return Song::where('user_id', $this->id)
                   ->orderBy('played_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    public function getTopArtists($limit = 5)
    {
        return Artist::select('artists.*')
                    ->join('songs', 'songs.artist_id', '=', 'artists.id')
                    ->where('songs.user_id', $this->id)
                    ->groupBy('artists.id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->limit($limit)
                    ->get();
    }

    public function getTopAlbums($limit = 5)
    {
        return Album::select('albums.*')
                   ->join('songs', 'songs.album_id', '=', 'albums.id')
                   ->where('songs.user_id', $this->id)
                   ->groupBy('albums.id')
                   ->orderByRaw('COUNT(*) DESC')
                   ->limit($limit)
                   ->get();
    }

    public function getTopTracks($limit = 5)
    {
        return Song::where('user_id', $this->id)
                   ->select('songs.*')
                   ->groupBy('songs.id')
                   ->orderByRaw('COUNT(*) DESC')
                   ->limit($limit)
                   ->get();
    }
}
