<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'لوحة التحكم' }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        {{-- 1. الهيدر (شريط التنقل العلوي) --}}
        {{-- استخدام Alpine.js للتحكم في القائمة المنسدلة --}}
        <nav x-data="{ open: false }" class="bg-gray-800 text-white shadow">
            <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    
                    {{-- شعار وروابط سطح المكتب --}}
                    <div class="flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="font-bold text-xl flex-shrink-0">
                            لوحة التحكم
                        </a>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4 space-x-reverse">
                                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900' : '' }} text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">الرئيسية</a>
                                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">إدارة المستخدمين</a>
                                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">إدارة التصنيفات</a>
                                <a href="{{ route('admin.trucks.index') }}" class="{{ request()->routeIs('admin.trucks.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">إدارة الشاحنات</a>
                                <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">إدارة الحجوزات</a>
                                <a href="/api/documentation" target="_blank" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">توثيق الـ API</a>
                            </div>
                        </div>
                    </div>

                    {{-- زر تسجيل الخروج (سطح المكتب) --}}
                    <div class="hidden md:block">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">تسجيل الخروج</button>
                        </form>
                    </div>

                    {{-- زر القائمة للموبايل (Hamburger Menu) --}}
                    <div class="-mr-2 flex md:hidden">
                        <button @click="open = !open" type="button" class="bg-gray-800 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            {{-- أيقونة القائمة (تظهر عندما تكون مغلقة) --}}
                            <svg x-show="!open" class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            {{-- أيقونة الإغلاق (تظهر عندما تكون مفتوحة) --}}
                            <svg x-show="open" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- القائمة المنسدلة للموبايل --}}
            <div x-show="open" x-transition class="md:hidden" id="mobile-menu" style="display: none;">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900' : '' }} text-white block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700">الرئيسية</a>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">إدارة المستخدمين</a>
                    <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">إدارة التصنيفات</a>
                    <a href="{{ route('admin.trucks.index') }}" class="{{ request()->routeIs('admin.trucks.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">إدارة الشاحنات</a>
                    <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">إدارة الحجوزات</a>
                    <a href="/api/documentation" target="_blank" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">توثيق الـ API</a>
                </div>
                {{-- زر تسجيل الخروج (الموبايل) --}}
                <div class="pt-4 pb-3 border-t border-gray-700">
                     <form action="{{ route('admin.logout') }}" method="POST" class="px-2">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">تسجيل الخروج</button>
                    </form>
                </div>
            </div>
        </nav>

        {{-- 2. رأس الصفحة (العنوان الرئيسي) --}}
        <header class="bg-white shadow-sm">
            <div class="container max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-xl md:text-3xl font-bold leading-tight text-gray-900">
                    {!! $header !!}
                </div>
            </div>
        </header>

        {{-- 3. المحتوى الرئيسي للصفحة --}}
        <main>
            <div class="container max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>