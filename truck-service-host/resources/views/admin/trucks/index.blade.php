<x-admin.layout>
    <x-slot name="title">إدارة الشاحنات</x-slot>
    <x-slot name="header">إدارة الشاحنات</x-slot>

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">{{ session('success') }}</div>
    @endif

    {{-- فلاتر الحالة --}}
    <div class="mb-4 flex space-x-2 space-x-reverse">
        <a href="{{ route('admin.trucks.index') }}" class="{{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600' }} px-3 py-1 rounded-full text-sm font-medium">الكل</a>
        <a href="{{ route('admin.trucks.index', ['status' => 'pending']) }}" class="{{ request('status') == 'pending' ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600' }} px-3 py-1 rounded-full text-sm font-medium">قيد المراجعة</a>
        <a href="{{ route('admin.trucks.index', ['status' => 'active']) }}" class="{{ request('status') == 'active' ? 'bg-green-500 text-white' : 'bg-white text-gray-600' }} px-3 py-1 rounded-full text-sm font-medium">نشطة</a>
        <a href="{{ route('admin.trucks.index', ['status' => 'inactive']) }}" class="{{ request('status') == 'inactive' ? 'bg-red-500 text-white' : 'bg-white text-gray-600' }} px-3 py-1 rounded-full text-sm font-medium">غير نشطة</a>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموديل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المالك</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التصنيف</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">الحالة</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($trucks as $truck)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $truck->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $truck->model }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $truck->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $truck->category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($truck->status == 'pending') bg-yellow-100 text-yellow-800 
                                    @elseif($truck->status == 'active') bg-green-100 text-green-800 
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $truck->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.trucks.show', $truck->id) }}" class="text-indigo-600 hover:text-indigo-900">مراجعة / عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center">لا توجد شاحنات تطابق هذا الفلتر.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $trucks->appends(request()->query())->links() }}
        </div>
    </div>
</x-admin.layout>