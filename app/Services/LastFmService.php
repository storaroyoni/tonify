<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class LastFmService
{
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        $this->apiKey = env('LASTFM_API_KEY');
        $this->client = new Client();
    }

    protected function createPromise($method, $params = [])
    {
        return $this->client->getAsync("http://ws.audioscrobbler.com/2.0/", [
            'query' => array_merge([
                'method' => $method,
                'api_key' => $this->apiKey,
                'format' => 'json',
            ], $params),
        ]);
    }

    public function getParallelData($username, $period = 'overall')
    {
        $promises = [
            'topTracks' => $this->createPromise('user.gettoptracks', [
                'user' => $username,
                'period' => $period,
                'limit' => 5
            ]),
            'topArtists' => $this->createPromise('user.gettopartists', [
                'user' => $username,
                'period' => $period,
                'limit' => 5
            ]),
            'topAlbums' => $this->createPromise('user.gettopalbums', [
                'user' => $username,
                'period' => $period,
                'limit' => 5
            ]),
            'recentTracks' => $this->createPromise('user.getrecenttracks', [
                'user' => $username,
                'limit' => 50,
                'extended' => 1
            ])
        ];

        try {
            $responses = Promise\Utils::unwrap($promises);
            
            return [
                'topTracks' => $this->processTopTracks($responses['topTracks']),
                'topArtists' => $this->processTopArtists($responses['topArtists']),
                'topAlbums' => $this->processTopAlbums($responses['topAlbums']),
                'recentTracks' => $this->processRecentTracks($responses['recentTracks'])
            ];
        } catch (\Exception $e) {
            \Log::error('Error in parallel requests: ' . $e->getMessage());
            return [
                'topTracks' => [],
                'topArtists' => [],
                'topAlbums' => [],
                'recentTracks' => []
            ];
        }
    }

    protected function processTopTracks($response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        return isset($data['toptracks']['track']) ? array_map(function($track) {
            return [
                'name' => $track['name'],
                'artist' => $track['artist']['name'] ?? $track['artist']['#text'],
                'playcount' => $track['playcount'],
                'url' => $track['url']
            ];
        }, $data['toptracks']['track']) : [];
    }

    protected function processTopArtists($response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        return isset($data['topartists']['artist']) ? array_map(function($artist) {
            return [
                'name' => $artist['name'],
                'playcount' => $artist['playcount'],
                'url' => $artist['url']
            ];
        }, $data['topartists']['artist']) : [];
    }

    protected function processTopAlbums($response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        return isset($data['topalbums']['album']) ? array_map(function($album) {
            return [
                'name' => $album['name'],
                'artist' => $album['artist']['name'],
                'playcount' => $album['playcount'],
                'url' => $album['url']
            ];
        }, $data['topalbums']['album']) : [];
    }

    protected function processRecentTracks($response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        return isset($data['recenttracks']['track']) ? array_map(function($track) {
            return [
                'name' => $track['name'],
                'artist' => $track['artist']['name'] ?? $track['artist']['#text'],
                'image' => $this->getLargestImage($track['image'] ?? []),
                'url' => $track['url']
            ];
        }, $data['recenttracks']['track']) : [];
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
