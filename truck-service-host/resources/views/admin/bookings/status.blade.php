<x-admin.layout>
    <x-slot name="title">إدارة حالة الحجز #{{ $booking->id }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>إدارة حالة الحجز #{{ $booking->id }}</span>
            <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                &larr; العودة إلى قائمة الحجوزات
            </a>
        </div>
    </x-slot>

    @if (session('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">{{ session('error') }}</div>
    @endif

    {{-- قسم تفاصيل الحجز --}}
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <h3 class="text-lg font-semibold border-b pb-2 mb-4">تفاصيل الحجز</h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
            <div><dt class="font-medium text-gray-500">الشاحنة</dt><dd class="text-gray-900">{{ $booking->truck->name }} ({{ $booking->truck->model }})</dd></div>
            <div><dt class="font-medium text-gray-500">العميل</dt><dd class="text-gray-900">{{ $booking->customer->name }}</dd></div>
            <div><dt class="font-medium text-gray-500">فترة الحجز</dt><dd class="text-gray-900 dir-ltr text-right">{{ $booking->start_datetime->format('Y-m-d H:i') }} &rarr; {{ $booking->end_datetime->format('Y-m-d H:i') }}</dd></div>
            <div><dt class="font-medium text-gray-500">السعر الإجمالي</dt><dd class="text-gray-900 font-bold">{{ $booking->total_price }}</dd></div>
            <div><dt class="font-medium text-gray-500">الحالة الحالية</dt>
                <dd>
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full capitalize 
                        @if($booking->status == 'pending') bg-yellow-100 text-yellow-800 
                        @elseif(in_array($booking->status, ['approved', 'confirmed', 'completed'])) bg-green-100 text-green-800 
                        @else bg-red-100 text-red-800 @endif">
                        {{ $booking->status }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

       {{-- قسم الإجراءات المتاحة --}}
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h3 class="text-lg font-semibold mb-4">الإجراءات المتاحة</h3>
        
        {{-- التحقق مما إذا كان الحجز في حالة نهائية --}}
        @if(in_array($booking->status, ['completed', 'rejected', 'cancelled']))
            <p class="text-gray-600">لا توجد إجراءات متاحة لهذا الحجز لأنه في حالة نهائية ({{ $booking->status }}).</p>
        @else
            <div class="flex items-center space-x-4 space-x-reverse">
                
                {{-- زر الإكمال: يظهر لكل الحالات غير النهائية --}}
                <form action="{{ route('admin.bookings.status.update', $booking->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من وضع علامة (مكتمل) على هذا الحجز؟');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-500 rounded-md hover:bg-green-700">
                        وضع علامة "مكتمل" (Completed)
                    </button>
                </form>

                {{-- زر الرفض: يظهر لكل الحالات غير النهائية --}}
                <form action="{{ route('admin.bookings.status.update', $booking->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رفض هذا الحجز؟ هذا الإجراء نهائي.');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        رفض الحجز (Rejected)
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-admin.layout>
