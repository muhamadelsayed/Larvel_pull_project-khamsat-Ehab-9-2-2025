<x-admin.layout>
    <x-slot name="title">إدارة الشاحنات</x-slot>
    <x-slot name="header">إدارة الشاحنات</x-slot>

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-400" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- فلاتر الحالة --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.trucks.index') }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 {{ !request('status') ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            الكل
        </a>
        <a href="{{ route('admin.trucks.index', ['status' => 'pending']) }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 {{ request('status') == 'pending' ? 'bg-yellow-500 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            قيد المراجعة
        </a>
        <a href="{{ route('admin.trucks.index', ['status' => 'active']) }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 {{ request('status') == 'active' ? 'bg-green-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            نشطة
        </a>
        <a href="{{ route('admin.trucks.index', ['status' => 'inactive']) }}" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 {{ request('status') == 'inactive' ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
            غير نشطة
        </a>
    </div>

    {{-- Responsive Cards for Mobile & Table for Desktop --}}
    <div class="hidden md:block bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">ID</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">الاسم</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">الموديل</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">المالك</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">التصنيف</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">الحالة</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($trucks as $truck)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $truck->id }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $truck->name }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">{{ $truck->model }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">{{ $truck->user->name }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">{{ $truck->category->name }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
                                    @if($truck->status == 'pending') 
                                        bg-yellow-100 text-yellow-800
                                    @elseif($truck->status == 'active') 
                                        bg-green-100 text-green-800
                                    @else 
                                        bg-red-100 text-red-800
                                    @endif">
                                    {{ $truck->status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.trucks.show', $truck->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors duration-150 font-medium">
                                    مراجعة / عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    لا توجد شاحنات تطابق هذا الفلتر.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-200">
            {{ $trucks->appends(request()->query())->links() }}
        </div>
    </div>

    {{-- Responsive Cards View for Mobile --}}
    <div class="md:hidden space-y-4">
        @forelse ($trucks as $truck)
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-150">
                <div class="p-4 space-y-3">
                    {{-- Header with ID and Status --}}
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-500 font-semibold uppercase">ID</span>
                            <span class="text-lg font-bold text-gray-900">{{ $truck->id }}</span>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
                            @if($truck->status == 'pending') 
                                bg-yellow-100 text-yellow-800
                            @elseif($truck->status == 'active') 
                                bg-green-100 text-green-800
                            @else 
                                bg-red-100 text-red-800
                            @endif">
                            {{ $truck->status }}
                        </span>
                    </div>

                    {{-- Truck Name --}}
                    <div class="border-t border-gray-100 pt-3">
                        <span class="text-xs text-gray-500 font-semibold uppercase block mb-1">الاسم</span>
                        <p class="text-sm font-semibold text-gray-900">{{ $truck->name }}</p>
                    </div>

                    {{-- Model --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-xs text-gray-500 font-semibold uppercase block mb-1">الموديل</span>
                            <p class="text-sm text-gray-600">{{ $truck->model }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 font-semibold uppercase block mb-1">التصنيف</span>
                            <p class="text-sm text-gray-600">{{ $truck->category->name }}</p>
                        </div>
                    </div>

                    {{-- Owner --}}
                    <div class="border-t border-gray-100 pt-3">
                        <span class="text-xs text-gray-500 font-semibold uppercase block mb-1">المالك</span>
                        <p class="text-sm text-gray-600">{{ $truck->user->name }}</p>
                    </div>

                    {{-- Action Button --}}
                    <div class="border-t border-gray-100 pt-3">
                        <a href="{{ route('admin.trucks.show', $truck->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-150 font-medium text-sm">
                            مراجعة / عرض
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-12 text-center">
                <div class="flex flex-col items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">لا توجد شاحنات تطابق هذا الفلتر.</p>
                </div>
            </div>
        @endforelse

        {{-- Pagination for Mobile --}}
        <div class="flex justify-center py-4">
            {{ $trucks->appends(request()->query())->links() }}
        </div>
    </div>
</x-admin.layout>