<x-admin.layout>
    <x-slot name="title">إدارة التصنيفات</x-slot>
    <x-slot name="header">إدارة التصنيفات</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">قائمة التصنيفات</h2>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
            إضافة تصنيف جديد
        </a>
    </div>

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الأيقونة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">عدد التصنيفات الفرعية</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-6 py-4">
                                <img src="{{ asset('storage/' . $category->icon) }}" alt="{{ $category->name }}" class="w-10 h-10 rounded-full object-cover">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $category->subCategories()->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.sub_categories.index', $category->id) }}" class="text-green-600 hover:text-green-900">إدارة الفرعية</a>
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="text-indigo-600 hover:text-indigo-900 ml-4">تعديل</a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline-block" onsubmit="return confirm('هل أنت متأكد؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center">لا توجد تصنيفات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
         <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $categories->links() }}
        </div>
    </div>
</x-admin.layout>