<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إعادة تعيين الأدوار والصلاحيات المخبأة (مهم جدًا)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // قائمة الصلاحيات التي يجب أن تكون موجودة في النظام
        $permissions = [
            'view users',
            'promote users',
            'delete users',
            'manage categories',
            'manage trucks',
        ];

        // إنشاء الصلاحيات (سيتم إنشاء الجديد فقط)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // --- إنشاء الأدوار وتعيين الصلاحيات ---

        // دور "المدير"
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions(['view users', 'manage categories', 'manage trucks']);

        // دور "الأدمن" - يحصل على جميع الصلاحيات
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());
        
        // (اختياري) إنشاء دور "العميل" بدون صلاحيات خاصة بلوحة التحكم
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        
        // (اختياري) إنشاء دور "صاحب الشاحنة" بدون صلاحيات خاصة بلوحة التحكم
        Role::firstOrCreate(['name' => 'truck_owner', 'guard_name' => 'web']);
    }
}