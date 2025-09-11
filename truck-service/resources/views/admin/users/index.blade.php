<x-admin.layout>
    <x-slot name="title">إدارة المستخدمين</x-slot>
    <x-slot name="header">إدارة المستخدمين</x-slot>
<main>
    <div class="container max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-400 rounded-lg" role="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded-lg" role="alert">{{ session('error') }}</div>
        @endif

        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاسم</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الهاتف</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدور الحالي</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id()) <span class="text-xs text-blue-600">(أنت)</span> @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                                    @if($user->hasRole('admin')) text-red-600 @elseif($user->hasRole('manager')) text-blue-600 @else text-gray-600 @endif">
                                    {{-- استخدام `getRoleNames` هو الأدق --}}
                                    {{ $user->getRoleNames()->first() ?? $user->account_type }}
                                </td>
                               <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
    {{-- الشرط الجديد والمحسن لعرض الإجراءات --}}
    @if(auth()->id() === $user->id || $user->hasRole('admin'))
        <span class="text-xs text-gray-400">لا توجد إجراءات متاحة</span>
    @else
        <div class="flex items-center justify-center space-x-4 space-x-reverse">
            @if($user->account_type !== 'truck_owner')
                @can('promote users')
                    <form action="{{ route('admin.users.role.update', $user->id) }}" method="POST" class="flex items-center space-x-2 space-x-reverse">
                        @csrf
                        @method('PATCH')
                        
                        {{-- >>-- التعديل هنا: أعدنا خيار الأدمن إلى القائمة --<< --}}
                        <select name="role" class="block w-32 pl-3 pr-8 py-1 text-xs border-gray-300 rounded-md">
                            @foreach(['client', 'manager', 'admin'] as $role)
                                <option value="{{ $role }}" @if($user->hasRole($role)) selected @endif>{{ $role }}</option>
                            @endforeach
                        </select>
                        {{-- >>-- نهاية التعديل --<< --}}

                        <button type="submit" class="px-2 py-1 text-xs text-white bg-indigo-600 rounded hover:bg-indigo-700">تحديث</button>
                    </form>
                @endcan
            @endif

            @can('delete users')
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700">حذف</button>
                </form>
            @endcan
        </div>
    @endif
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">لا يوجد مستخدمون.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</main>
</x-admin.layout>