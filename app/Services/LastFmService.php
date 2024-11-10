<?php

namespace App\Services;

use GuzzleHttp\Client;

class LastFmService
{
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        // Retrieve the Last.fm API key from the .env file
        $this->apiKey = env('LASTFM_API_KEY');
        $this->client = new Client();
    }

    // Get the top tracks for a given user.
     
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

    // Get the top albums for a given user.
     
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

    // Get the top artists for a given user.
     
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

    // Get a list of similar artists to a given artist.
    
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

}
