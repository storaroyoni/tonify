<?php

namespace App\Services;

use App\Models\User;

class TopStatsService
{
    public function getTopAlbums(User $user, $limit = 10)
    {
        return $user->topAlbums()->take($limit)->get();
    }

    public function getTopArtists(User $user, $limit = 10)
    {
        return $user->topArtists()->take($limit)->get();
    }

    public function getTopSongs(User $user, $limit = 10)
    {
        return $user->topSongs()->take($limit)->get();
    }
}
