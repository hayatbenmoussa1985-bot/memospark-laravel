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
            ['email' => 'admin@memospark.net'],
            [
                'name' => 'Hammadi',
                'password' => Hash::make('changeme123'),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
    }
}
