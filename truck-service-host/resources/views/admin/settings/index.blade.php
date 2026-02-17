<x-admin.layout>
    <x-slot name="header">إعدادات النظام</x-slot>
    
    <div class="bg-white p-8 rounded-lg shadow-md max-w-lg">
        <h3 class="text-lg font-bold mb-6 border-b pb-2">بوابة الدفع (Tap Payments)</h3>
        
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $mode === 'test' ? 'border-yellow-500 bg-yellow-50' : '' }}">
                    <input type="radio" name="tap_payment_mode" value="test" {{ $mode === 'test' ? 'checked' : '' }} class="text-yellow-600">
                    <div class="mr-3">
                        <p class="font-bold">وضع الاختبار (Sandbox)</p>
                        <p class="text-sm text-gray-500">استخدم مفاتيح التجربة فقط.</p>
                    </div>
                </label>

                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $mode === 'live' ? 'border-green-500 bg-green-50' : '' }}">
                    <input type="radio" name="tap_payment_mode" value="live" {{ $mode === 'live' ? 'checked' : '' }} class="text-green-600">
                    <div class="mr-3">
                        <p class="font-bold">وضع التشغيل الحقيقي (Live)</p>
                        <p class="text-sm text-gray-500">تنبيه: سيتم استقبال مدفوعات حقيقية.</p>
                    </div>
                </label>
            </div>

            <button type="submit" class="mt-8 w-full bg-gray-800 text-white py-3 rounded-lg font-bold hover:bg-gray-700 transition">
                حفظ الإعدادات
            </button>
        </form>
    </div>
</x-admin.layout>