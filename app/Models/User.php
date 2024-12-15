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
}
