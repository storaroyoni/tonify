<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MusicCacheService
{
    // Cache durations
    const TRENDING_TRACKS_TTL = 60 * 24;        
    const ARTIST_INFO_TTL = 60 * 24 * 7;        
    const TRACK_INFO_TTL = 60 * 24;             

    // Cache keys
    const TRENDING_TRACKS_KEY = 'trending_tracks';
    const ARTIST_INFO_PREFIX = 'artist_info_';
    const TRACK_INFO_PREFIX = 'track_info_';

    public function getTrendingTracks()
    {
        return Cache::remember(self::TRENDING_TRACKS_KEY, self::TRENDING_TRACKS_TTL, function () {
            $response = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'chart.gettoptracks',
                'api_key' => config('services.lastfm.api_key'),
                'format' => 'json',
                'limit' => 50
            ]);

            if ($response->successful()) {
                return $response->json()['tracks']['track'] ?? [];
            }

            Log::error('Failed to fetch trending tracks');
            return [];
        });
    }

    public function getArtistInfo($artistName)
    {
        $cacheKey = self::ARTIST_INFO_PREFIX . md5($artistName);

        return Cache::remember($cacheKey, self::ARTIST_INFO_TTL, function () use ($artistName) {
            $response = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'artist.getinfo',
                'artist' => $artistName,
                'api_key' => config('services.lastfm.api_key'),
                'format' => 'json'
            ]);

            if ($response->successful()) {
                return $response->json()['artist'] ?? null;
            }

            Log::error('Failed to fetch artist info', ['artist' => $artistName]);
            return null;
        });
    }

    public function getTrackInfo($trackName, $artistName)
    {
        $cacheKey = self::TRACK_INFO_PREFIX . md5($trackName . $artistName);

        return Cache::remember($cacheKey, self::TRACK_INFO_TTL, function () use ($trackName, $artistName) {
            $response = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'track.getInfo',
                'track' => $trackName,
                'artist' => $artistName,
                'api_key' => config('services.lastfm.api_key'),
                'format' => 'json'
            ]);

            if ($response->successful()) {
                return $response->json()['track'] ?? null;
            }

            Log::error('Failed to fetch track info', [
                'track' => $trackName,
                'artist' => $artistName
            ]);
            return null;
        });
    }

    public function clearTrendingTracks()
    {
        Cache::forget(self::TRENDING_TRACKS_KEY);
    }

    public function clearArtistInfo($artistName)
    {
        Cache::forget(self::ARTIST_INFO_PREFIX . md5($artistName));
    }

    public function clearTrackInfo($trackName, $artistName)
    {
        Cache::forget(self::TRACK_INFO_PREFIX . md5($trackName . $artistName));
    }
} 