<x-admin.layout>
    <x-slot name="title">إرسال إشعار عام</x-slot>
    <x-slot name="header">إرسال إشعار لجميع المستخدمين</x-slot>

    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
    @endif

    <div class="bg-white p-8 rounded-lg shadow-lg max-w-2xl mx-auto">
        <form action="{{ route('admin.notifications.broadcast.send') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">عنوان الإشعار</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       class="mt-1 block w-full px-3 py-2 border rounded-md" placeholder="مثال: تحديث مهم للخدمة">
            </div>
            
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">نص الإشعار</label>
                <textarea name="body" id="body" rows="4" required
                          class="mt-1 block w-full px-3 py-2 border rounded-md">{{ old('body') }}</textarea>
            </div>
            
            <div class="flex justify-end pt-4">
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    إرسال الإشعار للكل
                </button>
            </div>
        </form>
    </div>
</x-admin.layout>