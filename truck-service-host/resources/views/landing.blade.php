<x-guest.layout>
    <x-slot name="title">{{ $settings['landing_title'] ?? 'بول ستيشن' }}</x-slot>

    <style>
        .glass-card { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .mesh-bg {
            background: radial-gradient(at 0% 0%, #d8e2ff 0%, transparent 50%),
                        radial-gradient(at 100% 0%, #eaddff 0%, transparent 50%),
                        radial-gradient(at 100% 100%, #ffd8ea 0%, transparent 50%),
                        radial-gradient(at 0% 100%, #fcf8fb 0%, transparent 50%);
        }
    </style>

    <div class="selection:bg-indigo-200 overflow-x-hidden mesh-bg min-h-screen font-['Cairo']" dir="rtl">
        
        <!-- Header -->
        <header class="fixed top-0 w-full z-50 bg-white/20 backdrop-blur-md border-b border-white/30">
            <div class="flex items-center justify-between px-8 py-4 max-w-7xl mx-auto">
                <div class="text-2xl font-black tracking-tighter text-indigo-900">BULL STATION</div>
                <a href="{{ route('admin.login') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">دخول الإدارة</a>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative pt-32 pb-20 px-6">
            <div class="max-w-7xl mx-auto flex flex-col items-center text-center">
                
                <!-- App Logo -->
                @if(isset($settings['app_logo']))
                <div class="mb-8 animate-bounce">
                    <img src="{{ asset('storage/'.$settings['app_logo']) }}" class="w-24 h-24 md:w-32 md:h-32 object-contain rounded-3xl shadow-2xl border-4 border-white">
                </div>
                @endif

                <div class="max-w-3xl">
                    <h1 class="text-5xl md:text-7xl font-black text-slate-900 mb-6 leading-[1.1]">
                        {{ $settings['landing_title'] ?? 'اشحن معداتك بكل سهولة' }}
                    </h1>
                    <p class="text-xl text-slate-600 mb-12 leading-relaxed">
                        {{ $settings['landing_subtitle'] ?? 'المنصة الأولى لطلب وتأجير المعدات الثقيلة والشاحنات في جيبك.' }}
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-6 mb-20">
                        @if(isset($settings['ios_app_link']))
                        <a href="{{ $settings['ios_app_link'] }}" target="_blank" class="group flex items-center gap-4 px-10 py-5 bg-slate-900 text-white rounded-full font-bold transition-all hover:scale-105 hover:shadow-2xl">
                            <i class="fab fa-apple text-2xl"></i>
                            تحميل للآيفون
                        </a>
                        @endif

                        @if(isset($settings['android_app_file']))
                        <a href="{{ asset('storage/'.$settings['android_app_file']) }}" class="group flex items-center gap-4 px-10 py-5 bg-indigo-600 text-white rounded-full font-bold transition-all hover:scale-105 hover:shadow-2xl">
                            <i class="fab fa-android text-2xl"></i>
                            تحميل أندرويد (APK)
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Showcase Screens -->
                <div class="relative w-full max-w-5xl grid grid-cols-1 md:grid-cols-3 gap-8 items-center pt-10">
                    <div class="hidden md:block transform -rotate-12 translate-x-10">
                        <img src="{{ isset($settings['app_screenshot_2']) ? asset('storage/'.$settings['app_screenshot_2']) : 'https://via.placeholder.com/300x600' }}" class="rounded-[3rem] shadow-2xl glass-card p-2 border-white/50 border-4">
                    </div>
                    <div class="z-10 transform scale-110 shadow-3xl">
                        <img src="{{ isset($settings['app_screenshot_1']) ? asset('storage/'.$settings['app_screenshot_1']) : 'https://via.placeholder.com/300x600' }}" class="rounded-[3.5rem] shadow-2xl border-[12px] border-slate-900">
                    </div>
                    <div class="hidden md:block transform rotate-12 -translate-x-10">
                        <img src="{{ isset($settings['app_screenshot_3']) ? asset('storage/'.$settings['app_screenshot_3']) : 'https://via.placeholder.com/300x600' }}" class="rounded-[3rem] shadow-2xl glass-card p-2 border-white/50 border-4">
                    </div>
                </div>
            </div>
        </main>

        <!-- Features -->
        <section class="py-32 bg-white/50">
            <div class="max-w-7xl mx-auto px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <div class="glass-card p-10 rounded-[2.5rem]">
                        <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">أمان تام</h3>
                        <p class="text-slate-500">نضمن لك حقوقك في كل عملية حجز تتم عبر منصتنا.</p>
                    </div>
                    <div class="glass-card p-10 rounded-[2.5rem]">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">سرعة الإنجاز</h3>
                        <p class="text-slate-500">ابحث، احجز، وادفع في أقل من دقيقة واحدة.</p>
                    </div>
                    <div class="glass-card p-10 rounded-[2.5rem]">
                        <div class="w-16 h-16 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-6 text-3xl">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-4">تغطية واسعة</h3>
                        <p class="text-slate-500">مئات الشاحنات والمعدات قريبة منك في أي مكان.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 border-t border-indigo-100 text-center">
            <p class="text-slate-400 font-bold mb-4">© {{ date('Y') }} Bull Station. جميع الحقوق محفوظة.</p>
            <div class="flex justify-center gap-6">
                <a href="{{ route('policies.public') }}" class="text-indigo-600 hover:underline">الشروط والسياسات</a>
            </div>
        </footer>
    </div>
</x-guest.layout>