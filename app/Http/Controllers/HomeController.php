<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index()
    {
        $apiKey = config('services.lastfm.api_key');
        
        $tracksResponse = Http::get('http://ws.audioscrobbler.com/2.0/', [
            'method' => 'chart.gettoptracks',
            'api_key' => $apiKey,
            'format' => 'json',
            'limit' => 12
        ]);

        $artistsResponse = Http::get('http://ws.audioscrobbler.com/2.0/', [
            'method' => 'chart.gettopartists',
            'api_key' => $apiKey,
            'format' => 'json',
            'limit' => 8
        ]);

        $tracks = [];
        foreach ($tracksResponse->json()['tracks']['track'] ?? [] as $track) {
            $trackInfoResponse = Http::get('http://ws.audioscrobbler.com/2.0/', [
                'method' => 'track.getInfo',
                'api_key' => $apiKey,
                'artist' => $track['artist']['name'],
                'track' => $track['name'],
                'format' => 'json'
            ]);

            $trackInfo = $trackInfoResponse->json()['track'] ?? null;
            $albumImage = null;
            
            if (isset($trackInfo['album']['image'])) {
                $albumImage = end($trackInfo['album']['image'])['#text'];
            }
            
            if ($albumImage) {
                $tracks[] = [
                    'name' => $track['name'],
                    'artist' => $track['artist']['name'],
                    'playcount' => $track['playcount'],
                    'image' => $albumImage
                ];
            }

            if (count($tracks) >= 6) break;
        }

        $artists = [];
        foreach ($artistsResponse->json()['artists']['artist'] ?? [] as $artist) {
            $albumsResponse = Http::get('http://ws.audioscrobbler.com/2.0/', [
                'method' => 'artist.getTopAlbums',
                'artist' => $artist['name'],
                'api_key' => $apiKey,
                'format' => 'json',
                'limit' => 1
            ]);

            if (isset($albumsResponse->json()['topalbums']['album'][0]['image'])) {
                $albumImages = $albumsResponse->json()['topalbums']['album'][0]['image'];
                $largestImage = end($albumImages)['#text'];

                if ($largestImage) {
                    $artists[] = [
                        'name' => $artist['name'],
                        'listeners' => $artist['listeners'],
                        'image' => $largestImage
                    ];
                }
            }
        }

        $tagsResponse = Http::get('http://ws.audioscrobbler.com/2.0/', [
            'method' => 'tag.getTopTags',
            'api_key' => $apiKey,
            'format' => 'json',
            'limit' => 6
        ]);

        $genres = array_slice(array_map(function($tag) {
            return [
                'name' => $tag['name'],
                'count' => $tag['count'] ?? 0
            ];
        }, $tagsResponse->json()['toptags']['tag'] ?? []), 0, 6);

        return view('home', ['globalCharts' => [
            'topTracks' => $tracks,
            'trendingArtists' => $artists,
            'genres' => $genres
        ]]);
    }
}