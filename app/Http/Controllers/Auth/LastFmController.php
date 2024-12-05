<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class LastFmController extends Controller
{
    public function redirectToLastFm()
    {
        $apiKey = config('services.lastfm.api_key');
        $apiSecret = config('services.lastfm.api_secret');
        
        $url = 'https://www.last.fm/api/auth/?api_key=' . $apiKey . '&cb=' . urlencode(route('lastfm.callback'));
                return redirect($url);
    }

    public function handleCallback(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return redirect('/')->with('error', 'Authentication failed.');
        }

        $apiKey = config('services.lastfm.api_key');
        $apiSecret = config('services.lastfm.api_secret');

        $response = Http::get('https://ws.audioscrobbler.com/2.0/', [
            'method' => 'auth.getSession',
            'api_key' => $apiKey,
            'api_sig' => $this->generateSignature($apiKey, $apiSecret, $token),
            'token' => $token,
            'format' => 'json',
        ]);

        if ($response->failed()) {
            return redirect('/')->with('error', 'Unable to fetch session key.');
        }

        $data = $response->json();
        $sessionKey = $data['session']['key'];

        Auth::user()->update(['lastfm_session_key' => $sessionKey]);

        return redirect('/')->with('success', 'Successfully authenticated with Last.fm.');
    }

    private function generateSignature($apiKey, $apiSecret, $token)
    {
        $params = [
            'api_key' => $apiKey,
            'method' => 'auth.getSession',
            'token' => $token,
        ];

        ksort($params);

        $signature = '';
        foreach ($params as $key => $value) {
            $signature .= $key . $value;
        }
        $signature .= $apiSecret;

        return md5($signature);
    }
}

