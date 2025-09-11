<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    // هذه الدالة كل وظيفتها هي عرض الواجهة التي ستقوم بكل العمل
    public function showRegistrationForm()
    {
        return view('admin.register');
    }
}