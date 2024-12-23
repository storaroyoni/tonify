<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $song['name'] ?? 'Song' }} - Tonify</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-12">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-lg font-bold text-gray-800">Tonify</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        @if($song)
            <!-- Song Details Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="flex items-start space-x-6">
                    <div class="flex-shrink-0">
                        @if(isset($song['album']['image']))
                            <img class="w-24 h-24 rounded-md object-cover" 
                                 src="{{ end($song['album']['image'])['#text'] }}" 
                                 alt="{{ $song['name'] }}">
                        @else
                            <div class="w-24 h-24 rounded-md bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-400">No Image</span>
                            </div>
                        @endif
                    </div>

                    <!-- Song Info -->
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold mb-2">{{ $song['name'] }}</h1>
                        <p class="text-gray-600 mb-2">by {{ $song['artist']['name'] }}</p>
                        
                        @if(isset($song['album']))
                            <p class="text-gray-500 text-sm mb-4">
                                Album: {{ $song['album']['title'] }}
                            </p>
                        @endif

                        <div x-data="{ expanded: false }">
                            <div class="prose max-w-none">
                                <p class="text-gray-600" x-show="!expanded">
                                    {{ Str::limit(preg_replace('/<a\s.*?>.*?<\/a>/i', '', $song['wiki']['summary'] ?? ''), 200) }}
                                </p>
                                <p class="text-gray-600" x-show="expanded">
                                    {{ preg_replace('/<a\s.*?>.*?<\/a>/i', '', $song['wiki']['summary'] ?? '') }}
                                </p>
                            </div>
                            
                            @if(isset($song['wiki']['summary']) && strlen($song['wiki']['summary']) > 200)
                                <button 
                                    @click="expanded = !expanded"
                                    class="mt-2 text-blue-600 hover:text-blue-800 font-medium"
                                    x-text="expanded ? 'Read Less' : 'Read More'">
                                </button>
                            @endif
                        </div>

                        <a href="{{ $spotifyUrl }}" 
                           target="_blank"
                           class="inline-block mt-4 text-green-600 hover:text-green-800 font-medium">
                            Listen on Spotify
                        </a>
                    </div>
                </div>
            </div>

            @if(count($similarTracks) > 0)
                <div class="mt-8">
                    <h3 class="text-xl font-bold mb-4">Similar Tracks</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($similarTracks as $track)
                            <a href="{{ route('song.show', ['name' => $track['name'], 'artist' => $track['artist']['name']]) }}" 
                               class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4">
                                <div class="flex items-center space-x-4">
                                    @if(isset($track['image']))
                                        <img src="{{ end($track['image'])['#text'] }}" 
                                             alt="{{ $track['name'] }}" 
                                             class="w-16 h-16 rounded-md object-cover">
                                    @else
                                        <div class="w-16 h-16 rounded-md bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">No Image</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $track['name'] }}</h4>
                                        <p class="text-sm text-gray-600">{{ $track['artist']['name'] }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-sm text-yellow-700">Song not found.</p>
            </div>
        @endif
    </div>
</body>
</html> 