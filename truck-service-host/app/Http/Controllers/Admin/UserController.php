<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        $roles = Role::pluck('name');
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function updateRole(Request $request, User $user)
    {
        // --- إعادة ترتيب الشروط الأمنية ---

        // القاعدة رقم 1: لا يمكن لأي مستخدم تعديل بياناته الخاصة من هذا الفورم.
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تغيير دور حسابك الخاص.');
        }

        // القاعدة رقم 2: لا يمكن لأي مستخدم تعديل بيانات أدمن آخر. (الإصلاح الرئيسي)
        if ($user->hasRole('admin')) {
            return back()->with('error', 'لا يمكن تغيير دور مستخدم من نوع أدمن.');
        }
        
        // القاعدة رقم 3: لا يمكن تغيير دور "صاحب الشاحنة".
        if ($user->account_type === 'truck_owner') {
            return back()->with('error', 'لا يمكن تغيير دور "صاحب الشاحنة".');
        }

        // --- إذا مرت كل القواعد ---
        $validated = $request->validate([
            'role' => ['required', Rule::in(Role::pluck('name'))]
        ]);
        $newRole = $validated['role'];

        DB::transaction(function () use ($user, $newRole) {
            $user->update(['account_type' => $newRole]);
            $user->syncRoles($newRole);
        });

        return back()->with('success', "تم تحديث دور المستخدم '{$user->name}' بنجاح.");
    }

    public function destroy(User $user)
    {
        // القاعدة رقم 1: لا يمكن لأي مستخدم حذف نفسه.
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        // القاعدة رقم 2: لا يمكن لأي مستخدم حذف أدمن آخر. (الإصلاح الرئيسي)
        if ($user->hasRole('admin')) {
            return back()->with('error', 'لا يمكن حذف مستخدم من نوع أدمن.');
        }
        
        $user->delete();
        return back()->with('success', 'تم حذف المستخدم بنجاح.');
    }
    public function show(User $user)
{
    // تحميل الشاحنات مع علاقاتها لتحسين الأداء
    $user->load(['trucks.category', 'trucks.subCategory', 'trucks.images']);
    return view('admin.users.show', compact('user'));
}
}