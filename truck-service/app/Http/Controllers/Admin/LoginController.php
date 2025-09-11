<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();

            // تحقق إذا كان المستخدم لديه صلاحية الدخول للوحة
            if ($user->hasAnyRole(['admin', 'manager'])) {
                $request->session()->regenerate();
                return redirect()->intended('admin/dashboard');
            }

            // إذا كان مستخدم عادي (عميل أو صاحب شاحنة)
            Auth::guard('web')->logout();
            return redirect()->route('access.denied');
        }

        return back()->withErrors([
            'phone' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}