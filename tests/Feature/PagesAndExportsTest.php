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
    public function test_factures_excel_export_works(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->get(route('factures.export.excel'));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
}

public function test_factures_pdf_export_works(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->get(route('factures.export.pdf'));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
}

public function test_customers_excel_export_works(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->get(route('customers.export.excel'));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
}

public function test_customers_pdf_export_works(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->get(route('customers.export.pdf'));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
}
public function test_purchases_excel_export_works(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->get(route('purchases.export.excel'));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
}

public function test_purchases_pdf_export_works(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->get(route('purchases.export.pdf'));

    $response->assertStatus(200);
    $response->assertHeader('content-disposition');
}
}