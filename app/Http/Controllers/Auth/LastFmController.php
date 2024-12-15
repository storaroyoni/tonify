<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LastFmController extends Controller
{
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

            $this->fetchUserTopTracks($user);

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

    private function fetchUserTopTracks($user)
    {
        $apiKey = config('services.lastfm.api_key');

        try {
            $response = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.gettoptracks',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'period' => 'overall',
                'limit' => 50
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['toptracks']['track'])) {
                    $topTracks = collect($data['toptracks']['track'])->map(function ($track) {
                        return [
                            'name' => $track['name'],
                            'artist' => $track['artist']['name'],
                            'playcount' => $track['playcount'],
                            'url' => $track['url'],
                        ];
                    })->toArray();
                    session(['user_top_tracks' => $topTracks]);
                    
                    Log::info('Top tracks fetched successfully', [
                        'user' => $user->id,
                        'tracks_count' => count($topTracks)
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching top tracks', [
                'user' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}