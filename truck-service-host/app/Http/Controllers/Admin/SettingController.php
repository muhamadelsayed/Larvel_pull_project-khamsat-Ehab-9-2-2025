<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Policy;


class SettingController extends Controller
{
   public function index() {
    $mode = Setting::where('key', 'tap_payment_mode')->first()->value ?? 'test';
    $policies = Policy::orderBy('sort_order')->get(); // جلب جميع البنود
    return view('admin.settings.index', compact('mode', 'policies'));
}

public function update(Request $request) {
    $request->validate(['tap_payment_mode' => 'required|in:test,live']);
    Setting::updateOrCreate(
        ['key' => 'tap_payment_mode'],
        ['value' => $request->tap_payment_mode]
    );
    return back()->with('success', 'تم تحديث وضع الدفع بنجاح');
}

// إضافة بند جديد
public function storePolicy(Request $request) {
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    Policy::create($validated);
    return back()->with('success', 'تم إضافة البند بنجاح');
}

// حذف بند
public function destroyPolicy(Policy $policy) {
    $policy->delete();
    return back()->with('success', 'تم حذف البند بنجاح');
}

// عرض الصفحة العامة
public function publicPolicies() {
    $policies = Policy::orderBy('sort_order')->get();
    return view('policies', compact('policies'));
}
// تحديث بند موجود
public function updatePolicy(Request $request, Policy $policy) {
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    $policy->update($validated);
    
    return back()->with('success', 'تم تحديث البند بنجاح');
}
}
