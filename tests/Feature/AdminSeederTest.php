<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_created_by_seeder(): void
    {
        $this->seed();

        $admin = User::where('role', 'admin')->first();

        $this->assertNotNull($admin);
    }
}