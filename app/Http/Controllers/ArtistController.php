<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ArtistController extends Controller
{
    public function show($name)
    {
        $apiKey = config('services.lastfm.api_key');
        
        $artistResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
            'method' => 'artist.getInfo',
            'api_key' => $apiKey,
            'artist' => $name,
            'format' => 'json'
        ]);

        // Get artist's top album
        $albumsResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
            'method' => 'artist.getTopAlbums',
            'api_key' => $apiKey,
            'artist' => $name,
            'limit' => 1,
            'format' => 'json'
        ]);

        $artistData = $artistResponse->json()['artist'] ?? null;
    
        if (isset($albumsResponse->json()['topalbums']['album'][0]['image'])) {
            $albumImages = $albumsResponse->json()['topalbums']['album'][0]['image'];
            $artistData['mainImage'] = end($albumImages)['#text'];
        }

        $similarArtists = [];
        $similarResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
            'method' => 'artist.getSimilar',
            'api_key' => $apiKey,
            'artist' => $name,
            'limit' => 12,
            'format' => 'json'
        ]);

        if (isset($similarResponse->json()['similarartists']['artist'])) {
            foreach ($similarResponse->json()['similarartists']['artist'] as $similar) {
                if (count($similarArtists) >= 6) break;

                $similarAlbumsResponse = Http::get("http://ws.audioscrobbler.com/2.0/", [
                    'method' => 'artist.getTopAlbums',
                    'api_key' => $apiKey,
                    'artist' => $similar['name'],
                    'limit' => 1,
                    'format' => 'json'
                ]);

                $similarInfo = Http::get("http://ws.audioscrobbler.com/2.0/", [
                    'method' => 'artist.getInfo',
                    'api_key' => $apiKey,
                    'artist' => $similar['name'],
                    'format' => 'json'
                ])->json()['artist'] ?? null;

                if (isset($similarAlbumsResponse->json()['topalbums']['album'][0]['image'])) {
                    $albumImages = $similarAlbumsResponse->json()['topalbums']['album'][0]['image'];
                    $albumImage = end($albumImages)['#text'];

                    if ($albumImage) {
                        $similarArtists[] = [
                            'name' => $similar['name'],
                            'image' => $albumImage,
                            'listeners' => $similarInfo['stats']['listeners'] ?? 0
                        ];
                    }
                }
            }
        }

        if (isset($artistData['bio']['summary'])) {
            $artistData['bio']['summary'] = preg_replace('/<a href=".*?>Read more on Last\.fm<\/a>/', '', $artistData['bio']['summary']);
        }

        $spotifyUrl = "https://open.spotify.com/search/" . urlencode($name);

        return view('artists.show', [
            'artist' => $artistData,
            'similarArtists' => $similarArtists,
            'spotifyUrl' => $spotifyUrl
        ]);
    }
} 