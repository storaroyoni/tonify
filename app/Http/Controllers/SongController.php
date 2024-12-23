<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SongController extends Controller
{
    public function show($name, $artist)
    {
        $apiKey = config('services.lastfm.api_key');
        
        $songResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
            'method' => 'track.getInfo',
            'api_key' => $apiKey,
            'artist' => $artist,
            'track' => $name,
            'format' => 'json'
        ]);

        $spotifyResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
            'method' => 'track.getInfo',
            'api_key' => $apiKey,
            'artist' => $artist,
            'track' => $name,
            'format' => 'json',
            'autocorrect' => 1
        ]);

        $spotifyUrl = "https://open.spotify.com/search/" . urlencode($artist . " " . $name);

        $similarResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
            'method' => 'track.getSimilar',
            'api_key' => $apiKey,
            'artist' => $artist,
            'track' => $name,
            'autocorrect' => 1,
            'limit' => 12,  
            'format' => 'json'
        ]);

        $similarTracks = [];
        if (isset($similarResponse->json()['similartracks']['track'])) {
            foreach ($similarResponse->json()['similartracks']['track'] as $track) {
                if (count($similarTracks) >= 6) break; 

                $albumResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
                    'method' => 'track.getInfo',
                    'api_key' => $apiKey,
                    'artist' => $track['artist']['name'],
                    'track' => $track['name'],
                    'format' => 'json'
                ]);

                $albumInfo = $albumResponse->json()['track'] ?? null;
                $albumImage = $albumInfo['album']['image'][2]['#text'] ?? null;
                
                if ($albumImage) {
                    $similarTracks[] = [
                        'name' => $track['name'],
                        'artist' => [
                            'name' => $track['artist']['name']
                        ],
                        'image' => $albumInfo['album']['image']
                    ];
                }
            }
        }

        return view('songs.show', [
            'song' => $songResponse->json()['track'] ?? null,
            'similarTracks' => $similarTracks,
            'spotifyUrl' => $spotifyUrl
        ]);
    }
}
