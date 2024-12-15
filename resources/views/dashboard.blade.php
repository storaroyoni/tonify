// resources/views/dashboard.blade.php
@extends('layouts.app')

@section('content')
<div class="container">
    @if(auth()->check())
        <h1>Welcome to Your Dashboard, {{ auth()->user()->name }}!</h1>

        @if(auth()->user()->isLastfmConnected())
            <div class="lastfm-stats">
                <h3>Your Last.fm Stats</h3>
                
                <!-- Top Artists -->
                @if(session('user_top_artists'))
                    <div class="stats-section">
                        <h4>Top Artists</h4>
                        <ul>
                        @foreach(session('user_top_artists') as $artist)
                            <li>
                                {{ $artist['name'] }} 
                                ({{ $artist['playcount'] }} plays)
                                <a href="{{ $artist['url'] }}" target="_blank">View on Last.fm</a>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Top Albums -->
                @if(session('user_top_albums'))
                    <div class="stats-section">
                        <h4>Top Albums</h4>
                        <ul>
                        @foreach(session('user_top_albums') as $album)
                            <li>
                                {{ $album['name'] }} by {{ $album['artist'] }}
                                ({{ $album['playcount'] }} plays)
                                <a href="{{ $album['url'] }}" target="_blank">View on Last.fm</a>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Top Tracks -->
                @if(session('user_top_tracks'))
                    <div class="stats-section">
                        <h4>Top Tracks</h4>
                        <ul>
                        @foreach(session('user_top_tracks') as $track)
                            <li>
                                {{ $track['name'] }} by {{ $track['artist'] }}
                                ({{ $track['playcount'] }} plays)
                                <a href="{{ $track['url'] }}" target="_blank">View on Last.fm</a>
                            </li>
                        @endforeach
                        </ul>
                    </div>
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

<style>
.stats-section {
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.stats-section h4 {
    color: #333;
    margin-bottom: 1rem;
}

.stats-section ul {
    list-style: none;
    padding: 0;
}

.stats-section li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.stats-section li:last-child {
    border-bottom: none;
}

.stats-section a {
    margin-left: 1rem;
    color: #007bff;
    text-decoration: none;
}

.stats-section a:hover {
    text-decoration: underline;
}
</style>
@endsection
