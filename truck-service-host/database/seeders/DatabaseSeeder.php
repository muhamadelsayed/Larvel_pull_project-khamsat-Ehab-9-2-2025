<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. استدعاء الـ Seeder الخاص بالأدوار والصلاحيات أولاً.
        // هذا يضمن أن الأدوار والصلاحيات موجودة قبل محاولة إسنادها لأي مستخدم.
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // 2. (اختياري) يمكنك الآن إنشاء بعض المستخدمين الوهميين باستخدام الـ Factory الصحيح
        // \App\Models\User::factory(10)->create();
        
        // 3. نقوم بإنشاء مستخدمي الأدمن والمدير من خلال Tinker أو نتركهم ليتم إنشاؤهم
        // يدويًا من لوحة التحكم لاحقًا للحفاظ على نظافة الـ Seeders.
        // سنكتفي حاليًا بإنشاء الأدوار فقط.
    }
}