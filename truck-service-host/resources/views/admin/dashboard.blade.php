<x-admin.layout>
    <x-slot name="header">نظرة عامة على النظام</x-slot>

    <!-- 1. كروت الإحصائيات السريعة -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border-r-4 border-green-500">
            <p class="text-gray-500 text-sm">إجمالي الإيرادات</p>
            <p class="text-2xl font-bold">{{ number_format($stats['total_revenue'], 2) }} ريال</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-r-4 border-blue-500">
            <p class="text-gray-500 text-sm">إجمالي الحجوزات</p>
            <p class="text-2xl font-bold">{{ $stats['total_bookings'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-r-4 border-yellow-500">
            <p class="text-gray-500 text-sm">شاحنات بانتظار التفعيل</p>
            <p class="text-2xl font-bold">{{ $stats['pending_trucks_count'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-r-4 border-indigo-500">
            <p class="text-gray-500 text-sm">إجمالي المستخدمين</p>
            <p class="text-2xl font-bold">{{ $stats['active_users'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- 2. الرسم البياني للعمليات -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm">
            <h3 class="font-bold mb-4">نمو الحجوزات (آخر 7 أيام)</h3>
            <canvas id="bookingsChart" height="120"></canvas>
        </div>

        <!-- 3. حالة الدفع -->
        <div class="bg-white p-6 rounded-xl shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold mb-2">وضع بوابة الدفع</h3>
                <div class="mt-4 p-4 rounded-lg {{ $paymentMode == 'live' ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }}">
                    <p class="text-sm">الوضع الحالي:</p>
                    <p class="text-xl font-black uppercase {{ $paymentMode == 'live' ? 'text-green-700' : 'text-yellow-700' }}">
                        ● {{ $paymentMode }}
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.settings.index') }}" class="mt-6 block text-center bg-gray-800 text-white py-2 rounded-lg hover:bg-gray-700 transition">
                تعديل الإعدادات
            </a>
        </div>

        <!-- 4. آخر 5 حجوزات -->
        <div class="bg-white p-6 rounded-xl shadow-sm overflow-hidden">
            <h3 class="font-bold mb-4 border-b pb-2">أحدث الحجوزات</h3>
            <div class="space-y-4">
                @foreach($latestBookings as $b)
                <div class="flex justify-between items-center text-sm border-b border-gray-50 pb-2">
                    <div>
                        <p class="font-bold">{{ $b->truck->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $b->customer->name }}</p>
                    </div>
                    <span class="text-indigo-600 font-bold">{{ $b->total_price }} ر.س</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 5. شاحنات معلقة -->
        <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="font-bold mb-4 border-b pb-2 text-yellow-600">شاحنات بانتظار المراجعة</h3>
            <div class="space-y-4">
                @foreach($pendingTrucks as $t)
                <a href="{{ route('admin.trucks.show', $t->id) }}" class="flex items-center space-x-3 space-x-reverse hover:bg-gray-50 p-1 rounded transition">
                    <img src="{{ $t->images->first() ? asset('storage/'.$t->images->first()->path) : 'https://via.placeholder.com/40' }}" class="w-10 h-10 rounded-lg object-cover">
                    <div class="overflow-hidden">
                        <p class="font-bold text-sm truncate">{{ $t->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $t->user->name }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- 6. أهم المستخدمين (الأكثر دفعاً) -->
        <div class="bg-white p-6 rounded-xl shadow-sm">
            <h3 class="font-bold mb-4 border-b pb-2 text-green-600">كبار العملاء</h3>
            <div class="space-y-4">
                @foreach($topCustomers as $c)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <img src="{{ $c->profile_photo_url }}" class="w-8 h-8 rounded-full">
                        <p class="text-sm font-medium">{{ $c->name }}</p>
                    </div>
                    <p class="text-xs font-bold text-gray-600">{{ number_format($c->bookings_as_customer_sum_total_price, 0) }} ر.س</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 7. آخر المستخدمين الجدد -->
        <div class="bg-white p-6 rounded-xl shadow-sm lg:col-span-3">
             <h3 class="font-bold mb-4">آخر المنضمين للمنصة</h3>
             <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @foreach($latestUsers as $u)
                <div class="text-center p-4 border rounded-xl hover:shadow-md transition">
                    <img src="{{ $u->profile_photo_url }}" class="w-12 h-12 rounded-full mx-auto mb-2">
                    <p class="text-xs font-bold truncate">{{ $u->name }}</p>
                    <p class="text-[10px] text-gray-400 capitalize">{{ $u->account_type }}</p>
                </div>
                @endforeach
             </div>
        </div>

    </div>

    <!-- Scripts للرسم البياني -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('bookingsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [{
                    label: 'عدد الحجوزات',
                    data: {!! json_encode($chartData['data']) !!},
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
</x-admin.layout>