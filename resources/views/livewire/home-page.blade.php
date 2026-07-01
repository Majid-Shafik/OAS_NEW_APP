<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Logo -->
                <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xl font-bold shadow-lg">
                    EP
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Enterprise Portal</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Admin Link -->
                <a href="/admin" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
                    Admin Panel
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
        <!-- Search Section -->
        <div class="mb-12 max-w-2xl mx-auto">
            <div class="relative">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input 
                    wire:model.live.debounce.300ms="search"
                    type="search" 
                    class="block w-full p-4 pr-12 text-lg text-gray-900 border border-gray-200 rounded-2xl bg-white shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-400 transition-all duration-200" 
                    placeholder="Search for a system by name or description..." 
                >
            </div>
        </div>

        <!-- Systems Grid -->
        @if($systems->isEmpty())
            <div class="text-center py-20">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">No systems found</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">We couldn't find any systems matching your search.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($systems as $system)
                    <div class="group relative bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                        
                        <!-- Thumbnail -->
                        <div class="aspect-w-16 aspect-h-9 w-full bg-gray-200 dark:bg-gray-700 relative overflow-hidden">
                            @if($system->thumbnail)
                                <img src="{{ asset('storage/' . $system->thumbnail) }}" alt="{{ $system->name }}" class="object-cover w-full h-48 group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-48 flex items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-gray-700 dark:to-gray-600">
                                    <span class="text-4xl">🏢</span>
                                </div>
                            @endif
                            
                            <!-- Icon Badge -->
                            <div class="absolute -bottom-6 right-6 w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center text-xl" style="color: {{ $system->color ?? '#4f46e5' }}">
                                @if($system->icon)
                                    <!-- Dynamic Heroicon rendering -->
                                    <x-dynamic-component :component="$system->icon" class="w-6 h-6" />
                                @else
                                    ✨
                                @endif
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $system->name }}
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 line-clamp-3 mb-6 flex-1 text-sm">
                                {{ $system->description }}
                            </p>
                            
                            <!-- Action Button -->
                            <a href="{{ $system->url ?? '#' }}" 
                               {{ $system->open_in_new_tab ? 'target="_blank" rel="noopener noreferrer"' : '' }}
                               class="inline-flex items-center justify-center w-full px-4 py-3 bg-gray-50 hover:bg-indigo-50 text-gray-900 hover:text-indigo-700 font-medium rounded-xl transition-colors duration-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-indigo-900 dark:hover:text-indigo-200 group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500">
                                Open System
                                <svg class="w-4 h-4 mr-2 mr-reverse transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 py-8 mt-auto transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400 text-sm">
            &copy; {{ date('Y') }} Enterprise Portal. All rights reserved.
        </div>
    </footer>
</div>
