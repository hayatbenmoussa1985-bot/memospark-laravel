<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'wanish.hammouda@gmail.com'],
            [
                'name' => 'Hammadi',
                'password' => Hash::make('Aa0610637461'),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
    }
}
