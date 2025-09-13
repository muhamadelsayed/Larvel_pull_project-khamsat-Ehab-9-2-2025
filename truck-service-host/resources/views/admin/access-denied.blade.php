<x-guest.layout>
    <x-slot name="title">وصول غير مصرح به</x-slot>

    <div class="flex flex-col items-center justify-center min-h-screen px-4 text-center bg-gray-100">
        <div class="w-full max-w-lg p-10 bg-white rounded-lg shadow-2xl">
            {{-- ... باقي محتوى الصفحة كما هو بدون تغيير ... --}}
            <div class="flex justify-center mb-6">
                <svg class="w-20 h-20 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H4.5a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-800">وصول غير مصرح به</h1>
            <p class="mt-4 text-lg text-gray-600">هذه المنطقة مخصصة لإدارة النظام فقط.</p>
            <p class="mt-2 text-gray-500">الرجاء استخدام التطبيق أو التواصل مع الدعم الفني.</p>
            <div class="mt-8">
                <a href="{{ route('admin.login') }}" class="px-6 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">العودة لصفحة الدخول</a>
            </div>
        </div>
    </div>
</x-guest.layout>

