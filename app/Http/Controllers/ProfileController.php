<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use GuzzleHttp\Promise;
use GuzzleHttp\Client;

class ProfileController extends Controller
{
    protected $cacheTimeout = 180; 
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.lastfm.api_key');
        $this->client = new Client([
            'base_uri' => 'https://ws.audioscrobbler.com/2.0/',
        ]);
    }

    public function show($username)
    {
        $user = User::where('name', $username)->firstOrFail();
        $stats = [];
        
        if ($user->lastfm_username) {
            $stats = Cache::remember("user_stats_{$user->id}", $this->cacheTimeout, function () use ($user) {
                return $this->fetchUserData($user->lastfm_username);
            });
        }

        return view('profile', compact('user', 'stats'));
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

    private function fetchUserData($name)
    {
        try {
            $promises = [
                'nowPlaying' => $this->createPromise('user.getrecenttracks', [
                    'user' => $name,
                    'limit' => 1
                ]),
                'recent' => $this->createPromise('user.getrecenttracks', [
                    'user' => $name,
                    'limit' => 10
                ]),
                'info' => $this->createPromise('user.getinfo', [
                    'user' => $name
                ]),
                'weeklyArtists' => $this->createPromise('user.getweeklyartistchart', [
                    'user' => $name
                ]),
                'topTracks' => $this->createPromise('user.gettoptracks', [
                    'user' => $name,
                    'period' => '7day',
                    'limit' => 5
                ]),
                'topArtists' => $this->createPromise('user.gettopartists', [
                    'user' => $name,
                    'period' => '7day',
                    'limit' => 5
                ]),
                'topAlbums' => $this->createPromise('user.gettopalbums', [
                    'user' => $name,
                    'period' => '7day',
                    'limit' => 5
                ]),
                'weeklyTracks' => $this->createPromise('user.getrecenttracks', [
                    'user' => $name,
                    'limit' => 200,
                    'from' => now()->subWeek()->timestamp
                ])
            ];

            $responses = Promise\Utils::unwrap($promises);
            
            $recentTracks = [];
            $recentData = json_decode($responses['recent']->getBody(), true);
            if (isset($recentData['recenttracks']['track'])) {
                $recentTracks = collect($recentData['recenttracks']['track'])
                    ->filter(fn($track) => !isset($track['@attr']['nowplaying']))
                    ->unique(fn($track) => $track['name'] . ' - ' . $track['artist']['#text'])
                    ->map(function($track) {
                        $image = null;
                        
                        if (isset($track['image'])) {
                            foreach ($track['image'] as $img) {
                                if ($img['size'] === 'extralarge' && !empty($img['#text'])) {
                                    $image = $img['#text'];
                                    break;
                                }
                            }
                            
                            if (!$image) {
                                foreach ($track['image'] as $img) {
                                    if ($img['size'] === 'large' && !empty($img['#text'])) {
                                        $image = $img['#text'];
                                        break;
                                    }
                                }
                            }
                        }

                        return [
                            'name' => $track['name'],
                            'artist' => $track['artist']['#text'],
                            'album' => $track['album']['#text'] ?? '',
                            'image' => $image,
                            'url' => $track['url'],
                            'played_at' => isset($track['date']) ? 
                                Carbon::createFromTimestamp($track['date']['uts'])->diffForHumans() : null
                        ];
                    })
                    ->take(4)
                    ->values()
                    ->toArray();
            }

            $userInfo = json_decode($responses['info']->getBody(), true);
            $totalScrobbles = $userInfo['user']['playcount'] ?? 0;

            $topTracksData = json_decode($responses['topTracks']->getBody(), true);
            $topArtistsData = json_decode($responses['topArtists']->getBody(), true);
            $topAlbumsData = json_decode($responses['topAlbums']->getBody(), true);

            $stats = [
                'total_scrobbles' => $totalScrobbles,
                'top_tracks' => isset($topTracksData['toptracks']['track']) ? 
                    collect($topTracksData['toptracks']['track'])
                        ->take(5)
                        ->map(fn($track) => [
                            'name' => $track['name'],
                            'artist' => $track['artist']['name'],
                            'playcount' => $track['playcount'],
                            'url' => $track['url'],
                        ])->toArray() : [],
                'top_artists' => isset($topArtistsData['topartists']['artist']) ?
                    collect($topArtistsData['topartists']['artist'])
                        ->take(5)
                        ->map(fn($artist) => [
                            'name' => $artist['name'],
                            'playcount' => $artist['playcount'],
                            'url' => $artist['url'],
                        ])->toArray() : [],
                'top_albums' => isset($topAlbumsData['topalbums']['album']) ?
                    collect($topAlbumsData['topalbums']['album'])
                        ->take(5)
                        ->map(fn($album) => [
                            'name' => $album['name'],
                            'artist' => $album['artist']['name'],
                            'playcount' => $album['playcount'],
                            'url' => $album['url'],
                        ])->toArray() : [],
                'recent_tracks' => $recentTracks
            ];

            session(['user_top_tracks' => $stats['top_tracks']]);
            session(['user_top_artists' => $stats['top_artists']]);
            session(['user_top_albums' => $stats['top_albums']]);

            return $stats;

        } catch (\Exception $e) {
            \Log::error('Error fetching user data: ' . $e->getMessage());
            return [
                'total_scrobbles' => 0,
                'recent_tracks' => [],
                'top_tracks' => [],
                'top_artists' => [],
                'top_albums' => []
            ];
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

            if ($user->profile_picture) {
                \Log::info('Deleting old profile picture', [
                    'path' => $user->profile_picture
                ]);
                Storage::disk('public')->delete($user->profile_picture);
            }

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

