<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
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
