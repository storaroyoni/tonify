<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function show($username)
    {
        $user = User::where('name', $username)->firstOrFail();
        $stats = [];
        
        if ($user->lastfm_username) {
            $stats = $this->fetchUserStats($user);
        }

        return view('profile', compact('user', 'stats'));
    }

    private function fetchUserStats($user)
    {
        $apiKey = config('services.lastfm.api_key');
        
        try {
            $nowPlayingResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.getrecenttracks',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'limit' => 1
            ]);

            \Log::info('Now Playing Response:', [
                'data' => $nowPlayingResponse->json()
            ]);

            // check for now playing
            if ($nowPlayingResponse->successful()) {
                $track = $nowPlayingResponse['recenttracks']['track'][0] ?? null;
                
                if ($track) {
                    \Log::info('Track details:', [
                        'name' => $track['name'],
                        'artist' => $track['artist']['#text'] ?? 'no artist',
                        'has_nowplaying' => isset($track['@attr']['nowplaying']),
                        'attr' => $track['@attr'] ?? 'no attr'
                    ]);

                    if (isset($track['@attr']['nowplaying']) && $track['@attr']['nowplaying'] === 'true') {
                        $stats['now_playing'] = [
                            'name' => $track['name'],
                            'artist' => $track['artist']['#text'],
                            'album' => $track['album']['#text'] ?? '',
                            'image' => $track['image'][2]['#text'] ?? null,  // Using index 2 for medium size
                            'url' => $track['url']
                        ];
                    }
                }
            }

            $recentResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.getrecenttracks',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'limit' => 10
            ]);

            $infoResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.getinfo',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json'
            ]);

            // Get Weekly Chart
            $weeklyResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.getweeklyartistchart',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json'
            ]);

            if ($recentResponse->successful() && isset($recentResponse['recenttracks']['track'])) {
                $tracks = collect($recentResponse['recenttracks']['track']);
                
                    $recentTracks = $tracks
                    ->filter(function ($track) {
                        return !isset($track['@attr']['nowplaying']);
                    })
                    ->unique(function ($track) {
                        return $track['name'] . ' - ' . $track['artist']['#text'];
                    })
                    ->map(function ($track) {
                        return [
                            'name' => $track['name'],
                            'artist' => $track['artist']['#text'],
                            'album' => $track['album']['#text'],
                            'image' => $track['image'][2]['#text'] ?? null,
                            'url' => $track['url'],
                            'played_at' => isset($track['date']) ? Carbon::createFromTimestamp($track['date']['uts'])->diffForHumans() : null
                        ];
                    })
                    ->take(4)
                    ->values()
                    ->toArray();
            }

            // total scrobbles
            if ($infoResponse->successful() && isset($infoResponse['user'])) {
                $totalScrobbles = $infoResponse['user']['playcount'];
            }

            // weekly chart
            if ($weeklyResponse->successful() && isset($weeklyResponse['weeklyartistchart']['artist'])) {
                $weeklyChart = collect($weeklyResponse['weeklyartistchart']['artist'])
                    ->take(10)
                    ->map(function ($artist) {
                        return [
                            'name' => $artist['name'],
                            'playcount' => $artist['playcount'],
                            'url' => $artist['url']
                        ];
                    })->toArray();
            }

            $tracksResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.gettoptracks',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'period' => '7day',
                'limit' => 50
            ]);

            $artistsResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.gettopartists',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'period' => '7day',
                'limit' => 20
            ]);

            $albumsResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.gettopalbums',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'period' => '7day',
                'limit' => 20
            ]);

            if ($tracksResponse->successful() && isset($tracksResponse['toptracks']['track'])) {
                $topTracks = collect($tracksResponse['toptracks']['track'])
                    ->take(5)
                    ->map(function ($track) {
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
                $topArtists = collect($artistsResponse['topartists']['artist'])
                    ->take(5)
                    ->map(function ($artist) {
                        return [
                            'name' => $artist['name'],
                            'playcount' => $artist['playcount'],
                            'url' => $artist['url'],
                        ];
                    })->toArray();
                session(['user_top_artists' => $topArtists]);
            }

            if ($albumsResponse->successful() && isset($albumsResponse['topalbums']['album'])) {
                $topAlbums = collect($albumsResponse['topalbums']['album'])
                    ->take(5)
                    ->map(function ($album) {
                        return [
                            'name' => $album['name'],
                            'artist' => $album['artist']['name'],
                            'playcount' => $album['playcount'],
                            'url' => $album['url'],
                        ];
                    })->toArray();
                session(['user_top_albums' => $topAlbums]);
            }

            $recentTracksResponse = Http::get('https://ws.audioscrobbler.com/2.0/', [
                'method' => 'user.getrecenttracks',
                'user' => $user->lastfm_username,
                'api_key' => $apiKey,
                'format' => 'json',
                'limit' => 200,  
                'from' => now()->subWeek()->timestamp 
            ]);

            if ($recentTracksResponse->successful() && isset($recentTracksResponse['recenttracks']['track'])) {
                $tracks = collect($recentTracksResponse['recenttracks']['track'])
                    ->filter(function($track) {
                        return !isset($track['@attr']['nowplaying']);
                    });

                $dailyListening = $tracks->groupBy(function($track) {
                    return Carbon::createFromTimestamp($track['date']['uts'])->format('Y-m-d');
                })->map->count();

                $hourlyDistribution = $tracks->groupBy(function($track) {
                    return Carbon::createFromTimestamp($track['date']['uts'])->format('H');
                })->map->count();

                $mostActiveHours = collect($hourlyDistribution)
                    ->sortDesc()
                    ->take(3)
                    ->map(function($count, $hour) {
                        return [
                            'hour' => sprintf('%02d:00', $hour),
                            'count' => $count
                        ];
                    });

                // Add to stats array
                $stats['listening_stats'] = [
                    'daily_average' => round($tracks->count() / 7),  // Average tracks per day
                    'total_week' => $tracks->count(),  // Total tracks this week
                    'most_active_hours' => $mostActiveHours,
                    'daily_breakdown' => $dailyListening
                ];
            }

            return [
                'now_playing' => $stats['now_playing'] ?? null,
                'recent_tracks' => $recentTracks ?? [],
                'total_scrobbles' => $totalScrobbles ?? 0,
                'weekly_chart' => $weeklyChart ?? [],
                'top_tracks' => $topTracks ?? [],
                'top_artists' => $topArtists ?? [],
                'top_albums' => $topAlbums ?? [],
                'listening_stats' => $stats['listening_stats'] ?? null,
                'fetched_at' => now()
            ];

        } catch (\Exception $e) {
            \Log::error('Error fetching Last.fm stats', [
                'error' => $e->getMessage()
            ]);
            return [];
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
