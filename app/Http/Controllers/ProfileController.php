<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show($username)
    {
        $user = User::where('name', $username)->firstOrFail();
                if ($user->lastfm_username) {
            $this->fetchUserStats($user);
        }

        return view('profile', compact('user'));
    }

    private function fetchUserStats($user)
    {
        $apiKey = config('services.lastfm.api_key');

        try {
            // Fetch top tracks
            $tracksResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.gettoptracks',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'period' => 'overall',
                'limit' => 50
            ]);

            // Fetch top artists
            $artistsResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.gettopartists',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'period' => 'overall',
                'limit' => 20
            ]);

            // Fetch top albums
            $albumsResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.gettopalbums',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'period' => 'overall',
                'limit' => 20
            ]);

            if ($tracksResponse->successful() && isset($tracksResponse['toptracks']['track'])) {
                $topTracks = collect($tracksResponse['toptracks']['track'])->map(function ($track) {
                    return [
                        'name' => $track['name'],
                        'artist' => $track['artist']['name'],
                        'playcount' => $track['playcount'],
                        'url' => $track['url'],
                    ];
                })->toArray();
                session(['user_top_tracks' => $topTracks]);
            }

            if ($artistsResponse->successful() && isset($artistsResponse['topartists']['artist'])) {
                $topArtists = collect($artistsResponse['topartists']['artist'])->map(function ($artist) {
                    return [
                        'name' => $artist['name'],
                        'playcount' => $artist['playcount'],
                        'url' => $artist['url'],
                    ];
                })->toArray();
                session(['user_top_artists' => $topArtists]);
            }

            if ($albumsResponse->successful() && isset($albumsResponse['topalbums']['album'])) {
                $topAlbums = collect($albumsResponse['topalbums']['album'])->map(function ($album) {
                    return [
                        'name' => $album['name'],
                        'artist' => $album['artist']['name'],
                        'playcount' => $album['playcount'],
                        'url' => $album['url'],
                    ];
                })->toArray();
                session(['user_top_albums' => $topAlbums]);
            }

        } catch (\Exception $e) {
            Log::error('Error fetching Last.fm stats', [
                'user' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|max:1024',
        ]);

        if ($request->hasFile('profile_picture')) {
            \Log::info('Uploading profile picture', [
                'original_name' => $request->file('profile_picture')->getClientOriginalName()
            ]);

            // deleting the old profile picture if it exists
            if ($user->profile_picture) {
                \Log::info('Deleting old profile picture', [
                    'path' => $user->profile_picture
                ]);
                Storage::disk('public')->delete($user->profile_picture);
            }

            // storing the new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            \Log::info('Stored new profile picture', [
                'path' => $path
            ]);
            $validated['profile_picture'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.show', $user->name)
            ->with('success', 'Profile updated successfully!');
    }
}
