<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PagesAndExportsTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_products_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/product');

        $response->assertStatus(200);
    }

    public function test_customers_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/Customer');

        $response->assertStatus(200);
    }

    public function test_suppliers_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/suppliers');

        $response->assertStatus(200);
    }

    public function test_expenses_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/expenses');

        $response->assertStatus(200);
    }

    public function test_reports_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/reports');

        $response->assertStatus(200);
    }

    public function test_stock_movements_page_opens(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/stock-movements');

        $response->assertStatus(200);
    }
}