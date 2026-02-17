<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Truck;
use App\Models\Booking;
use App\Models\Setting;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // دالة مساعدة لتحديد شرط "الدفع الحقيقي"
        // نبحث عن الحجوزات التي تحتوي على معرف دفع ولا يبدأ بـ chg_test
        $realPaymentQuery = function($query) {
            $query->whereIn('status', ['confirmed', 'completed'])
                  ->whereNotNull('tap_charge_id')
                  ->where('tap_charge_id', 'NOT LIKE', 'chg_test%');
        };

        // 1. إحصائيات سريعة (الإيرادات الحقيقية فقط)
        $stats = [
            'total_revenue' => Booking::where($realPaymentQuery)->sum('total_price'),
            'total_bookings' => Booking::count(),
            'pending_trucks_count' => Truck::where('status', 'pending')->count(),
            'active_users' => User::count(),
        ];

        // 2. حالة الدفع (من جدول الإعدادات)
        $paymentMode = Setting::where('key', 'tap_payment_mode')->first()->value ?? 'test';

        // 3. آخر 5 عمليات حجز (بغض النظر عن الدفع)
        $latestBookings = Booking::with(['truck', 'customer'])->latest()->take(5)->get();

        // 4. طلبات الشاحنات المعلقة
        $pendingTrucks = Truck::with('user')->where('status', 'pending')->latest()->take(5)->get();

        // 5. آخر 5 مستخدمين سجلوا
        $latestUsers = User::latest()->take(5)->get();

        // 6. كبار العملاء (الذين دفعوا مبالغ حقيقية فقط)
        $topCustomers = User::withSum(['bookingsAsCustomer' => function($query) use ($realPaymentQuery) {
            $realPaymentQuery($query);
        }], 'total_price')
        ->orderByDesc('bookings_as_customer_sum_total_price')
        ->take(5)
        ->get()
        ->filter(function($user) {
            // استبعاد من لم يدفع مبالغ حقيقية إطلاقاً
            return $user->bookings_as_customer_sum_total_price > 0;
        });

        // 7. بيانات الرسم البياني
        $chartData = $this->getChartData();

        return view('admin.dashboard', compact(
            'stats', 'paymentMode', 'latestBookings', 
            'pendingTrucks', 'latestUsers', 'topCustomers', 'chartData'
        ));
    }

    private function getChartData()
    {
        $days = [];
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('M d');
            // هنا نحسب عدد الحجوزات الكلي (للإشارة إلى حركة الموقع)
            $counts[] = Booking::whereDate('created_at', $date->toDateString())->count();
        }
        return ['labels' => $days, 'data' => $counts];
    }
}