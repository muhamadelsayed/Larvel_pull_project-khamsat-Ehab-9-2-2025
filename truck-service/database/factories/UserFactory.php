<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            
            // تم استبدال 'email' بـ 'phone'
            'phone' => fake()->unique()->e164PhoneNumber(), // e164PhoneNumber يضمن تنسيقًا دوليًا فريدًا
            
            'password' => static::$password ??= Hash::make('password'),
            
            // تحديد قيمة افتراضية لنوع الحساب
            // يمكننا تغييره لاحقًا عند استخدام الـ Factory إذا أردنا
            'account_type' => fake()->randomElement(['client', 'truck_owner']),
            
            'remember_token' => Str::random(10),
        ];
    }
}