<x-guest.layout>
    <x-slot name="title">{{ $settings['landing_title'] ?? 'بول ستيشن - حمل التطبيق الآن' }}</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white pt-16 pb-32 overflow-hidden">
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/2 mb-12 lg:mb-0">
                    <span class="inline-block py-1 px-3 mb-4 text-xs font-semibold bg-indigo-50 text-indigo-600 rounded-full uppercase tracking-px">وصلنا الآن</span>
                    <h1 class="text-5xl md:text-6xl font-black text-gray-900 mb-6 leading-tight">
                        {{ $settings['landing_title'] ?? 'أفضل حل لنقل المعدات الثقيلة' }}
                    </h1>
                    <p class="text-lg text-gray-600 mb-10 shadow-sm">
                        {{ $settings['landing_subtitle'] ?? 'حمل تطبيق بول ستيشن الآن وابدأ في إدارة حجوزاتك وشاحناتك بكل سهولة وأمان.' }}
                    </p>
                    
                    <div class="flex flex-wrap gap-4">
                        @if(isset($settings['android_app_file']))
                        <a href="{{ asset('storage/'.$settings['android_app_file']) }}" class="flex items-center bg-black text-white px-8 py-3 rounded-2xl hover:bg-gray-800 transition shadow-xl border-2 border-gray-900">
                           <span class="mr-3 text-left leading-tight"><small class="block text-[10px] opacity-70">حمل للأندرويد</small><b class="text-lg">Google Play</b></span>
                        </a>
                        @endif
                        
                        @if(isset($settings['ios_app_link']))
                        <a href="{{ $settings['ios_app_link'] }}" target="_blank" class="flex items-center bg-white text-black px-8 py-3 rounded-2xl hover:bg-gray-50 transition shadow-xl border-2 border-gray-100">
                           <span class="mr-3 text-left leading-tight"><small class="block text-[10px] opacity-70">حمل للآيفون</small><b class="text-lg">App Store</b></span>
                        </a>
                        @endif
                    </div>
                </div>
                
                <div class="w-full lg:w-1/2 relative">
                     <img class="relative z-10 mx-auto w-64 md:w-80 shadow-2xl rounded-[3rem] border-[10px] border-gray-900" src="{{ isset($settings['app_screenshot_1']) ? asset('storage/'.$settings['app_screenshot_1']) : 'https://via.placeholder.com/400x800' }}" alt="App Screenshot">
                     <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-indigo-50 rounded-full filter blur-3xl opacity-50"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-gray-50 py-24">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">لماذا بول ستيشن؟</h2>
                <p class="max-w-2xl mx-auto text-gray-600">{{ $settings['landing_description'] ?? 'نحن نوفر لك أسرع وأسهل طريقة لطلب الشاحنات والمعدات الثقيلة في المملكة.' }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach(['app_screenshot_1', 'app_screenshot_2', 'app_screenshot_3'] as $shot)
                    @if(isset($settings[$shot]))
                    <div class="bg-white p-4 rounded-[2rem] shadow-sm hover:shadow-xl transition-all duration-500">
                        <img src="{{ asset('storage/'.$settings[$shot]) }}" class="rounded-2xl w-full h-auto">
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-white border-t border-gray-100">
        <div class="container mx-auto px-6 text-center">
            <p class="text-gray-500">© {{ date('Y') }} Bull Station. جميع الحقوق محفوظة.</p>
            <a href="{{ route('policies.public') }}" class="text-indigo-600 font-bold mt-4 inline-block hover:underline text-sm">سياسة الخصوصية والشروط</a>
        </div>
    </footer>
</x-guest.layout>