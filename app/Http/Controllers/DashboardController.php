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
            $topTracks = $this->lastFmService->getTopTracks($user->lastfm_username, 'overall');
            $topArtists = $this->lastFmService->getTopArtists($user->lastfm_username, 'overall');
            $topAlbums = $this->lastFmService->getTopAlbums($user->lastfm_username, 'overall');
            $recentTracks = $this->lastFmService->getRecentTracks($user->lastfm_username);
            
            $moodAnalysis = $this->moodAnalyzer->analyzeTracks($recentTracks);

            return view('dashboard', compact('topTracks', 'topArtists', 'topAlbums', 'recentTracks', 'moodAnalysis'));
        }

        return view('dashboard');
    }
}