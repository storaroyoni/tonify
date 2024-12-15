<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function show(User $user)
    {
        $lastfmData = $user->lastfmData;

        if ($lastfmData) {
            $topTracks = $lastfmData->top_tracks;
            // to do : fetch other data

        } else {
            $topTracks = [];
        }

        return view('dashboard', compact('user', 'topTracks'));
    }
}
