@extends('layouts.app')

@section('content')
<div class="fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-purple-500/5 to-purple-500/5 animate-slow-spin"></div>
    <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-gradient-to-tl from-purple-500/5 to-purple-500/5 animate-slow-spin-reverse"></div>
</div>

<div class="min-h-screen">
    <div class="bg-gradient-to-r from-purple-600/90 to-purple-700/90 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8 relative z-10">
            <h2 class="text-4xl font-extrabold tracking-tight text-center animate-fade-in">
                Discover What's Trending in Music
            </h2>
            <p class="mt-4 text-xl text-center animate-slide-up">
                Real-time global music trends, charts, and discoveries
            </p>
        </div>
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-white/10 to-transparent animate-slow-spin"></div>
            <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-gradient-to-tl from-white/10 to-transparent animate-slow-spin-reverse"></div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 bg-white shadow-lg rounded-2xl -mt-8 relative z-20">
        <!-- Weekly Top Tracks -->
        <div class="mb-12">
            <h3 class="text-2xl font-bold mb-6 text-purple-900">Weekly Top Tracks</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($globalCharts['topTracks'] ?? [] as $track)
                    <a href="{{ route('song.show', [
                        'name' => rawurlencode($track['name']),
                        'artist' => rawurlencode($track['artist'])
                    ]) }}" 
                       class="group bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 p-4 border border-purple-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-20 h-20 overflow-hidden rounded-lg">
                                @if($track['image'])
                                    <img src="{{ $track['image'] }}" 
                                         alt="{{ $track['name'] }}" 
                                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full bg-purple-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">{{ $track['name'] }}</h4>
                                <p class="text-gray-600">{{ $track['artist'] }}</p>
                                <p class="text-sm text-purple-500">{{ number_format($track['playcount']) }} plays</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Trending Artists -->
        <div class="mb-12">
            <h3 class="text-2xl font-bold mb-6 text-purple-900">Trending Artists</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($globalCharts['trendingArtists'] ?? [] as $artist)
                    <a href="{{ route('artist.show', ['name' => $artist['name']]) }}" 
                       class="group bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 p-4 border border-purple-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-16 h-16 overflow-hidden rounded-full">
                                @if($artist['image'])
                                    <img src="{{ $artist['image'] }}" 
                                         alt="{{ $artist['name'] }}" 
                                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h4 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">{{ $artist['name'] }}</h4>
                                <p class="text-sm text-purple-500">{{ number_format($artist['listeners']) }} listeners</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Genre Trends -->
        <div>
            <h3 class="text-2xl font-bold mb-6 text-purple-900">Genre Trends</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($globalCharts['genres'] ?? [] as $genre)
                    <div class="group bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 p-4 text-center border border-purple-100">
                        <h4 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">{{ $genre['name'] }}</h4>
                        <p class="text-sm text-purple-500">{{ number_format($genre['count']) }} tracks</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection