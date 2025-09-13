<x-admin.layout>
    <x-slot name="title">الملف الشخصي لـ {{ $user->name }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>الملف الشخصي: <span class="text-indigo-600">"{{ $user->name }}"</span></span>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                &larr; العودة إلى قائمة المستخدمين
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        {{-- قسم معلومات المستخدم --}}
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-semibold border-b pb-2 mb-4">معلومات المستخدم</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                <div><dt class="font-medium text-gray-500">الاسم</dt><dd class="text-gray-900">{{ $user->name }}</dd></div>
                <div><dt class="font-medium text-gray-500">رقم الهاتف</dt><dd class="text-gray-900">{{ $user->phone }}</dd></div>
                <div><dt class="font-medium text-gray-500">الدور</dt><dd class="text-gray-900 font-semibold">{{ $user->account_type }}</dd></div>
            </dl>
        </div>

        {{-- قسم شاحنات المستخدم (إذا كان صاحب شاحنة) --}}
        @if($user->account_type === 'truck_owner')
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-semibold border-b pb-2 mb-4">شاحنات المستخدم ({{ $user->trucks->count() }})</h3>
            @if($user->trucks->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                    @foreach($user->trucks as $truck)
                        <a href="{{ route('admin.trucks.show', $truck->id) }}" class="block border rounded-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                            <div class="relative">
                                <img src="{{ $truck->images->first() ? asset('storage/' . $truck->images->first()->path) : 'https://via.placeholder.com/400x300?text=No+Image' }}" alt="{{ $truck->model }}" class="w-full h-48 object-cover">
                                <span class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold rounded-full
                                    @if($truck->status == 'pending') bg-yellow-400 text-yellow-800 
                                    @elseif($truck->status == 'active') bg-green-400 text-green-800 
                                    @else bg-red-400 text-red-800 @endif">
                                    {{ $truck->status }}
                                </span>
                            </div>
                            <div class="p-4">
                                <h4 class="text-lg font-bold text-gray-800">{{ $truck->model }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $truck->category->name }} / {{ $truck->subCategory->name }}</p>
                                <p class="text-sm text-gray-500 mt-2 truncate">{{ $truck->description }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">هذا المستخدم لم يضف أي شاحنات بعد.</p>
            @endif
        </div>
        @endif
    </div>
</x-admin.layout>