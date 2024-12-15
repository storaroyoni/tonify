@extends('layouts.app')

@section('content')
<div class="container">
    <div class="profile-header">
        <div class="profile-info">
            @if($user->profile_picture)
                <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" class="profile-picture">
            @else
                <div class="default-profile-picture">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div class="user-details">
                <h1>{{ $user->name }}</h1>
                @if($user->lastfm_username)
                    <a href="https://www.last.fm/user/{{ $user->lastfm_username }}" target="_blank" class="lastfm-link">
                        Last.fm Profile
                    </a>
                @endif
                <p class="bio">{{ $user->bio ?? 'No bio yet.' }}</p>
            </div>
        </div>
        @if(auth()->id() === $user->id)
            <a href="{{ route('profile.edit') }}" class="edit-profile-btn">Edit Profile</a>
        @endif
    </div>

    <div class="music-stats">
        <div class="stats-grid">
            <!-- Top Artists -->
            <div class="stat-card">
                <h3>Top Artists</h3>
                @foreach(session('user_top_artists', []) as $artist)
                    <div class="stat-item">
                        <span class="name">{{ $artist['name'] }}</span>
                        <span class="count">{{ $artist['playcount'] }} plays</span>
                    </div>
                @endforeach
            </div>

            <!-- Top Albums -->
            <div class="stat-card">
                <h3>Top Albums</h3>
                @foreach(session('user_top_albums', []) as $album)
                    <div class="stat-item">
                        <span class="name">{{ $album['name'] }}</span>
                        <span class="artist">by {{ $album['artist'] }}</span>
                        <span class="count">{{ $album['playcount'] }} plays</span>
                    </div>
                @endforeach
            </div>

            <!-- Top Tracks -->
            <div class="stat-card">
                <h3>Top Tracks</h3>
                @foreach(session('user_top_tracks', []) as $track)
                    <div class="stat-item">
                        <span class="name">{{ $track['name'] }}</span>
                        <span class="artist">by {{ $track['artist'] }}</span>
                        <span class="count">{{ $track['playcount'] }} plays</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-info {
    display: flex;
    gap: 20px;
    align-items: center;
}

.profile-picture, .default-profile-picture {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
}

.default-profile-picture {
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: #666;
}

.user-details h1 {
    margin: 0 0 10px 0;
    color: #333;
}

.lastfm-link {
    display: inline-block;
    color: #d51007;
    text-decoration: none;
    margin-bottom: 10px;
}

.bio {
    color: #666;
    margin: 0;
}

.edit-profile-btn {
    background: #d51007;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    transition: background-color 0.2s;
}

.edit-profile-btn:hover {
    background: #b30d06;
}

.music-stats {
    margin-top: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card h3 {
    margin: 0 0 15px 0;
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.stat-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
}

.name {
    display: block;
    font-weight: 600;
    color: #333;
}

.artist {
    display: block;
    color: #666;
    font-size: 0.9em;
}

.count {
    display: block;
    color: #999;
    font-size: 0.8em;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        gap: 20px;
    }

    .edit-profile-btn {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection