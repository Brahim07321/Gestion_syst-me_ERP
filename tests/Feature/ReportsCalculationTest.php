<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportsCalculationTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_reports_calculates_sales_paid_and_remaining_for_selected_month(): void
    {
        $admin = $this->adminUser();
    
        DB::table('factures')->insert([
            'code_facture' => 'FAC-REPORT-001',
            'client_name' => 'Client Report Test',
            'total' => 1000,
            'paid_amount' => 400,
            'remaining_amount' => 600,
            'status' => 'partiellement payée',
            'date_facture' => '2026-06-15',
            'due_date' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        DB::table('factures')->insert([
            'code_facture' => 'FAC-REPORT-CANCELLED',
            'client_name' => 'Client Cancelled Report Test',
            'total' => 500,
            'paid_amount' => 0,
            'remaining_amount' => 0,
            'status' => 'annulée',
            'date_facture' => '2026-06-16',
            'due_date' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        DB::table('factures')->insert([
            'code_facture' => 'FAC-REPORT-DELETED',
            'client_name' => 'Client Deleted Report Test',
            'total' => 700,
            'paid_amount' => 0,
            'remaining_amount' => 700,
            'status' => 'non payée',
            'date_facture' => '2026-06-17',
            'due_date' => '2026-06-30',
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        $response = $this->actingAs($admin)
            ->get(route('reports', ['month' => '2026-06']));
    
        $response->assertStatus(200);
        $response->assertViewHas('totalSales', fn ($value) => (float) $value === 1000.0);
        $response->assertViewHas('totalPaid', fn ($value) => (float) $value === 400.0);
        $response->assertViewHas('totalRemaining', fn ($value) => (float) $value === 600.0);
    }
    public function test_reports_calculates_purchases_and_expenses_for_selected_month(): void
    {
        $admin = $this->adminUser();

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Fournisseur Report Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            [
                'purchase_code' => 'PUR-REPORT-001',
                'supplier_id' => $supplierId,
                'purchase_date' => '2026-06-10',
                'total' => 700,
                'status' => 'reçu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'purchase_code' => 'PUR-REPORT-OUT',
                'supplier_id' => $supplierId,
                'purchase_date' => '2026-07-10',
                'total' => 999,
                'status' => 'reçu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('expenses')->insert([
            [
                'name' => 'Dépense Report 1',
                'amount' => 100,
                'expense_date' => '2026-06-05',
                'description' => 'Test',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dépense Report 2',
                'amount' => 50,
                'expense_date' => '2026-06-20',
                'description' => 'Test',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dépense Report Out',
                'amount' => 999,
                'expense_date' => '2026-07-05',
                'description' => 'Test',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports', ['month' => '2026-06']));

        $response->assertStatus(200);
        $response->assertViewHas('totalPurchases', fn ($value) => (float) $value === 700.0);
        $response->assertViewHas('totalExpenses', fn ($value) => (float) $value === 150.0);
    }

    public function test_reports_calculates_net_profit(): void
    {
        $admin = $this->adminUser();

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Fournisseur Profit Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('factures')->insert([
            'code_facture' => 'FAC-REPORT-001',
            'client_name' => 'Client Report Test',
            'total' => 1000,
            'paid_amount' => 400,
            'remaining_amount' => 600,
            'status' => 'partiellement payée',
            'date_facture' => '2026-06-15',
            'due_date' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('factures')->insert([
            'code_facture' => 'FAC-REPORT-CANCELLED',
            'client_name' => 'Client Cancelled Report Test',
            'total' => 500,
            'paid_amount' => 0,
            'remaining_amount' => 0,
            'status' => 'annulée',
            'date_facture' => '2026-06-16',
            'due_date' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('factures')->insert([
            'code_facture' => 'FAC-REPORT-DELETED',
            'client_name' => 'Client Deleted Report Test',
            'total' => 700,
            'paid_amount' => 0,
            'remaining_amount' => 700,
            'status' => 'non payée',
            'date_facture' => '2026-06-17',
            'due_date' => '2026-06-30',
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            'purchase_code' => 'PUR-PROFIT-001',
            'supplier_id' => $supplierId,
            'purchase_date' => '2026-06-10',
            'total' => 400,
            'status' => 'reçu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expenses')->insert([
            'name' => 'Dépense Profit Test',
            'amount' => 100,
            'expense_date' => '2026-06-05',
            'description' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports', ['month' => '2026-06']));

        $response->assertStatus(200);
        $response->assertViewHas('netProfit', fn ($value) => (float) $value === 500.0);
    }
}