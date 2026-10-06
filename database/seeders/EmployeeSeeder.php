<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('employees')->updateOrInsert(
            ['employee_code' => 'EMP0001'],
            [
                'name' => 'ผู้ดูแลระบบ',
                'email' => 'staff@banklite.test',
                'phone' => '02-XXX-4521',
                'position' => 'ผู้ดูแลระบบ',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make(env('BANKLITE_DEMO_PASSWORD', 'BankLite@123')),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
