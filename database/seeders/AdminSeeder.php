<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'brahim@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('123456'),
                'role' => 'admin',
            ]
        );
    }
}