<x-admin.layout>
    <x-slot name="title">إدارة المستخدمين</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>إدارة المستخدمين</span>
            @can('manage trucks')
                <a href="{{ route('admin.notifications.broadcast.form') }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 md:hidden">
                    إشعار عام
                </a>
            @endcan
        </div>
    </x-slot>

    {{-- رسائل التنبيه --}}
    @if (session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-400 rounded-lg" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded-lg" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- الزر العام للشاشات الكبيرة --}}
    <div class="mb-6 flex justify-end">
        @can('manage trucks')
            <a href="{{ route('admin.notifications.broadcast.form') }}"
               class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 shadow-sm transition">
                إرسال إشعار لجميع المستخدمين
            </a>
        @endcan
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-right">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الهاتف</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">الدور والحالة</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr class="{{ $user->blocked_at ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $user->name }}
                                @if($user->id === auth()->id()) 
                                    <span class="mr-2 text-xs text-blue-600 font-bold bg-blue-50 px-2 py-1 rounded-full border border-blue-200">أنت</span> 
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dir-ltr">{{ $user->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($user->hasRole('admin')) bg-red-100 text-red-800 
                                    @elseif($user->hasRole('manager')) bg-blue-100 text-blue-800 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $user->getRoleNames()->first() ?? $user->account_type }}
                                </span>
                                @if($user->blocked_at)
                                    <span class="mr-2 px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-600 text-white">محظور</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex flex-wrap items-center justify-center gap-3">
                                    
                                    {{-- 1. عرض الملف الشخصي --}}
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="text-green-600 hover:text-green-900 font-bold transition">الملف الشخصي</a>

                                    {{-- 2. زر الإرسال --}}
                                    @can('manage trucks')
                                        <button onclick="openModal('notify-modal-{{ $user->id }}')" class="text-blue-600 hover:text-blue-800 font-bold transition">
                                            إرسال إشعار
                                        </button>
                                    @endcan
                                    
                                    {{-- 3. إجراءات الإدارة --}}
                                    @if(auth()->id() !== $user->id && !$user->hasRole('admin'))
                                        
                                        {{-- تغيير الدور --}}
                                        @can('promote users')
                                            <form action="{{ route('admin.users.role.update', $user->id) }}" method="POST" class="flex items-center gap-1 border-r pr-3 border-gray-300">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" class="block w-24 pl-2 pr-6 py-1 text-xs border-gray-300 rounded-md focus:ring-indigo-500">
                                                    @foreach(['client', 'manager', 'admin'] as $role)
                                                        <option value="{{ $role }}" @if($user->hasRole($role)) selected @endif>{{ $role }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-xs font-bold">تعديل</button>
                                            </form>
                                        @endcan

                                        {{-- حظر / إلغاء حظر --}}
                                        <form action="{{ route('admin.users.toggle-block', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('هل أنت متأكد من تغيير حالة حظر هذا المستخدم؟');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
    class="px-3 py-1 text-xs rounded-md font-bold text-white transition 
    {{ $user->blocked_at ? 'bg-blue-600 hover:bg-blue-700' : 'bg-pink-500 hover:bg-pink-600' }}">
    {{ $user->blocked_at ? 'إلغاء الحظر' : 'حظر' }}
</button>
                                        </form>

                                        {{-- حذف --}}
                                        @can('delete users')
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold transition">حذف</button>
                                            </form>
                                        @endcan

                                    @else
                                        <span class="text-xs text-gray-400 italic">إدارة ذاتية</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic font-medium">لا يوجد مستخدمون حالياً في النظام.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
    
    {{-- النماذج المنبثقة (Modals) --}}
    @foreach ($users as $user)
        <div id="notify-modal-{{ $user->id }}" 
             class="js-modal-backdrop fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4" 
             style="display:none;"
             onclick="closeModal('notify-modal-{{ $user->id }}')">
            
            <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-md transform transition-all" 
                 onclick="event.stopPropagation()">
                
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-xl font-bold text-gray-800">إرسال إشعار لـ {{ $user->name }}</h4>
                    <button onclick="closeModal('notify-modal-{{ $user->id }}')" class="text-gray-400 hover:text-gray-600 transition text-2xl">&times;</button>
                </div>

                <form action="{{ route('admin.users.notify.send', $user->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">عنوان الإشعار</label>
                        <input type="text" name="title" placeholder="ادخل عنواناً جذاباً" required 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نص الرسالة</label>
                        <textarea name="body" placeholder="اكتب تفاصيل الإشعار هنا..." rows="4" required 
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onclick="closeModal('notify-modal-{{ $user->id }}')" 
                                class="px-5 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">إلغاء</button>
                        <button type="submit" 
                                class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md transition">إرسال الآن</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

</x-admin.layout>