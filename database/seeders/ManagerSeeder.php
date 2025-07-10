<?php

namespace Database\Seeders;

use App\Models\Manager;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        // إنشاء مستخدم جديد من نوع "manager"
        $user = User::create([
            'username' => 'manager_user',
            'password' => Hash::make('12345678'), // كلمة المرور
            'email' => 'manager@example.com',
            'phone_number' => '0912345678',
            'address' => 'Damascus',
            'registration_date' => now(),
            'user_type' => 'manager',
        ]);

        // إنشاء سجل المدير المرتبط بالمستخدم
        Manager::create([
            'user_id' => $user->id,
            'department' => 'Human Resources',
            'position' => 'HR Manager',
        ]);
    }

}
