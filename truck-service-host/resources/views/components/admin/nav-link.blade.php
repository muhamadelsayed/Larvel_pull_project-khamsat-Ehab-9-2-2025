@props(['route', 'icon', 'title'])

<a href="{{ route($route) }}" 
   class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs($route) ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path>
    </svg>
    <span x-show="sidebarOpen" class="mr-3 whitespace-nowrap">{{ $title }}</span>
</a>