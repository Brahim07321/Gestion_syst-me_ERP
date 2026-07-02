<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'default-company'],
            [
                'name' => 'Default Company',
                'plan' => 'free',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'brahim@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'company_id' => $company->id,
            ]
        );

        User::whereNull('company_id')->update([
            'company_id' => $company->id,
        ]);
    }
}