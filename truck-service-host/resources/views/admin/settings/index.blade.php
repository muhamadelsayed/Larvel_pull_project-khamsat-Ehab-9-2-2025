<x-admin.layout>
    <x-slot name="header">إعدادات النظام</x-slot>

    {{-- رسائل التنبيه --}}
    @if (session('success'))
        <div class="p-4 mb-6 text-sm text-green-700 bg-green-100 border border-green-400 rounded-lg shadow-sm" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- القسم الأول: إعدادات بوابة الدفع -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2 text-gray-800">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    بوابة الدفع (Tap Payments) 
                </h3>
                
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer transition {{ $mode === 'test' ? 'border-indigo-500 bg-indigo-50' : 'hover:bg-gray-50' }}">
                            <input type="radio" name="tap_payment_mode" value="test" {{ $mode === 'test' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <div class="mr-3">
                                <p class="font-bold text-gray-900">وضع الاختبار (Sandbox)</p>
                                <p class="text-xs text-gray-500 italic">مفاتيح التجربة فعالة حالياً</p>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer transition {{ $mode === 'live' ? 'border-green-500 bg-green-50' : 'hover:bg-gray-50' }}">
                            <input type="radio" name="tap_payment_mode" value="live" {{ $mode === 'live' ? 'checked' : '' }} class="w-4 h-4 text-green-600 focus:ring-green-500">
                            <div class="mr-3">
                                <p class="font-bold text-gray-900">وضع التشغيل الحقيقي (Live)</p>
                                <p class="text-xs text-red-500 font-medium">تنبيه: سيتم خصم مبالغ حقيقية</p>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="mt-8 w-full bg-gray-900 text-white py-3 rounded-xl font-bold hover:bg-gray-800 transition shadow-lg">
                        حفظ وضع الدفع
                    </button>
                </form>
            </div>
        </div>

        <!-- القسم الثاني: إدارة بنود الخدمة -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- فورم إضافة بند جديد -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <h3 class="text-lg font-bold mb-6 text-gray-800 border-b pb-2">إضافة بند جديد</h3>
                <form action="{{ route('admin.policies.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">عنوان البند</label>
                        <input type="text" name="title" required class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="مثلاً: سياسة الإلغاء">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">المحتوى</label>
                        <textarea name="content" rows="3" required class="w-full border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none resize-none" placeholder="اكتب تفاصيل البند هنا..."></textarea>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 transition">
                        + إضافة للبند
                    </button>
                </form>
            </div>

            <!-- قائمة البنود الحالية -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <h3 class="text-lg font-bold mb-6 text-gray-800 border-b pb-2">البنود الحالية</h3>
                <div class="space-y-4">
                    @forelse($policies as $policy)
                        <div class="group flex justify-between items-start p-5 border rounded-xl bg-gray-50 hover:bg-white hover:border-indigo-300 transition-all">
                            <div class="flex-1">
                                <h4 class="font-bold text-indigo-900">{{ $policy->title }}</h4>
                                <p class="text-sm text-gray-600 mt-2 leading-relaxed whitespace-pre-line">{{ $policy->content }}</p>
                            </div>
                            <div class="flex items-center gap-1 mr-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- زر التعديل -->
                                <button onclick="openEditPolicyModal('{{ $policy->id }}', '{{ addslashes($policy->title) }}', '{{ addslashes($policy->content) }}')" 
                                        class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-full transition" title="تعديل">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>

                                <!-- زر الحذف -->
                                <form action="{{ route('admin.policies.destroy', $policy->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا البند؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-full transition" title="حذف">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-gray-400">
                            <p class="italic text-sm">لا توجد بنود مضافة حالياً.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تعديل البند -->
    <div id="editPolicyModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 z-[60] flex items-center justify-center p-4 backdrop-blur-sm" style="display:none;" onclick="closeEditPolicyModal()">
        <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-lg transform transition-all border border-gray-100" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-bold text-gray-800">تعديل البند</h4>
                <button onclick="closeEditPolicyModal()" class="text-gray-400 hover:text-gray-600 transition text-3xl leading-none">&times;</button>
            </div>

            <form id="editPolicyForm" method="POST" class="space-y-5">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">عنوان البند</label>
                    <input type="text" name="title" id="edit_policy_title" required 
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">المحتوى</label>
                    <textarea name="content" id="edit_policy_content" rows="6" required 
                              class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none resize-none transition leading-relaxed"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-50">
                    <button type="button" onclick="closeEditPolicyModal()" 
                            class="px-6 py-2.5 text-sm font-bold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition">إلغاء</button>
                    <button type="submit" 
                            class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg transition">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditPolicyModal(id, title, content) {
            const form = document.getElementById('editPolicyForm');
            // التأكد من توجيه الرابط للبند الصحيح
            form.action = `/admin/settings/policies/${id}`;
            
            document.getElementById('edit_policy_title').value = title;
            document.getElementById('edit_policy_content').value = content;
            
            document.getElementById('editPolicyModal').style.display = 'flex';
            // منع التمرير في الخلفية عند فتح المودال
            document.body.style.overflow = 'hidden';
        }

        function closeEditPolicyModal() {
            document.getElementById('editPolicyModal').style.display = 'none';
            // إعادة التمرير
            document.body.style.overflow = 'auto';
        }
    </script>
</x-admin.layout>