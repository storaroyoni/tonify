<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\MusicCacheService;

class LastFmController extends Controller
{
    protected $musicCache;

    public function __construct(MusicCacheService $musicCache)
    {
        $this->musicCache = $musicCache;
    }

    public function redirectToLastFm()
    {
        $apiKey = config('services.lastfm.api_key');
        $callbackUrl = url('/lastfm/callback');
        $url = 'https://www.last.fm/api/auth/?api_key=' . $apiKey . '&cb=' . urlencode($callbackUrl);
        return redirect($url);
    }

    public function handleCallback(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect('/')->with('error', 'Authentication failed: No token received.');
        }

        $apiKey = config('services.lastfm.api_key');
        $apiSecret = config('services.lastfm.api_secret');

        try {
            $signature = $this->generateSignature($apiKey, $apiSecret, $token);

            $params = [
                'method' => 'auth.getSession',
                'api_key' => $apiKey,
                'token' => $token,
                'api_sig' => $signature,
                'format' => 'json'
            ];

            $response = Http::get('https://ws.audioscrobbler.com/2.0/', $params);

            if ($response->failed()) {
                Log::error('LastFM API Session Request Failed', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                return redirect('/')->with('error', 'Unable to fetch session key.');
            }

            $data = $response->json();

            if (!isset($data['session']['key']) || !isset($data['session']['name'])) {
                Log::error('Invalid LastFM session response', ['data' => $data]);
                return redirect('/')->with('error', 'Invalid authentication response.');
            }

            $sessionKey = $data['session']['key'];
            $lastfmUsername = $data['session']['name'];

            $user = Auth::user();

            if (!$user) {
                $user = User::updateOrCreate(
                    ['lastfm_username' => $lastfmUsername],
                    [
                        'name' => $lastfmUsername,
                        'email' => $lastfmUsername . '@lastfm.temp',
                        'password' => bcrypt(uniqid()),
                        'lastfm_session_key' => $sessionKey,
                        'lastfm_connected_at' => now(),
                    ]
                );
            } else {
                $user->update([
                    'lastfm_username' => $lastfmUsername,
                    'lastfm_session_key' => $sessionKey,
                    'lastfm_connected_at' => now(),
                ]);
            }

            if (!Auth::check()) {
                Auth::login($user);
            }

            $this->fetchUserStats($user);

            return redirect('/dashboard')->with('success', 'Successfully authenticated with Last.fm');

        } catch (\Exception $e) {
            Log::error('LastFM Authentication Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/')->with('error', 'An unexpected error occurred during authentication.');
        }
    }

    private function generateSignature($apiKey, $apiSecret, $token)
    {
        $params = [
            'api_key' => $apiKey,
            'method' => 'auth.getSession',
            'token' => $token,
        ];

        ksort($params);

        $signatureString = '';
        foreach ($params as $key => $value) {
            $signatureString .= $key . $value;
        }
        $signatureString .= $apiSecret;

        return md5($signatureString);
    }

    private function fetchUserStats($user)
    {
        try {
            // User-specific data stays in session
            session(['user_top_tracks' => $topTracks]);
            session(['user_top_artists' => $topArtists]);
            session(['user_top_albums' => $topAlbums]);

            // Get cached trending tracks
            $trendingTracks = $this->musicCache->getTrendingTracks();

            // Get cached artist info for top artists
            $artistsInfo = collect($topArtists)->map(function ($artist) {
                return $this->musicCache->getArtistInfo($artist['name']);
            });

            // Make data available to views
            view()->share('trending_tracks', $trendingTracks);
            view()->share('artists_info', $artistsInfo);

        } catch (\Exception $e) {
            Log::error('Error fetching stats', [
                'user' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}