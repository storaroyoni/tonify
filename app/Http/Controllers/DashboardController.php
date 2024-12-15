<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
                if ($user && $user->isLastfmConnected()) {
            $topTracks = session('user_top_tracks', []);
            
            return view('dashboard', compact('topTracks'));
        }

        return view('dashboard');
    }
}