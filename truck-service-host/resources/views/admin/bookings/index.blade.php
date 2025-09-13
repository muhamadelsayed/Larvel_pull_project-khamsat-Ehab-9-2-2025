<x-admin.layout>
        <x-slot name="title">إدارة الحجوزات</x-slot>
        <x-slot name="header">إدارة الحجوزات</x-slot>

        {{-- فلاتر الحالة --}}
        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.bookings.index') }}" class="{{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600' }} px-3 py-1 rounded-full text-sm font-medium">الكل</a>
            @foreach(['pending', 'approved', 'confirmed', 'rejected', 'cancelled', 'completed'] as $status)
                <a href="{{ route('admin.bookings.index', ['status' => $status]) }}" class="{{ request('status') == $status ? 'bg-gray-700 text-white' : 'bg-white text-gray-600' }} px-3 py-1 rounded-full text-sm font-medium capitalize">{{ $status }}</a>
            @endforeach
        </div>

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الشاحنة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">العميل</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التواريخ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">السعر</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><a href="{{ route('admin.trucks.show', $booking->truck_id) }}" class="text-indigo-600 hover:underline">{{ $booking->truck->model }}</a></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><a href="{{ route('admin.users.show', $booking->customer_id) }}" class="text-indigo-600 hover:underline">{{ $booking->customer->name }}</a></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $booking->start_datetime->format('Y/m/d') }} - {{ $booking->end_datetime->format('Y/m/d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $booking->total_price }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize 
                                        @if($booking->status == 'pending') bg-yellow-100 text-yellow-800 
                                        @elseif($booking->status == 'approved' || $booking->status == 'confirmed') bg-green-100 text-green-800 
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center">لا توجد حجوزات تطابق هذا الفلتر.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $bookings->appends(request()->query())->links() }}
            </div>
        </div>
    </x-admin.layout>