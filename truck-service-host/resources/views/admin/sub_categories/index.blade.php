<x-admin.layout>
    <x-slot name="title">التصنيفات الفرعية لـ "{{ $category->name }}"</x-slot>
    <x-slot name="header">التصنيفات الفرعية لـ <span class="text-indigo-600">"{{ $category->name }}"</span></x-slot>

    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                &larr; العودة إلى التصنيفات الرئيسية
            </a>
            <h2 class="text-2xl font-semibold text-gray-700 mt-2">قائمة التصنيفات الفرعية</h2>
        </div>
        <a href="{{ route('admin.sub_categories.create', $category->id) }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
            إضافة تصنيف فرعي جديد
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
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الأيقونة</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم التصنيف الفرعي</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ الإنشاء</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($subCategories as $subCategory)
                        <tr>
                            <td class="px-6 py-4">
                                @if($subCategory->icon)
                                <img src="{{ asset('storage/' . $subCategory->icon) }}" alt="{{ $subCategory->name }}" class="w-10 h-10 rounded-full object-cover">
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $subCategory->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $subCategory->created_at->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.sub_categories.edit', ['category' => $category->id, 'subCategory' => $subCategory->id]) }}" class="text-indigo-600 hover:text-indigo-900 ml-4">تعديل</a>
                                <form action="{{ route('admin.sub_categories.destroy', ['category' => $category->id, 'subCategory' => $subCategory->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                لا توجد تصنيفات فرعية لهذا التصنيف.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
         <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $subCategories->links() }}
        </div>
    </div>
</x-admin.layout>