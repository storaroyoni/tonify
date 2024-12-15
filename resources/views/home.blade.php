<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tonify - Global Music Trends</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-12">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-lg font-bold text-gray-800">Tonify</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Dashboard</a>
                        <a href="{{ route('profile.show', Auth::user()->name) }}" class="flex items-center">
                            <!-- profile pic -->
                            <div class="w-6 h-6 rounded-full overflow-hidden">
                                @if(Auth::user()->profile_picture)
                                    <img src="{{ Storage::url(Auth::user()->profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-500 text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-gray-900">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-extrabold tracking-tight text-center">
                Discover What's Trending in Music
            </h2>
            <p class="mt-4 text-xl text-center">
                Real-time global music trends, charts, and discoveries
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-12">
            <h3 class="text-2xl font-bold mb-6">Weekly Top Tracks</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($globalCharts['topTracks'] ?? [] as $track)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4">
                        <div class="flex items-center">
                            @if($track['image'])
                                <img src="{{ $track['image'] }}" alt="{{ $track['name'] }}" class="w-20 h-20 rounded-md">
                            @endif
                            <div class="ml-4">
                                <h4 class="font-semibold">{{ $track['name'] }}</h4>
                                <p class="text-gray-600">{{ $track['artist'] }}</p>
                                <p class="text-sm text-gray-500">{{ number_format($track['playcount']) }} plays</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-12">
            <h3 class="text-2xl font-bold mb-6">Trending Artists</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($globalCharts['trendingArtists'] ?? [] as $artist)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4 text-center">
                        @if($artist['image'])
                            <img src="{{ $artist['image'] }}" alt="{{ $artist['name'] }}" class="w-32 h-32 rounded-full mx-auto mb-4">
                        @endif
                        <h4 class="font-semibold">{{ $artist['name'] }}</h4>
                        <p class="text-sm text-gray-500">{{ number_format($artist['listeners']) }} listeners</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Genre Trends -->
        <div>
            <h3 class="text-2xl font-bold mb-6">Genre Trends</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($globalCharts['genres'] ?? [] as $genre)
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4 text-center">
                        <h4 class="font-semibold">{{ $genre['name'] }}</h4>
                        <p class="text-sm text-gray-500">{{ number_format($genre['count']) }} tracks</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>