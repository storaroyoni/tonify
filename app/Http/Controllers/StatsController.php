<?php

namespace App\Http\Controllers;

use App\Services\TopStatsService;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    protected $topStatsService;

    public function __construct(TopStatsService $topStatsService)
    {
        $this->topStatsService = $topStatsService;
    }

    public function showTopStats()
    {
        $user = auth()->user();  
        
        // fetching top albums, artists, and songs
        $topAlbums = $this->topStatsService->getTopAlbums($user);
        $topArtists = $this->topStatsService->getTopArtists($user);
        $topSongs = $this->topStatsService->getTopSongs($user);

        return response()->json([
            'top_albums' => $topAlbums->map(function ($album) {
                return [
                    'title' => $album->title,
                    'artist' => $album->artist->name,
                    'play_count' => $album->pivot->play_count,  // accessing the play count from the pivot table
                    'cover_image' => $album->cover_image,
                ];
            }),
            'top_artists' => $topArtists->map(function ($artist) {
                return [
                    'name' => $artist->name,
                    'play_count' => $artist->pivot->play_count,  
                ];
            }),
            'top_songs' => $topSongs->map(function ($song) {
                return [
                    'title' => $song->title,
                    'artist' => $song->artist->name,
                    'play_count' => $song->pivot->play_count,  
                ];
            }),
        ]);
    }
}

