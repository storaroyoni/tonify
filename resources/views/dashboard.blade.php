@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    @if(auth()->check())
        <h1 class="text-3xl font-bold text-gray-900 mb-8 animate-fade-in">Welcome to Your Dashboard, {{ auth()->user()->name }}!</h1>

        @if(auth()->user()->isLastfmConnected())
            <div class="space-y-8">
                <h2 class="text-2xl font-bold text-purple-600">
                    TOP MUSIC
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Artists Card -->
                    <div class="group relative bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="relative p-4">
                            <div class="flex justify-between items-center">
                                <span>Artists</span>
                                <span class="text-purple-600">{{ count(session('user_top_artists', [])) }}</span>
                            </div>
                            
                            @if(session('user_top_artists'))
                                <div class="mt-4">
                                    <span class="text-purple-600">Top Artist</span>
                                    <h3 class="text-xl font-semibold mt-1">{{ session('user_top_artists')[0]['name'] }}</h3>
                                    <span class="text-sm text-gray-500">{{ session('user_top_artists')[0]['playcount'] }} scrobbles</span>
                                </div>
                                <div class="mt-4 space-y-2">
                                    @foreach(array_slice(session('user_top_artists'), 1, 4) as $index => $artist)
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="text-purple-600">#{{ $index + 2 }}</span>
                                                <span>{{ $artist['name'] }}</span>
                                            </div>
                                            <span class="text-gray-500">{{ $artist['playcount'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Albums Card -->
                    <div class="group relative bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="relative p-4">
                            <div class="flex justify-between items-center">
                                <span>Albums</span>
                                <span class="text-purple-600">{{ count(session('user_top_albums', [])) }}</span>
                            </div>
                            
                            @if(session('user_top_albums'))
                                <div class="mt-4">
                                    <span class="text-purple-600">Top Album</span>
                                    <h3 class="text-xl font-semibold mt-1">{{ session('user_top_albums')[0]['name'] }}</h3>
                                    <span class="text-sm text-gray-500">{{ session('user_top_albums')[0]['playcount'] }} scrobbles</span>
                                </div>
                                <div class="mt-4 space-y-2">
                                    @foreach(array_slice(session('user_top_albums'), 1, 4) as $index => $album)
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="text-purple-600">#{{ $index + 2 }}</span>
                                                <span>{{ $album['name'] }}</span>
                                            </div>
                                            <span class="text-gray-500">{{ $album['playcount'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tracks Card -->
                    <div class="group relative bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="relative p-4">
                            <div class="flex justify-between items-center">
                                <span>Tracks</span>
                                <span class="text-purple-600">{{ count(session('user_top_tracks', [])) }}</span>
                            </div>
                            
                            @if(session('user_top_tracks'))
                                <div class="mt-4">
                                    <span class="text-purple-600">Top Track</span>
                                    <h3 class="text-xl font-semibold mt-1">{{ session('user_top_tracks')[0]['name'] }}</h3>
                                    <span class="text-sm text-gray-500">{{ session('user_top_tracks')[0]['playcount'] }} scrobbles</span>
                                </div>
                                <div class="mt-4 space-y-2">
                                    @foreach(array_slice(session('user_top_tracks'), 1, 4) as $index => $track)
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <span class="text-purple-600">#{{ $index + 2 }}</span>
                                                <span>{{ $track['name'] }}</span>
                                            </div>
                                            <span class="text-gray-500">{{ $track['playcount'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Mood Board -->
                @if(isset($moodAnalysis))
                    <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg p-6 mt-8">
                        <h2 class="text-2xl font-bold text-purple-600 mb-6">
                            Mood Analysis
                        </h2>
                        <div class="grid gap-6">
                            <div class="bg-purple-50 rounded-lg p-4 text-center">
                                <h3 class="text-lg font-medium text-gray-700 mb-2">Current Mood</h3>
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ $moodAnalysis['primary_mood'] }}
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-purple-50 rounded-lg p-4 text-center">
                                    <h3 class="text-lg font-medium text-gray-700 mb-2">Energy Level</h3>
                                    <p class="text-2xl font-bold text-purple-600">{{ $moodAnalysis['energy_level'] }}%</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-4 text-center">
                                    <h3 class="text-lg font-medium text-gray-700 mb-2">Happiness Level</h3>
                                    <p class="text-2xl font-bold text-purple-600">{{ $moodAnalysis['happiness_level'] }}%</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h3 class="text-xl font-bold text-gray-900">Mood Distribution</h3>
                                @foreach($moodAnalysis['mood_scores'] as $mood => $score)
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm font-medium">
                                            <span class="text-gray-700">{{ ucfirst($mood) }}</span>
                                            <span class="text-purple-600">{{ round($score) }}%</span>
                                        </div>
                                        <div class="h-2 bg-purple-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-purple-600 rounded-full transition-all duration-500"
                                                 style="width: {{ $score }}%"></div>
                                        </div>
                                        @if(!empty($moodAnalysis['mood_tracks'][$mood]))
                                            <div class="text-sm text-gray-600 pl-4 border-l-2 border-purple-200">
                                                Contributing tracks:
                                                @foreach($moodAnalysis['mood_tracks'][$mood] as $track)
                                                    {{ $track['artist'] }} - {{ $track['name'] }}@if(!$loop->last), @endif
                                                @endforeach
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
            <div class="text-center py-12">
                <p class="text-lg text-gray-600 mb-4">Connect your Last.fm account to see your music stats!</p>
                <a href="{{ route('lastfm.auth') }}" 
                   class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                    Connect Last.fm Account
                </a>
            </div>
        @endif
    @else
        <p class="text-center text-lg text-gray-600">Please log in to view your dashboard.</p>
    @endif
</div>
<div class="fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-purple-500/10 to-purple-500/10 animate-slow-spin"></div>
    <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-gradient-to-tl from-purple-500/10 to-purple-500/10 animate-slow-spin-reverse"></div>
</div>
@endsection