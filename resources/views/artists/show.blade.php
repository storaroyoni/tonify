<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $artist['name'] }} - Tonify</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-12">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="text-lg font-bold text-gray-800">Tonify</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Artist Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-start">
                @if(isset($artist['mainImage']))
                    <img src="{{ $artist['mainImage'] }}" 
                         alt="{{ $artist['name'] }}" 
                         class="w-48 h-48 rounded-lg object-cover">
                @endif
                <div class="ml-6">
                    <h1 class="text-3xl font-bold mb-2">{{ $artist['name'] }}</h1>
                    <p class="text-gray-600 mb-4">{{ number_format($artist['stats']['listeners']) }} listeners</p>
                    
                    <div x-data="{ expanded: false }">
                        <div class="prose max-w-none">
                            <p class="text-gray-600" x-show="!expanded">
                                {{ Str::limit($artist['bio']['summary'] ?? '', 200) }}
                            </p>
                            <p class="text-gray-600" x-show="expanded">
                                {{ $artist['bio']['summary'] ?? '' }}
                            </p>
                        </div>
                        
                        @if(isset($artist['bio']['summary']) && strlen($artist['bio']['summary']) > 200)
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

        <!-- Similar Artists -->
        <h2 class="text-2xl font-bold mb-6">Similar Artists</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
            @foreach($similarArtists as $similar)
                <a href="{{ route('artist.show', ['name' => $similar['name']]) }}" 
                   class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4">
                    <div class="flex items-center">
                        @if($similar['image'])
                            <img src="{{ $similar['image'] }}" 
                                 alt="{{ $similar['name'] }}" 
                                 class="w-16 h-16 rounded-full object-cover">
                        @endif
                        <div class="ml-4">
                            <h3 class="font-semibold">{{ $similar['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ number_format($similar['listeners']) }} listeners</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</body>
</html> 