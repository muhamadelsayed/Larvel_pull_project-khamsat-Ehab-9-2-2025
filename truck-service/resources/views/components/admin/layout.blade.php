<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- استلام عنوان الصفحة كمتغير --}}
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
        <nav class="bg-gray-800 text-white shadow">
            <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="font-bold text-xl">لوحة التحكم</a>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4 space-x-reverse">
                                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900' : '' }} text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">الرئيسية</a>
                                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">إدارة المستخدمين</a>
                                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">إدارة التصنيفات</a>
<a href="{{ route('admin.trucks.index') }}" class="{{ request()->routeIs('admin.trucks.*') ? 'bg-gray-900' : '' }} text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">إدارة الشاحنات</a>

</div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">تسجيل الخروج</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        {{-- 2. رأس الصفحة (العنوان الرئيسي) --}}
        <header class="bg-white shadow-sm">
            <div class="container max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold leading-tight text-gray-900">
                    {{ $header }}
                </h1>
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