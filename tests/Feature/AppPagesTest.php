<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppPagesTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_dashboard_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_purchases_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/purchases');

        $response->assertStatus(200);
    }

    public function test_factures_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/archife');

        $response->assertStatus(200);
    }

    public function test_stock_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/stock');

        $response->assertStatus(200);
    }

    public function test_documents_archives_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/documents-archives');

        $response->assertStatus(200);
    }
}