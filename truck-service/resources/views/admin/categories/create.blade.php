<x-admin.layout>
    <x-slot name="title">إضافة تصنيف جديد</x-slot>
       <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>إضافة تصنيف جديد</span>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                &larr; العودة
            </a>
        </div>
    </x-slot>

    <div class="bg-white p-8 rounded-lg shadow-lg">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">اسم التصنيف</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700">أيقونة التصنيف</label>
                <input type="file" name="icon" id="icon" required
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('icon') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-end space-x-4 space-x-reverse">
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">إلغاء</a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">حفظ</button>
            </div>
        </form>
    </div>
</x-admin.layout>