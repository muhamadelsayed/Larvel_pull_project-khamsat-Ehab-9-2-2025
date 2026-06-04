<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Policy;
use Illuminate\Support\Facades\Storage;



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


public function updateLandingPage(Request $request) {
    $data = $request->except('_token');
    
    foreach ($data as $key => $value) {
        if ($request->hasFile($key)) {
            // حذف القديم
            $old = Setting::where('key', $key)->first()->value ?? null;
            if ($old) { Storage::disk('public')->delete($old); } // تم تعديل استدعاء السيرفس ليتوافق مع الـ Imports
            
            // تحديد مجلد الحفظ
            $folder = 'landing';
            // تم إضافة شرط لرفع ملف الآيفون المباشر داخل مجلد الـ apps
            if ($key === 'android_app_file' || $key === 'ios_app_file') {
                $folder = 'apps';
            }
            if ($key === 'app_logo') {
                $folder = 'brand';
            }
            
            $value = $request->file($key)->store($folder, 'public');
        }
        
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
    
    return back()->with('success', 'تم تحديث البيانات والملفات بنجاح');
}

}
