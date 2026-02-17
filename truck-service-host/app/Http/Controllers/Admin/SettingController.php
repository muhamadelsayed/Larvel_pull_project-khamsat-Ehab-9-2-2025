<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
   public function index() {
    $mode = Setting::where('key', 'tap_payment_mode')->first()->value ?? 'test';
    return view('admin.settings.index', compact('mode'));
}

public function update(Request $request) {
    $request->validate(['tap_payment_mode' => 'required|in:test,live']);
    Setting::updateOrCreate(
        ['key' => 'tap_payment_mode'],
        ['value' => $request->tap_payment_mode]
    );
    return back()->with('success', 'تم تحديث وضع الدفع بنجاح');
}
}
