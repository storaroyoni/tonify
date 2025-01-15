@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    @if($artist)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-start space-x-6">
                <div class="flex-shrink-0">
                    @if(isset($artist['mainImage']))
                        <img class="w-24 h-24 rounded-md object-cover" 
                             src="{{ $artist['mainImage'] }}" 
                             alt="{{ $artist['name'] }}">
                    @else
                        <div class="w-24 h-24 rounded-md bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">No Image</span>
                        </div>
                    @endif
                </div>

                <!-- Artist Info -->
                <div class="flex-1">
                    <h1 class="text-2xl font-bold mb-2">{{ $artist['name'] }}</h1>
                    <p class="text-gray-600 mb-2">{{ number_format($artist['stats']['listeners']) }} listeners</p>

                    <div x-data="{ expanded: false }">
                        <div class="prose max-w-none mb-2">
                            <div class="inline">
                                <span class="text-gray-600" x-show="!expanded">
                                    {{ Str::limit(preg_replace('/<a\s.*?>.*?<\/a>/i', '', $artist['bio']['summary'] ?? ''), 200) }}
                                </span>
                                <span class="text-gray-600" x-show="expanded">
                                    {{ preg_replace('/<a\s.*?>.*?<\/a>/i', '', $artist['bio']['summary'] ?? '') }}
                                </span>
                                
                                @if(isset($artist['bio']['summary']) && strlen($artist['bio']['summary']) > 200)
                                    <button 
                                        @click="expanded = !expanded"
                                        class="text-purple-600 hover:text-purple-800 font-medium ml-1"
                                        x-text="expanded ? 'Read Less' : 'Read More'">
                                    </button>
                                @endif
                            </div>
                        </div>
                        <a href="{{ $spotifyUrl }}" 
                           target="_blank"
                           class="spotify-button mt-2">
                            Listen on Spotify
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Artists Section -->
        @if(count($similarArtists) > 0)
            <div class="mt-8">
                <h3 class="text-xl font-bold mb-4">Similar Artists</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($similarArtists as $similar)
                        <a href="{{ route('artist.show', ['name' => $similar['name']]) }}" 
                           class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4">
                            <div class="flex items-center space-x-4">
                                @if($similar['image'])
                                    <img src="{{ $similar['image'] }}" 
                                         alt="{{ $similar['name'] }}" 
                                         class="w-16 h-16 rounded-md object-cover">
                                @else
                                    <div class="w-16 h-16 rounded-md bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400 text-xs">No Image</span>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $similar['name'] }}</h4>
                                    <p class="text-sm text-gray-600">{{ number_format($similar['listeners']) }} listeners</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <p class="text-sm text-yellow-700">Artist not found.</p>
        </div>
    @endif
</div>
@endsection 