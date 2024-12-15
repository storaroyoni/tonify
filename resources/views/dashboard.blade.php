// resources/views/dashboard.blade.php
@extends('layouts.app')

@section('content')
<div class="container">
    @if(auth()->check())
        <h1>Welcome to Your Dashboard, {{ auth()->user()->name }}!</h1>

        <div class="user-info">
            <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>Joined:</strong> {{ auth()->user()->created_at->format('M d, Y') }}</p>
        </div>

        @if(auth()->user()->isLastfmConnected())
            <div class="lastfm-stats">
                <h3>Last.fm Stats</h3>
                @if(session('user_top_tracks'))
                    <h4>Your Top Tracks:</h4>
                    <ul>
                    @foreach(session('user_top_tracks') as $track)
                        <li>
                            {{ $track['name'] }} by {{ $track['artist'] }} 
                            ({{ $track['playcount'] }} plays)
                            <a href="{{ $track['url'] }}" target="_blank">View on Last.fm</a>
                        </li>
                    @endforeach
                    </ul>
                @else
                    <p>Loading your Last.fm data...</p>
                @endif
            </div>
        @else
            <div class="connect-lastfm">
                <p>Connect your Last.fm account to see your music stats!</p>
                <a href="{{ route('lastfm.auth') }}" class="btn btn-primary">
                    Connect Last.fm Account
                </a>
            </div>
        @endif
    @else
        <p>Please log in to view your dashboard.</p>
    @endif
</div>
@endsection
