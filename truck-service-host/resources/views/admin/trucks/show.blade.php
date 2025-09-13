<x-admin.layout>
    <x-slot name="title">مراجعة الشاحنة</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>مراجعة شاحنة: <span class="text-indigo-600">"{{ $truck->model }}"</span></span>
            <a href="{{ route('admin.trucks.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                &larr; العودة
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">{{ session('success') }}</div>
    @endif

    {{-- فورم تغيير الحالة --}}
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <h3 class="text-lg font-semibold mb-4">تغيير حالة الشاحنة</h3>
        <form action="{{ route('admin.trucks.status.update', $truck->id) }}" method="POST" class="flex items-center space-x-4 space-x-reverse">
            @csrf
            @method('PATCH')
            <select name="status" class="block w-48 pl-3 pr-8 py-2 text-base border-gray-300 rounded-md">
                <option value="active" @if($truck->status == 'active') selected @endif>تفعيل (Active)</option>
                <option value="pending" @if($truck->status == 'pending') selected @endif>قيد المراجعة (Pending)</option>
                <option value="inactive" @if($truck->status == 'inactive') selected @endif>إلغاء التفعيل (Inactive)</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">تحديث الحالة</button>
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                @if($truck->status == 'pending') bg-yellow-100 text-yellow-800 
                @elseif($truck->status == 'active') bg-green-100 text-green-800 
                @else bg-red-100 text-red-800 @endif">
                الحالة الحالية: {{ $truck->status }}
            </span>
        </form>
    </div>

    {{-- عرض تفاصيل الشاحنة --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-semibold border-b pb-2 mb-4">التفاصيل الأساسية</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                <div><dt class="font-medium text-gray-500">الموديل</dt><dd class="text-gray-900">{{ $truck->model }}</dd></div>
                <div><dt class="font-medium text-gray-500">سنة التصنيع</dt><dd class="text-gray-900">{{ $truck->year_of_manufacture }}</dd></div>
                <div><dt class="font-medium text-gray-500">التصنيف</dt><dd class="text-gray-900">{{ $truck->category->name }} &rarr; {{ $truck->subCategory->name }}</dd></div>
                <div><dt class="font-medium text-gray-500">الحجم</dt><dd class="text-gray-900">{{ $truck->size }}</dd></div>
                <div class="md:col-span-2"><dt class="font-medium text-gray-500">الوصف</dt><dd class="text-gray-900 whitespace-pre-wrap">{{ $truck->description }}</dd></div>
                <div class="md:col-span-2"><dt class="font-medium text-gray-500">مميزات إضافية</dt><dd class="text-gray-900 whitespace-pre-wrap">{{ $truck->additional_features ?? 'لا يوجد' }}</dd></div>
            </dl>
            <h3 class="text-lg font-semibold border-b pb-2 mt-6 mb-4">التسعير والتوفر</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                <div><dt class="font-medium text-gray-500">السعر/اليوم</dt><dd class="text-gray-900 font-bold">{{ $truck->price_per_day }}</dd></div>
                <div><dt class="font-medium text-gray-500">السعر/الساعة</dt><dd class="text-gray-900 font-bold">{{ $truck->price_per_hour }}</dd></div>
                <div><dt class="font-medium text-gray-500">ساعات العمل</dt><dd class="text-gray-900">{{ \Carbon\Carbon::parse($truck->work_start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($truck->work_end_time)->format('h:i A') }}</dd></div>
                <div><dt class="font-medium text-gray-500">التوصيل</dt><dd class="text-gray-900">{{ $truck->delivery_available ? 'متاح' : 'غير متاح' }} ({{ $truck->delivery_price ?? 0 }})</dd></div>
                <div class="md:col-span-2"><dt class="font-medium text-gray-500">مكان الاستلام</dt><dd class="text-gray-900">{{ $truck->pickup_location }}</dd></div>
            </dl>
        </div>
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold border-b pb-2 mb-4">بيانات المالك</h3>
                <dl>
                    <div><dt class="font-medium text-gray-500">الاسم</dt><dd class="text-gray-900">{{ $truck->user->name }}</dd></div>
                    <div class="mt-4"><dt class="font-medium text-gray-500">رقم الهاتف</dt><dd class="text-gray-900">{{ $truck->user->phone }}</dd></div>
                </dl>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold border-b pb-2 mb-4">الصور والفيديو</h3>
                <div class="grid grid-cols-2 gap-2">
                    @forelse($truck->images as $image)
                    <a href="{{ asset('storage/' . $image->path) }}" target="_blank"><img src="{{ asset('storage/' . $image->path) }}" class="rounded-md object-cover aspect-square"></a>
                    @empty
                        <p class="col-span-2 text-sm text-gray-500">لا توجد صور.</p>
                    @endforelse
                </div>
                @if($truck->video)
                    <video controls class="mt-4 w-full rounded-md"><source src="{{ asset('storage/' . $truck->video) }}" type="video/mp4">متصفحك لا يدعم الفيديو.</video>
                @endif
            </div>
        </div>
    </div>
</x-admin.layout>