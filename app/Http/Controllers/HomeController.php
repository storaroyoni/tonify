<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $globalCharts = Cache::remember('global_charts', 3600, function () {
            $apiKey = config('services.lastfm.api_key');
            $charts = [];

            try {
                // weekly top tracks
                $topTracksResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                    'method' => 'chart.gettoptracks',
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 9
                ]);

                if ($topTracksResponse->successful()) {
                    $charts['topTracks'] = collect($topTracksResponse['tracks']['track'])
                        ->map(function ($track) {
                            return [
                                'name' => $track['name'],
                                'artist' => $track['artist']['name'],
                                'playcount' => $track['playcount'],
                                'image' => $track['image'][2]['#text'] ?? null,
                                'url' => $track['url']
                            ];
                        })->toArray();
                }

                // trending artists
                $artistsResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                    'method' => 'chart.gettopartists',
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 8
                ]);

                if ($artistsResponse->successful()) {
                    $charts['trendingArtists'] = collect($artistsResponse['artists']['artist'])
                        ->map(function ($artist) {
                            return [
                                'name' => $artist['name'],
                                'listeners' => $artist['listeners'],
                                'image' => $artist['image'][2]['#text'] ?? null,
                                'url' => $artist['url']
                            ];
                        })->toArray();
                }

                // top genres
                $tagsResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                    'method' => 'chart.gettoptags',
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 12
                ]);

                if ($tagsResponse->successful()) {
                    $charts['genres'] = collect($tagsResponse['tags']['tag'])
                        ->map(function ($tag) {
                            return [
                                'name' => $tag['name'],
                                'count' => $tag['reach'],
                                'url' => $tag['url']
                            ];
                        })->toArray();
                }

            } catch (\Exception $e) {
                \Log::error('Error fetching global charts', [
                    'error' => $e->getMessage()
                ]);
            }

            return $charts;
        });

        return view('home', compact('globalCharts'));
    }
}