<nav x-data="{ open: false }" class="nav-fixed">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="font-bold text-xl text-indigo-600">
                        Tonify
                    </a>
                </div>

                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="searchInput"
                            placeholder="Search users..." 
                            class="w-64 rounded-full border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                        <div id="searchResults" class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg hidden">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                        Dashboard
                    </a>

                    <div class="relative" x-data="{ notificationsOpen: false }">
                        <button @click="notificationsOpen = !notificationsOpen" class="relative p-1 text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            
                            @php
                                $pendingRequests = Auth::user()->receivedFriendRequests()->where('status', 'pending')->count();
                            @endphp
                            
                            @if($pendingRequests > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full" style="min-width: 18px; min-height: 18px;">
                                    {{ $pendingRequests }}
                                </span>
                            @endif
                        </button>

                        <div x-show="notificationsOpen" 
                             @click.away="notificationsOpen = false"
                             class="absolute right-0 w-80 mt-2 bg-white rounded-md shadow-lg overflow-hidden z-50">
                            <div class="py-2">
                                @php
                                    $requests = Auth::user()->receivedFriendRequests()
                                        ->where('status', 'pending')
                                        ->with('sender')
                                        ->get();
                                @endphp

                                @if($requests->count() > 0)
                                    @foreach($requests as $request)
                                        <div class="px-4 py-3 hover:bg-gray-50 flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    @if($request->sender->profile_picture)
                                                        <img class="h-8 w-8 rounded-full object-cover" 
                                                             src="{{ Storage::url($request->sender->profile_picture) }}" 
                                                             alt="">
                                                    @else
                                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-500">
                                                                {{ substr($request->sender->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $request->sender->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        Sent you a friend request
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <form action="{{ route('friend.accept', $request) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-gray-900 bg-green-400 rounded-md hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shadow-sm">
                                                        Accept
                                                    </button>
                                                </form>
                                                <form action="{{ route('friend.reject', $request) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-gray-900 bg-red-400 rounded-md hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-sm">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="px-4 py-3 text-sm text-gray-500">
                                        No pending friend requests
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="ml-3 relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center">
                            @if(Auth::user()->profile_picture)
                                <img class="h-6 w-6 rounded-full object-cover" 
                                     src="{{ Storage::url(Auth::user()->profile_picture) }}" 
                                     alt="Profile Photo" />
                            @else
                                <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs text-gray-500">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="py-1" role="none">
                                <a href="/profile/{{ Auth::user()->name }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Your Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Login</a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-700 hover:text-gray-900">Register</a>
                @endauth
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        let debounceTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            
            if (this.value.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }

            debounceTimeout = setTimeout(() => {
                fetch(`/search?query=${encodeURIComponent(this.value)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.users.length > 0) {
                            let html = '<div class="py-2">';
                            data.users.forEach(user => {
                                html += `
                                    <a href="${user.url}" class="flex items-center px-4 py-2 hover:bg-gray-100">
                                        ${user.profile_picture 
                                            ? `<img src="${user.profile_picture}" class="h-6 w-6 rounded-full object-cover mr-3">` 
                                            : `<div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <span class="text-xs text-gray-500">${user.name.charAt(0)}</span>
                                               </div>`
                                        }
                                        <span class="text-sm text-gray-700">${user.name}</span>
                                    </a>
                                `;
                            });
                            html += '</div>';
                            searchResults.innerHTML = html;
                            searchResults.classList.remove('hidden');
                        } else {
                            searchResults.innerHTML = '<div class="px-4 py-2 text-sm text-gray-700">No users found</div>';
                            searchResults.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }, 300);
        });
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.classList.add('hidden');
            }
        });
    </script>
</nav>