<x-admin.layout>
    <x-slot name="header">إعدادات صفحة الهبوط (Landing Page)</x-slot>

    <form action="{{ route('admin.settings.landing.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- النصوص -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <h3 class="font-bold text-gray-800 border-b pb-2">المحتوى النصي</h3>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">العنوان الرئيسي</label>
                    <input type="text" name="landing_title" value="{{ $settings['landing_title'] ?? '' }}" class="w-full border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">العنوان الفرعي</label>
                    <input type="text" name="landing_subtitle" value="{{ $settings['landing_subtitle'] ?? '' }}" class="w-full border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">الوصف المطول</label>
                    <textarea name="landing_description" rows="4" class="w-full border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500">{{ $settings['landing_description'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- روابط التحميل والشعار -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <h3 class="font-bold text-gray-800 border-b pb-2">روابط التحميل والهوية</h3>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 text-indigo-600">شعار التطبيق (Logo)</label>
                    <input type="file" name="app_logo" class="w-full text-xs">
                    @if(isset($settings['app_logo']))
                        <img src="{{ asset('storage/'.$settings['app_logo']) }}" class="h-12 mt-2">
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 text-green-600">ملف أندرويد (APK)</label>
                    <input type="file" name="android_app_file" class="w-full text-xs">
                    @if(isset($settings['android_app_file']))
                        <p class="text-[10px] mt-1 text-gray-400 truncate">{{ $settings['android_app_file'] }}</p>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1 text-blue-600">رابط متجر أبل (App Store)</label>
                    <input type="text" name="ios_app_link" value="{{ $settings['ios_app_link'] ?? '' }}" placeholder="https://apps.apple.com/..." class="w-full border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- لقطات الشاشة -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 border-b pb-2 mb-4">صور شاشات التطبيق (3 صور)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(['app_screenshot_1', 'app_screenshot_2', 'app_screenshot_3'] as $shot)
                <div class="p-4 border border-dashed border-gray-200 rounded-2xl text-center">
                    @if(isset($settings[$shot]))
                        <img src="{{ asset('storage/'.$settings[$shot]) }}" class="h-40 mx-auto mb-4 rounded-xl shadow-md">
                    @endif
                    <input type="file" name="{{ $shot }}" class="text-[10px]">
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold text-lg hover:bg-indigo-700 shadow-xl transition-all active:scale-95">
            تحديث صفحة الهبوط والملفات
        </button>
    </form>
</x-admin.layout>