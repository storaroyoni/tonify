@extends('layouts.app')

@section('content')
<div class="container">
    @if(auth()->check())
        <h1>Welcome to Your Dashboard, {{ auth()->user()->name }}!</h1>

        @if(auth()->user()->isLastfmConnected())
            <div class="lastfm-stats">
                <h3 class="top-music-header">TOP MUSIC</h3>
                
                <div class="stats-grid">
                    <!-- Artists Card -->
                    <div class="stats-card">
                        <div class="card-header">
                            <span>Artists</span>
                            <span class="count">{{ count(session('user_top_artists', [])) }}</span>
                        </div>
                        @if(session('user_top_artists'))
                            <div class="card-content">
                                <div class="top-item">
                                    <span class="label">Top Artist</span>
                                    <h3>{{ session('user_top_artists')[0]['name'] }}</h3>
                                    <span class="scrobbles">{{ session('user_top_artists')[0]['playcount'] }} scrobbles</span>
                                </div>
                                <div class="other-items">
                                    @foreach(array_slice(session('user_top_artists'), 1, 4) as $index => $artist)
                                        <div class="item">
                                            <span class="rank">#{{ $index + 2 }}</span>
                                            <span class="name">{{ $artist['name'] }}</span>
                                            <span class="count">{{ $artist['playcount'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Albums Card -->
                    <div class="stats-card">
                        <div class="card-header">
                            <span>Albums</span>
                            <span class="count">{{ count(session('user_top_albums', [])) }}</span>
                        </div>
                        @if(session('user_top_albums'))
                            <div class="card-content">
                                <div class="top-item">
                                    <span class="label">Top Album</span>
                                    <h3>{{ session('user_top_albums')[0]['name'] }}</h3>
                                    <span class="scrobbles">{{ session('user_top_albums')[0]['playcount'] }} scrobbles</span>
                                </div>
                                <div class="other-items">
                                    @foreach(array_slice(session('user_top_albums'), 1, 4) as $index => $album)
                                        <div class="item">
                                            <span class="rank">#{{ $index + 2 }}</span>
                                            <span class="name">{{ $album['name'] }}</span>
                                            <span class="count">{{ $album['playcount'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Tracks Card -->
                    <div class="stats-card">
                        <div class="card-header">
                            <span>Tracks</span>
                            <span class="count">{{ count(session('user_top_tracks', [])) }}</span>
                        </div>
                        @if(session('user_top_tracks'))
                            <div class="card-content">
                                <div class="top-item">
                                    <span class="label">Top Track</span>
                                    <h3>{{ session('user_top_tracks')[0]['name'] }}</h3>
                                    <span class="scrobbles">{{ session('user_top_tracks')[0]['playcount'] }} scrobbles</span>
                                </div>
                                <div class="other-items">
                                    @foreach(array_slice(session('user_top_tracks'), 1, 4) as $index => $track)
                                        <div class="item">
                                            <span class="rank">#{{ $index + 2 }}</span>
                                            <span class="name">{{ $track['name'] }}</span>
                                            <span class="count">{{ $track['playcount'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Mood Board -->
                @if(isset($moodAnalysis))
                    <div class="stats-section mood-board">
                        <h4>Mood Analysis</h4>
                        <div class="mood-stats">
                            <div class="mood-primary">
                                <h5>Current Mood</h5>
                                <p class="mood-value">{{ $moodAnalysis['primary_mood'] }}</p>
                            </div>
                            
                            <div class="mood-levels">
                                <div class="mood-level">
                                    <h5>Energy Level</h5>
                                    <p class="mood-value">{{ $moodAnalysis['energy_level'] }}%</p>
                                </div>
                                <div class="mood-level">
                                    <h5>Happiness Level</h5>
                                    <p class="mood-value">{{ $moodAnalysis['happiness_level'] }}%</p>
                                </div>
                            </div>

                            <div class="mood-distribution">
                                <h5>Mood Distribution</h5>
                                @foreach($moodAnalysis['mood_scores'] as $mood => $score)
                                    <div class="mood-row">
                                        <span class="mood-label">{{ ucfirst($mood) }}</span>
                                        <div class="mood-progress">
                                            <div class="mood-progress-bar" style="width: {{ $score }}%"></div>
                                        </div>
                                        <span class="mood-score">{{ round($score) }}%</span>
                                        
                                        @if(!empty($moodAnalysis['mood_tracks'][$mood]))
                                            <div class="mood-tracks">
                                                <small>
                                                    Contributing tracks:
                                                    @foreach($moodAnalysis['mood_tracks'][$mood] as $track)
                                                        {{ $track['artist'] }} - {{ $track['name'] }}
                                                        @if(!$loop->last), @endif
                                                    @endforeach
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="connect-lastfm">
                <p>Connect your Last.fm account to see your music stats!</p>
                <a href="{{ route('lastfm.auth') }}" class="btn">Connect Last.fm Account</a>
            </div>
        @endif
    @else
        <p>Please log in to view your dashboard.</p>
    @endif
</div>

<style>
.top-music-header {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stats-card {
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-header {
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    font-weight: 500;
}

.card-content {
    padding: 1rem;
}

.top-item {
    margin-bottom: 1.5rem;
}

.top-item .label {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.top-item h3 {
    font-size: 1.25rem;
    margin: 0.5rem 0;
}

.scrobbles {
    font-size: 0.875rem;
    color: #666;
}

.other-items .item {
    display: grid;
    grid-template-columns: 30px 1fr auto;
    gap: 0.5rem;
    padding: 0.5rem 0;
    align-items: center;
}

.other-items .rank {
    color: #666;
    font-size: 0.875rem;
}

.other-items .count {
    color: #666;
    font-size: 0.875rem;
}

/* Mood Board */
.mood-board {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.mood-stats {
    display: grid;
    gap: 1.5rem;
}

.mood-primary {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.mood-value {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

.mood-levels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.mood-level {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.mood-distribution {
    margin-top: 1rem;
}

.mood-bar {
    display: grid;
    grid-template-columns: 100px 1fr 50px;
    gap: 1rem;
    align-items: center;
    margin-bottom: 0.5rem;
}

.mood-progress {
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
}

.mood-progress-bar {
    height: 100%;
    background: #666;
    border-radius: 4px;
}

.mood-score {
    font-size: 0.875rem;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection