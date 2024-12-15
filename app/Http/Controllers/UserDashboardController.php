<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // HTTP client for making API requests

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $lastfmData = $user->lastfmData;
        $hasLastfmAccess = false;

        if ($lastfmData && $lastfmData->access_token) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $lastfmData->access_token
            ])->get('https://ws.audioscrobbler.com/2.0/?method=auth.getSession&api_key=' . env('LASTFM_API_KEY'));

            if ($response->successful() && $response->json('user')) {
                $hasLastfmAccess = true;
            }
        }

        if ($hasLastfmAccess) {
            $topTracks = $lastfmData->top_tracks; 
            $topArtists = $lastfmData->top_artists; 
            $topAlbums = $lastfmData->top_albums; 
        } else {
            $topTracks = [];
            $topArtists = [];
            $topAlbums = [];
        }

        return view('dashboard', compact('user', 'hasLastfmAccess', 'topTracks', 'topArtists', 'topAlbums'));
    }
}
