<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class HomeController extends Controller
{
    public function index()
    {
        $apiKey = config('services.lastfm.api_key');
        $client = new Client();

        $promises = [
            'tracks' => $client->getAsync('http://ws.audioscrobbler.com/2.0/', [
                'query' => [
                    'method' => 'chart.gettoptracks',
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 12
                ]
            ]),
            'artists' => $client->getAsync('http://ws.audioscrobbler.com/2.0/', [
                'query' => [
                    'method' => 'chart.gettopartists',
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 12
                ]
            ]),
            'tags' => $client->getAsync('http://ws.audioscrobbler.com/2.0/', [
                'query' => [
                    'method' => 'tag.getTopTags',
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 6
                ]
            ])
        ];

        $responses = Promise\Utils::unwrap($promises);
        $trackPromises = [];
        $artistPromises = [];
        
        $tracksData = json_decode($responses['tracks']->getBody(), true)['tracks']['track'] ?? [];
        foreach ($tracksData as $track) {
            $trackPromises[$track['name']] = $client->getAsync('http://ws.audioscrobbler.com/2.0/', [
                'query' => [
                    'method' => 'track.getInfo',
                    'api_key' => $apiKey,
                    'artist' => $track['artist']['name'],
                    'track' => $track['name'],
                    'format' => 'json'
                ]
            ]);
        }

        $artistsData = json_decode($responses['artists']->getBody(), true)['artists']['artist'] ?? [];
        foreach ($artistsData as $artist) {
            $artistPromises[$artist['name']] = $client->getAsync('http://ws.audioscrobbler.com/2.0/', [
                'query' => [
                    'method' => 'artist.getTopAlbums',
                    'artist' => $artist['name'],
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 1
                ]
            ]);
        }

        $trackInfos = Promise\Utils::unwrap($trackPromises);
        $artistInfos = Promise\Utils::unwrap($artistPromises);

        $tracks = [];
        foreach ($tracksData as $track) {
            if (isset($trackInfos[$track['name']])) {
                $trackInfo = json_decode($trackInfos[$track['name']]->getBody(), true)['track'] ?? null;
                if (isset($trackInfo['album']['image'])) {
                    $tracks[] = [
                        'name' => $track['name'],
                        'artist' => $track['artist']['name'],
                        'playcount' => $track['playcount'],
                        'image' => end($trackInfo['album']['image'])['#text']
                    ];
                }
            }
            if (count($tracks) >= 6) break;
        }

        // Process artist data
        $artists = [];
        foreach ($artistsData as $artist) {
            if (isset($artistInfos[$artist['name']])) {
                $albumsInfo = json_decode($artistInfos[$artist['name']]->getBody(), true);
                if (isset($albumsInfo['topalbums']['album'][0]['image'])) {
                    $artists[] = [
                        'name' => $artist['name'],
                        'listeners' => $artist['listeners'],
                        'image' => end($albumsInfo['topalbums']['album'][0]['image'])['#text']
                    ];
                }
            }
            if (count($artists) >= 8) break;
        }

        $tagsData = json_decode($responses['tags']->getBody(), true);
        $genres = array_slice(array_map(function($tag) {
            return [
                'name' => $tag['name'],
                'count' => $tag['count'] ?? 0
            ];
        }, $tagsData['toptags']['tag'] ?? []), 0, 6);

        return view('home', ['globalCharts' => [
            'topTracks' => $tracks,
            'trendingArtists' => $artists,
            'genres' => $genres
        ]]);
    }
}