<x-admin.layout>
    <x-slot name="title">تعديل التصنيف الفرعي</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>تعديل التصنيف الفرعي: <span class="text-indigo-600">"{{ $subCategory->name }}"</span></span>
            <a href="{{ route('admin.sub_categories.index', $category->id) }}" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                &larr; العودة
            </a>
        </div>
    </x-slot>
    
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-2xl mx-auto">
        {{-- ... باقي الفورم كما هو بدون تغيير ... --}}
        <form action="{{ route('admin.sub_categories.update', ['category' => $category->id, 'subCategory' => $subCategory->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PATCH')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">اسم التصنيف الفرعي</dlabel>
                <input type="text" name="name" id="name" value="{{ old('name', $subCategory->name) }}" required
                       class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700">تغيير الأيقونة (اختياري)</label>
                <div class="mt-2 flex items-center space-x-4 space-x-reverse">
                    @if($subCategory->icon)
                        <img src="{{ Storage::url($subCategory->icon) }}" alt="{{ $subCategory->name }}" class="w-16 h-16 rounded-full object-cover">
                    @endif
                    <input type="file" name="icon" id="icon"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                @error('icon') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-end pt-4">
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">تحديث</button>
            </div>
        </form>
    </div>
</x-admin.layout>