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

    public function getTopTracks($user, $period = 'overall')
    {
        try {
            $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
                'query' => [
                    'method' => 'user.gettoptracks',
                    'user' => $user,
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                    'period' => $period,
                    'limit' => 5
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['toptracks']['track'])) {
                return array_map(function($track) {
                    return [
                        'name' => $track['name'],
                        'artist' => $track['artist']['name'] ?? $track['artist']['#text'],
                        'playcount' => $track['playcount'],
                        'url' => $track['url']
                    ];
                }, $data['toptracks']['track']);
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::error('Error getting top tracks: ' . $e->getMessage());
            return [];
        }
    }

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
                    'extended' => 1  
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
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

    public function getTopAlbums($user, $period = 'overall')
    {
        try {
            $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
                'query' => [
                    'method' => 'user.gettopalbums',
                    'user' => $user,
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                    'period' => $period,
                    'limit' => 5
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['topalbums']['album'])) {
                return array_map(function($album) {
                    return [
                        'name' => $album['name'],
                        'artist' => $album['artist']['name'],
                        'playcount' => $album['playcount'],
                        'url' => $album['url']
                    ];
                }, $data['topalbums']['album']);
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::error('Error getting top albums: ' . $e->getMessage());
            return [];
        }
    }

    public function getTopArtists($user, $period = 'overall')
    {
        try {
            $response = $this->client->get("http://ws.audioscrobbler.com/2.0/", [
                'query' => [
                    'method' => 'user.gettopartists',
                    'user' => $user,
                    'api_key' => $this->apiKey,
                    'format' => 'json',
                    'period' => $period,
                    'limit' => 5
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['topartists']['artist'])) {
                return array_map(function($artist) {
                    return [
                        'name' => $artist['name'],
                        'playcount' => $artist['playcount'],
                        'url' => $artist['url']
                    ];
                }, $data['topartists']['artist']);
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::error('Error getting top artists: ' . $e->getMessage());
            return [];
        }
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
