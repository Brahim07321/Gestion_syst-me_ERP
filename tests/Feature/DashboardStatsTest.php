<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_dashboard_opens_with_products_data(): void
    {
        $admin = $this->adminUser();

        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Dashboard Category Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'Category_ID' => $categoryId,
            'code' => 'DASH-PROD-001',
            'Referonce' => 'DASH-REF-001',
            'Designation' => 'Dashboard Product Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Inventory System');
    }

    public function test_dashboard_opens_with_factures_data(): void
    {
        $admin = $this->adminUser();

        DB::table('factures')->insert([
            'code_facture' => 'DASH-FAC-001',
            'client_name' => 'Dashboard Client Test',
            'total' => 500,
            'paid_amount' => 200,
            'remaining_amount' => 300,
            'status' => 'partiellement payée',
            'date_facture' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Inventory System');
    }

    public function test_dashboard_opens_with_purchases_and_expenses_data(): void
    {
        $admin = $this->adminUser();

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Dashboard Supplier Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            'purchase_code' => 'DASH-PUR-001',
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'total' => 400,
            'status' => 'reçu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expenses')->insert([
            'name' => 'Dashboard Expense Test',
            'amount' => 100,
            'expense_date' => now()->toDateString(),
            'description' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Inventory System');
    }
}