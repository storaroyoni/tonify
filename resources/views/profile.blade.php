@extends('layouts.app')

@section('content')
<div class="container">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
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
        <div class="profile-actions">
            @auth
                @if(auth()->id() === $user->id)
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Edit Profile
                    </a>
                @else
                    @if(auth()->user()->isFriendsWith($user) || auth()->id() === $user->id)
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="inline-flex items-center px-6 py-3 bg-purple-600 rounded-full font-semibold text-sm text-white uppercase tracking-widest hover:bg-purple-700 transition duration-150 ease-in-out w-[160px] justify-center">
                                Friends
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-[160px]">
                                <form action="{{ route('friend.remove', $user) }}" method="POST" class="block w-full">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-6 py-3 text-sm font-semibold text-purple-600 uppercase transition duration-150 ease-in-out bg-white rounded-full shadow-lg hover:bg-purple-50 border-2 border-purple-500">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif(auth()->user()->hasFriendRequestPending($user))
                        <span class="inline-flex items-center px-6 py-3 bg-gray-100 rounded-full font-semibold text-sm text-gray-700 uppercase tracking-widest w-[160px] justify-center">
                            Request Sent
                        </span>
                    @elseif(auth()->user()->hasFriendRequestReceived($user))
                        <div class="flex space-x-2">
                            @php
                                $friendRequest = $user->sentFriendRequests()
                                    ->where('receiver_id', auth()->id())
                                    ->where('status', 'pending')
                                    ->first();
                            @endphp
                            
                            @if($friendRequest)
                                <form action="{{ route('friend.accept', ['request' => $friendRequest->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-400 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-green-500">
                                        Accept
                                    </button>
                                </form>
                                <form action="{{ route('friend.reject', ['request' => $friendRequest->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-400 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-red-500">
                                        Reject
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <form action="{{ route('friend.request', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Add Friend
                            </button>
                        </form>
                    @endif
                @endif
            @endauth
        </div>
    </div>

    @if(isset($stats['now_playing']))
    <div class="now-playing-section">
        <div class="now-playing">
            <div class="pulse-animation"></div>
            <div class="track-info">
                <h3>Now Playing</h3>
                <div class="track">
                    @if(isset($stats['now_playing']['image']))
                        <img src="{{ $stats['now_playing']['image'] }}" alt="Album Art">
                    @endif
                    <div>
                        <span class="name">{{ $stats['now_playing']['name'] }}</span>
                        <span class="artist">by {{ $stats['now_playing']['artist'] }}</span>
                        @if(isset($stats['now_playing']['album']))
                            <span class="artist">on {{ $stats['now_playing']['album'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

    <div class="stats-overview">
        <div class="total-scrobbles">
            <h3>Total Scrobbles</h3>
            <div class="count">{{ number_format($stats['total_scrobbles'] ?? 0) }}</div>
        </div>
    </div>

    <div class="recent-tracks">
        <h3>Recent Tracks</h3>
        <div class="tracks-grid">
            @foreach(array_slice($stats['recent_tracks'] ?? [], 0, 4) as $track)
                <div class="track-card">
                    @if(isset($track['image']))
                        <img src="{{ $track['image'] }}" alt="Album Art">
                    @endif
                    <div class="track-info">
                        <span class="name">{{ $track['name'] }}</span>
                        <span class="artist">{{ $track['artist'] }}</span>
                        <span class="played-at">{{ $track['played_at'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    <div class="music-stats">
        <div class="stats-grid">
            <!-- Top Artists -->
            <div class="stat-card">
                <h3>This Week's Top Artists</h3>
                @foreach($stats['top_artists'] ?? [] as $artist)
                    <div class="stat-item">
                        <span class="name">{{ $artist['name'] }}</span>
                        <span class="count">{{ $artist['playcount'] }} plays</span>
                    </div>
                @endforeach
            </div>

            <!-- Top Albums -->
            <div class="stat-card">
                <h3>This Week's Top Albums</h3>
                @foreach($stats['top_albums'] ?? [] as $album)
                    <div class="stat-item">
                        <span class="name">{{ $album['name'] }}</span>
                        <span class="artist">by {{ $album['artist'] }}</span>
                        <span class="count">{{ $album['playcount'] }} plays</span>
                    </div>
                @endforeach
            </div>

            <!-- Top Tracks -->
            <div class="stat-card">
                <h3>This Week's Top Tracks</h3>
                @foreach($stats['top_tracks'] ?? [] as $track)
                    <div class="stat-item">
                        <span class="name">{{ $track['name'] }}</span>
                        <span class="artist">by {{ $track['artist'] }}</span>
                        <span class="count">{{ $track['playcount'] }} plays</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if(isset($stats['listening_stats']))
        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">Listening Activity</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Weekly Overview -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="font-semibold text-gray-700 mb-2">This Week</h3>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">
                            Total Tracks: <span class="font-medium text-gray-900">{{ $stats['listening_stats']['total_week'] }}</span>
                        </p>
                        <p class="text-sm text-gray-600">
                            Daily Average: <span class="font-medium text-gray-900">{{ $stats['listening_stats']['daily_average'] }} tracks</span>
                        </p>
                    </div>
                </div>

                <!-- Most Active Hours -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="font-semibold text-gray-700 mb-2">Most Active Hours</h3>
                    <div class="space-y-2">
                        @foreach($stats['listening_stats']['most_active_hours'] as $hour)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ $hour['hour'] }}</span>
                                <span class="text-sm font-medium text-gray-900">{{ $hour['count'] }} tracks</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Comments Section -->
    <div class="mt-8 bg-white rounded-lg shadow p-6" 
         x-data="comments()">
        <h2 class="text-2xl font-bold mb-6">Comments</h2>

        @if(auth()->user()->isFriendsWith($user) || auth()->id() === $user->id)
            <form @submit.prevent="submitComment" class="mb-6">
                @csrf
                <div x-data="{ comment: '' }">
                    <textarea 
                        x-model="comment"
                        rows="3" 
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                        placeholder="Write a comment..."
                    ></textarea>
                    <button 
                        type="submit" 
                        x-show="comment.length > 0"
                        x-transition
                        class="mt-2 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition duration-150 ease-in-out font-semibold">
                        Post Comment
                    </button>
                </div>
            </form>
        @endif

        <div class="space-y-6">
            <template x-for="comment in comments" :key="comment.id">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <template x-if="comment.user.profile_picture">
                                <img :src="'/storage/' + comment.user.profile_picture" 
                                     :alt="comment.user.name" 
                                     class="w-8 h-8 rounded-full mr-2">
                            </template>
                            <template x-if="!comment.user.profile_picture">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2"
                                     x-text="comment.user.name.charAt(0).toUpperCase()">
                                </div>
                            </template>
                            <span class="font-medium" x-text="comment.user.name"></span>
                        </div>
                        <template x-if="canDeleteComment(comment)">
                            <button @click="deleteComment(comment.id)" 
                                    class="text-red-500 hover:text-red-600 text-sm">
                                Delete
                            </button>
                        </template>
                    </div>
                    <p class="text-gray-700 mb-2" x-text="comment.content"></p>
                    <span class="text-sm text-gray-500" x-text="formatDate(comment.created_at)"></span>
                </div>
            </template>
        </div>
    </div>
</div>


<script>
function comments() {
    return {
        comments: [],
        async init() {
            await this.fetchComments();
        },
        async fetchComments() {
            const response = await fetch(`/api/profile/{{ $user->id }}/comments`);
            this.comments = await response.json();
        },
        async submitComment(e) {
            const content = e.target.querySelector('textarea').value;
            const response = await fetch(`/profile/{{ $user->id }}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ content })
            });

            if (response.ok) {
                e.target.querySelector('textarea').value = '';
                await this.fetchComments();
            }
        },
        async deleteComment(commentId) {
            if (!confirm('Are you sure you want to delete this comment?')) return;

            const response = await fetch(`/profile/comment/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                await this.fetchComments();
            }
        },
        canDeleteComment(comment) {
            return {{ auth()->id() }} === comment.user_id || {{ auth()->id() }} === comment.profile_user_id;
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-purple-500/5 to-purple-500/5 animate-slow-spin"></div>
        <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-gradient-to-tl from-purple-500/5 to-purple-500/5 animate-slow-spin-reverse"></div>
    </div>
@endsection 

