<?php

namespace App\Services;

use GuzzleHttp\Client;

class LastFmService
{
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        $this->apiKey = env('LASTFM_API_KEY');
        $this->client = new Client();
    }

    public function getTopTracks($user)
    {
        $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
            'query' => [
                'method' => 'user.gettoptracks',
                'user' => $user,
                'api_key' => $this->apiKey,
                'format' => 'json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // Add new method for recent tracks
    public function getRecentTracks($user)
    {
        try {
            $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
                'query' => [
                    'method' => 'user.getrecenttracks',
                    'user' => $user,
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                    'limit' => 50,
                    'extended' => 1  // Get extended track info
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            // Log the full response for debugging
            \Log::info('Recent tracks response:', ['data' => $data]);

            if (isset($data['recenttracks']['track'])) {
                $tracks = $data['recenttracks']['track'];
                return array_map(function($track) {
                    return [
                        'name' => $track['name'],
                        'artist' => $track['artist']['name'] ?? $track['artist']['#text'],
                        'image' => $this->getLargestImage($track['image'] ?? []),
                        'url' => $track['url']
                    ];
                }, $tracks);
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::error('Error getting recent tracks: ' . $e->getMessage());
            return [];
        }
    }

    private function getLargestImage($images)
    {
        if (empty($images)) {
            return null;
        }

        foreach (['extralarge', 'large', 'medium', 'small'] as $size) {
            foreach ($images as $image) {
                if ($image['size'] === $size && !empty($image['#text'])) {
                    return $image['#text'];
                }
            }
        }

        return end($images)['#text'] ?? null;
    }

    public function getTopAlbums($user)
    {
        $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
            'query' => [
                'method' => 'user.gettopalbums',
                'user' => $user,
                'api_key' => $this->apiKey,
                'format' => 'json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getTopArtists($user)
    {
        $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
            'query' => [
                'method' => 'user.gettopartists',
                'user' => $user,
                'api_key' => $this->apiKey,
                'format' => 'json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getSimilarArtists($artist)
    {
        $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
            'query' => [
                'method' => 'artist.getsimilar',
                'artist' => $artist,
                'api_key' => $this->apiKey,
                'format' => 'json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // Get track tags for mood analysis
    public function getTrackTags($artist, $track)
    {
        try {
            $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
                'query' => [
                    'method' => 'track.gettoptags',
                    'artist' => $artist,
                    'track' => $track,
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            // Log the response for debugging
            \Log::info('Last.fm tags response:', [
                'artist' => $artist,
                'track' => $track,
                'response' => $data
            ]);

            return $data['toptags']['tag'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Error getting track tags:', [
                'error' => $e->getMessage(),
                'artist' => $artist,
                'track' => $track
            ]);
            return [];
        }
    }
}
