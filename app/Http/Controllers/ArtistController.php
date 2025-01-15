<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Cache;

class ArtistController extends Controller
{
    protected $client;
    protected $apiKey;
    protected $cacheTimeout = 300; 

    public function __construct()
    {
        $this->apiKey = config('services.lastfm.api_key');
        $this->client = new Client([
            'base_uri' => 'https://ws.audioscrobbler.com/2.0/',
        ]);
    }

    public function show($name)
    {
        $cacheKey = "artist_details_" . md5($name);
        
        $data = Cache::remember($cacheKey, $this->cacheTimeout, function () use ($name) {
            return $this->fetchArtistData($name);
        });

        $data['spotifyUrl'] = "https://open.spotify.com/search/" . urlencode($name);
        
        return view('artists.show', $data);
    }

    private function createPromise($method, $params = [])
    {
        return $this->client->getAsync('', [
            'query' => array_merge([
                'method' => $method,
                'api_key' => $this->apiKey,
                'format' => 'json',
            ], $params),
        ]);
    }

    private function fetchArtistData($name)
    {
        try {
            $promises = [
                'artist' => $this->createPromise('artist.getInfo', ['artist' => $name]),
                'albums' => $this->createPromise('artist.getTopAlbums', [
                    'artist' => $name,
                    'limit' => 1
                ]),
                'similar' => $this->createPromise('artist.getSimilar', [
                    'artist' => $name,
                    'limit' => 12
                ])
            ];

            $responses = Promise\Utils::unwrap($promises);
            
            $artistData = json_decode($responses['artist']->getBody(), true)['artist'] ?? null;
            
            $albumsData = json_decode($responses['albums']->getBody(), true);
            if (isset($albumsData['topalbums']['album'][0]['image'])) {
                $albumImages = $albumsData['topalbums']['album'][0]['image'];
                $artistData['mainImage'] = end($albumImages)['#text'];
            }

            $similarArtists = [];
            $similarData = json_decode($responses['similar']->getBody(), true);
            
            if (isset($similarData['similarartists']['artist'])) {
                $similarPromises = [];
                foreach (array_slice($similarData['similarartists']['artist'], 0, 6) as $similar) {
                    $similarPromises[$similar['name']] = [
                        'albums' => $this->createPromise('artist.getTopAlbums', [
                            'artist' => $similar['name'],
                            'limit' => 1
                        ]),
                        'info' => $this->createPromise('artist.getInfo', [
                            'artist' => $similar['name']
                        ])
                    ];
                }

                $similarResponses = [];
                foreach ($similarPromises as $artistName => $promises) {
                    $similarResponses[$artistName] = Promise\Utils::unwrap($promises);
                }

                foreach ($similarResponses as $artistName => $response) {
                    $albumsData = json_decode($response['albums']->getBody(), true);
                    $infoData = json_decode($response['info']->getBody(), true);

                    if (isset($albumsData['topalbums']['album'][0]['image'])) {
                        $albumImages = $albumsData['topalbums']['album'][0]['image'];
                        $albumImage = end($albumImages)['#text'];

                        if ($albumImage) {
                            $similarArtists[] = [
                                'name' => $artistName,
                                'image' => $albumImage,
                                'listeners' => $infoData['artist']['stats']['listeners'] ?? 0
                            ];
                        }
                    }
                }
            }

            if (isset($artistData['bio']['summary'])) {
                $artistData['bio']['summary'] = preg_replace(
                    '/<a href=".*?>Read more on Last\.fm<\/a>/', 
                    '', 
                    $artistData['bio']['summary']
                );
            }

            return [
                'artist' => $artistData,
                'similarArtists' => $similarArtists
            ];

        } catch (\Exception $e) {
            \Log::error('Error fetching artist data: ' . $e->getMessage());
            return [
                'artist' => null,
                'similarArtists' => []
            ];
        }
    }
} 