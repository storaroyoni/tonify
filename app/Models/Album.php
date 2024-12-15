<?php

namespace App\Models;

use App\Models\Pivots\AlbumUser;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['title', 'artist_id', 'cover_image', 'release_date'];

    
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->using(AlbumUser::class)
                    ->withPivot('play_count');
    }

    public function songs()
    {
        return $this->hasMany(Song::class);
    }
}