@extends('layouts.app')

@section('content')
<div class="fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-purple-500/5 to-purple-500/5 animate-slow-spin"></div>
    <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-gradient-to-tl from-purple-500/5 to-purple-500/5 animate-slow-spin-reverse"></div>
</div>

<div class="min-h-screen bg-gradient-to-br from-purple-50/80 to-white/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Welcome to Your Dashboard, {{ auth()->user()->name }}!</h1>

        <div class="mb-12">
            <h2 class="text-xl font-semibold text-purple-600 mb-6">TOP MUSIC</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Top Artist -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-medium text-purple-600 mb-4">Top Artist (All Time)</h3>
                    @if(!empty($topArtists))
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-full">
                                <h4 class="text-xl font-bold text-gray-900 line-clamp-2">{{ $topArtists[0]['name'] }}</h4>
                                <p class="text-gray-500">{{ number_format($topArtists[0]['playcount']) }} scrobbles</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @foreach(array_slice($topArtists, 1, 4) as $index => $artist)
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <span class="text-sm font-medium text-purple-400 w-8 flex-shrink-0">#{{ $index + 2 }}</span>
                                        <span class="text-gray-900 truncate hover:text-clip" title="{{ $artist['name'] }}">{{ $artist['name'] }}</span>
                                    </div>
                                    <span class="text-gray-500 text-sm ml-2 flex-shrink-0 mt-1">{{ number_format($artist['playcount']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Top Album -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-medium text-purple-600 mb-4">Top Album (All Time)</h3>
                    @if(!empty($topAlbums))
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-full">
                                <h4 class="text-xl font-bold text-gray-900 line-clamp-2">{{ $topAlbums[0]['name'] }}</h4>
                                <p class="text-gray-500">{{ number_format($topAlbums[0]['playcount']) }} scrobbles</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @foreach(array_slice($topAlbums, 1, 4) as $index => $album)
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <span class="text-sm font-medium text-purple-400 w-8 flex-shrink-0">#{{ $index + 2 }}</span>
                                        <span class="text-gray-900 truncate hover:text-clip" title="{{ $album['name'] }}">{{ $album['name'] }}</span>
                                    </div>
                                    <span class="text-gray-500 text-sm ml-2 flex-shrink-0 mt-1">{{ number_format($album['playcount']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Top Track -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="text-sm font-medium text-purple-600 mb-4">Top Track (All Time)</h3>
                    @if(!empty($topTracks))
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-full">
                                <h4 class="text-xl font-bold text-gray-900 line-clamp-2">{{ $topTracks[0]['name'] }}</h4>
                                <p class="text-gray-500">{{ number_format($topTracks[0]['playcount']) }} scrobbles</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            @foreach(array_slice($topTracks, 1, 4) as $index => $track)
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <span class="text-sm font-medium text-purple-400 w-8 flex-shrink-0">#{{ $index + 2 }}</span>
                                        <span class="text-gray-900 truncate hover:text-clip" title="{{ $track['name'] }}">{{ $track['name'] }}</span>
                                    </div>
                                    <span class="text-gray-500 text-sm ml-2 flex-shrink-0 mt-1">{{ number_format($track['playcount']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mood Analysis -->
        <div class="mb-12">
            <h2 class="text-xl font-semibold text-purple-600 mb-6">Mood Analysis</h2>
            @if(isset($moodAnalysis))
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-sm p-8 text-white">
                    <div class="text-center mb-8">
                        <h3 class="text-lg font-medium mb-2">Current Mood</h3>
                        <div class="text-4xl font-bold">{{ $moodAnalysis['primary_mood'] }}</div>
                    </div>

                    <div class="flex justify-center mb-8">
                        <div class="grid grid-cols-2 gap-16">
                            <div class="text-center">
                                <h4 class="text-sm font-medium">Energy Level</h4>
                                <p class="text-3xl font-bold">{{ $moodAnalysis['energy_level'] }}%</p>
                            </div>
                            <div class="text-center">
                                <h4 class="text-sm font-medium">Happiness Level</h4>
                                <p class="text-3xl font-bold">{{ $moodAnalysis['happiness_level'] }}%</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-medium mb-6">Mood Distribution</h4>
                        <div class="space-y-6">
                            @foreach($moodAnalysis['mood_scores'] as $mood => $score)
                                <div>
                                    <div class="flex justify-between text-sm mb-2">
                                        <span>{{ ucfirst($mood) }}</span>
                                        <span>{{ round($score) }}%</span>
                                    </div>
                                    <div class="h-2 bg-white/20 rounded-full overflow-hidden w-full">
                                        <div class="h-full bg-white rounded-full transition-none" style="width: {{ $score }}%"></div>
                                    </div>
                                    @if(!empty($moodAnalysis['mood_tracks'][$mood]))
                                        <div class="mt-2 text-sm text-white/80">
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
            @else
                <div class="bg-white rounded-2xl shadow-sm p-8 text-center">
                    <p class="text-gray-600 mb-4">Connect your Last.fm account to see your music stats!</p>
                    <a href="{{ route('lastfm.auth') }}" 
                       class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        Connect Last.fm Account
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 