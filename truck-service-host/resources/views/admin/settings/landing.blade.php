<x-admin.layout>
    <x-slot name="header">إعدادات صفحة الهبوط</x-slot>

    <form action="{{ route('admin.settings.landing.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <!-- النصوص -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
            <h3 class="text-lg font-bold mb-4 border-b pb-2">النصوص الرئيسية</h3>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">العنوان الرئيسي (Hero Title)</label>
                    <input type="text" name="landing_title" value="{{ $settings['landing_title'] ?? '' }}" class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">العنوان الفرعي (Subtitle)</label>
                    <input type="text" name="landing_subtitle" value="{{ $settings['landing_subtitle'] ?? '' }}" class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">وصف التطبيق</label>
                    <textarea name="landing_description" rows="3" class="w-full border rounded-lg px-4 py-2">{{ $settings['landing_description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- ملفات التطبيق -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
            <h3 class="text-lg font-bold mb-4 border-b pb-2">روابط وتحميل التطبيق</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold mb-1 text-green-600">ملف الأندرويد (APK)</label>
                    <input type="file" name="android_app_file" class="w-full border rounded-lg px-3 py-2">
                    @if(isset($settings['android_app_file']))
                        <p class="text-xs mt-1 text-gray-500">الملف الحالي: {{ $settings['android_app_file'] }}</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-blue-600">رابط الـ iOS (App Store)</label>
                    <input type="text" name="ios_app_link" value="{{ $settings['ios_app_link'] ?? '' }}" placeholder="https://apps.apple.com/..." class="w-full border rounded-lg px-4 py-2">
                </div>
            </div>
        </div>

        <!-- صور التطبيق -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
            <h3 class="text-lg font-bold mb-4 border-b pb-2">صور من داخل التطبيق (Screenshots)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['app_screenshot_1', 'app_screenshot_2', 'app_screenshot_3'] as $shot)
                <div class="border p-3 rounded-lg text-center">
                    @if(isset($settings[$shot]))
                        <img src="{{ asset('storage/'.$settings[$shot]) }}" class="h-32 mx-auto mb-2 rounded shadow">
                    @endif
                    <input type="file" name="{{ $shot }}" class="text-xs">
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-black text-lg hover:bg-indigo-700 shadow-xl transition">
            حفظ وتحديث صفحة الهبوط
        </button>
    </form>
</x-admin.layout>
