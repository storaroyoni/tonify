<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;
use App\Services\MoodAnalyzer;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\LastFmService;

class DashboardController extends Controller
{
    protected $moodAnalyzer;
    protected $lastFmService;

    public function __construct(MoodAnalyzer $moodAnalyzer, LastFmService $lastFmService)
    {
        $this->moodAnalyzer = $moodAnalyzer;
        $this->lastFmService = $lastFmService;
    }

    public function index()
    {
        $user = auth()->user();
        
        if ($user && $user->isLastfmConnected()) {
            $topTracks = session('user_top_tracks', []);
            $topArtists = session('user_top_artists', []);
            $topAlbums = session('user_top_albums', []);
            
            $recentTracks = $this->lastFmService->getRecentTracks($user->lastfm_username);
            \Log::info('Recent tracks structure:', ['tracks' => $recentTracks]);
            
            $moodAnalysis = $this->moodAnalyzer->analyzeTracks($recentTracks);
            \Log::info('Mood analysis result:', ['analysis' => $moodAnalysis]);

            return view('dashboard', compact('topTracks', 'topArtists', 'topAlbums', 'recentTracks', 'moodAnalysis'));
        }

        return view('dashboard');
    }
}