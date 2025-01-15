<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Cache;


class SongController extends Controller
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

    public function show($name, $artist)
    {
        try {
            $name = rawurldecode(str_replace(['!', '?', '#'], ['%21', '%3F', '%23'], $name));
            $artist = rawurldecode($artist);
            
            $cacheKey = "song_details_" . md5($name . $artist);
            
            $data = Cache::remember($cacheKey, $this->cacheTimeout, function () use ($name, $artist) {
                return $this->fetchSongData($name, $artist);
            });

            if (empty($data['song'])) {
                return back()->with('error', 'Song not found.');
            }

            $data['spotifyUrl'] = "https://open.spotify.com/search/" . urlencode($name . " " . $artist);
            
            return view('songs.show', $data);
        } catch (\Exception $e) {
            \Log::error('Error in song show method: ' . $e->getMessage());
            return back()->with('error', 'Unable to load song details.');
        }
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

    private function fetchSongData($name, $artist)
    {
        try {
            $promises = [
                'song' => $this->createPromise('track.getInfo', [
                    'artist' => $artist,
                    'track' => $name,
                    'autocorrect' => 1
                ]),
                'similar' => $this->createPromise('track.getSimilar', [
                    'artist' => $artist,
                    'track' => $name,
                    'autocorrect' => 1,
                    'limit' => 12
                ])
            ];

            $responses = Promise\Utils::unwrap($promises);
            
            $songData = json_decode($responses['song']->getBody(), true);
            if (!isset($songData['track'])) {
                \Log::error('Song data not found', [
                    'artist' => $artist,
                    'track' => $name,
                    'response' => $songData
                ]);
                return [
                    'song' => null,
                    'similarTracks' => []
                ];
            }

            $similarTracks = [];
            $similarData = json_decode($responses['similar']->getBody(), true);
            
            if (isset($similarData['similartracks']['track'])) {
                $similarPromises = [];
                foreach ($similarData['similartracks']['track'] as $track) {
                    if (count($similarTracks) >= 6) break;
                    
                    $similarPromises[$track['name']] = $this->createPromise('track.getInfo', [
                        'artist' => $track['artist']['name'],
                        'track' => $track['name'],
                        'autocorrect' => 1
                    ]);
                }

                $similarResponses = Promise\Utils::unwrap($similarPromises);

                foreach ($similarData['similartracks']['track'] as $track) {
                    if (count($similarTracks) >= 6) break;
                    
                    if (isset($similarResponses[$track['name']])) {
                        $trackInfo = json_decode($similarResponses[$track['name']]->getBody(), true);
                        if (isset($trackInfo['track']['album']['image'])) {
                            $similarTracks[] = [
                                'name' => $track['name'],
                                'artist' => [
                                    'name' => $track['artist']['name']
                                ],
                                'playcount' => $trackInfo['track']['playcount'] ?? 0,
                                'image' => $trackInfo['track']['album']['image']
                            ];
                        }
                    }
                }
            }

            $song = $songData['track'];

            return [
                'song' => $song, 
                'similarTracks' => $similarTracks
            ];
        } catch (\Exception $e) {
            \Log::error('Error fetching song data: ' . $e->getMessage(), [
                'artist' => $artist,
                'track' => $name
            ]);
            return [
                'song' => null,
                'similarTracks' => []
            ];
        }
    }
}
