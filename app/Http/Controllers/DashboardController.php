<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;
use App\Services\MoodAnalyzer;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\LastFmService;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Promise;

class DashboardController extends Controller
{
    protected $moodAnalyzer;
    protected $lastFmService;
    protected $cacheTimeout = 300; 

    public function __construct(MoodAnalyzer $moodAnalyzer, LastFmService $lastFmService)
    {
        $this->moodAnalyzer = $moodAnalyzer;
        $this->lastFmService = $lastFmService;
    }

    public function index()
    {
        $user = auth()->user();
        
        if (!$user || !$user->isLastfmConnected()) {
            return view('dashboard');
        }

        $data = Cache::remember("dashboard_data_{$user->id}", $this->cacheTimeout, function () use ($user) {
            try {
                $results = $this->lastFmService->getParallelData($user->lastfm_username);
                $results['moodAnalysis'] = $this->moodAnalyzer->analyzeTracks($results['recentTracks']);
                return $results;
            } catch (\Exception $e) {
                \Log::error('Error fetching LastFm data: ' . $e->getMessage());
                return [
                    'topTracks' => [],
                    'topArtists' => [],
                    'topAlbums' => [],
                    'recentTracks' => [],
                    'moodAnalysis' => []
                ];
            }
        });

        return view('dashboard', $data);
    }
}