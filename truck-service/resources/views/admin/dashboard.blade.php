<x-admin.layout>
    <x-slot name="title">لوحة التحكم الرئيسية</x-slot>
    <x-slot name="header">لوحة التحكم الرئيسية</x-slot>

    <!-- ابدأ بإضافة محتوى لوحة التحكم هنا -->
    <div class="px-4 py-6 sm:px-0">
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 p-4 text-center text-gray-500">
            <p>مرحباً بك، {{ auth()->user()->name }}!</p>
            <p class="mt-4">هذه هي الصفحة الرئيسية للوحة التحكم. يمكنك إضافة إحصائيات وتقارير هنا لاحقاً.</p>
        </div>
    </div>
</x-admin.layout>