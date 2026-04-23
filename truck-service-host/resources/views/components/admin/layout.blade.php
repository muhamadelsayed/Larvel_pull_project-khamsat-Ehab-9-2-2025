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
    <style> body { font-family: 'Cairo', sans-serif; } 
        aside{
            min-height: 100%;
        }
    </style>
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: true }">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
       <aside 
    :class="sidebarOpen ? 'translate-x-0 w-64' : 'translate-x-full lg:translate-x-0 lg:w-20'" 
    class="fixed inset-y-0 right-0 z-50 bg-gray-800 text-white transition-all duration-300 transform lg:static lg:inset-0 flex flex-col shadow-2xl">
            <!-- Logo Area -->
            <div class="h-16 flex items-center justify-between px-4 bg-gray-900">
                <span x-show="sidebarOpen" class="font-bold text-xl overflow-hidden whitespace-nowrap">Bull Station</span>
                <button @click="sidebarOpen = !sidebarOpen" class="p-1 hover:bg-gray-700 rounded">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                <x-admin.nav-link route="admin.dashboard" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" title="الرئيسية" />
                <x-admin.nav-link route="admin.users.index" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" title="المستخدمين" />
                <x-admin.nav-link route="admin.categories.index" icon="M4 6h16M4 10h16M4 14h16M4 18h16" title="التصنيفات" />
                <x-admin.nav-link route="admin.trucks.index" icon="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" title="الشاحنات" />
                <x-admin.nav-link route="admin.bookings.index" icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" title="الحجوزات" />
                <x-admin.nav-link route="admin.settings.index" icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" title="الإعدادات" />
                <x-admin.nav-link route="admin.settings.landing" icon="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" title="إعدادات الصفحة الرئيسية" />
            </nav>

            <!-- Logout -->
            <div class="p-4 bg-gray-900">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center text-red-400 hover:text-red-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span x-show="sidebarOpen" class="mr-3 transition-opacity">تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white shadow-sm h-16 flex items-center px-4 justify-between">
                <!-- زر الهامبرغر يظهر فقط على الموبايل لفتح السايد بار -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                <div class="text-lg font-bold truncate">{!! $header !!}</div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
    // الدوال المسؤولة عن فتح وإغلاق النماذج المنبثقة
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    // إغلاق المودال عند النقر خارج مساحته (أكثر تعقيداً بقليل في Vanilla JS)
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-modal-backdrop').forEach(backdrop => {
            backdrop.addEventListener('click', (e) => {
                if (e.target.classList.contains('js-modal-backdrop')) {
                    backdrop.style.display = 'none';
                }
            });
        });
    });
</script>
</body>
</html>