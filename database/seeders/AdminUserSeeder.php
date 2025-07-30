<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@myfuture.com',
            'password' => bcrypt('admin1234'),
            'role' => 'admin',
            'phone' => '0102030405',
            'address' => 'Siège MyFuture',
            'date_of_birth' => '1990-01-01',
            'email_verified_at' => now(),
        ]);
    }
}
