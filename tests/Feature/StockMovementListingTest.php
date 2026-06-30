<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockMovementListingTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    private function createProduct(string $reference): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Catégorie Stock Movement Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'Category_ID' => $categoryId,
            'code' => 'P-' . $reference,
            'Referonce' => $reference,
            'Designation' => 'Produit ' . $reference,
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_stock_movements_page_shows_active_facture_movement(): void
    {
        $admin = $this->adminUser();

        $productId = $this->createProduct('REF-MOV-FAC-001');

        DB::table('factures')->insert([
            'code_facture' => 'FAC-MOV-001',
            'client_name' => 'Client Movement Test',
            'total' => 450,
            'paid_amount' => 0,
            'remaining_amount' => 450,
            'status' => 'non payée',
            'date_facture' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_movements')->insert([
            'product_id' => $productId,
            'type' => 'sortie',
            'quantity' => 3,
            'source' => 'facture',
            'reference' => 'FAC-MOV-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('stock.movements'));

        $response->assertStatus(200);
        $response->assertSee('FAC-MOV-001');
        $response->assertSee('facture');
    }

    public function test_stock_movements_page_shows_received_purchase_movement(): void
    {
        $admin = $this->adminUser();

        $productId = $this->createProduct('REF-MOV-PUR-001');

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Fournisseur Movement Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            'purchase_code' => 'PUR-MOV-001',
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'total' => 500,
            'status' => 'reçu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_movements')->insert([
            'product_id' => $productId,
            'type' => 'entree',
            'quantity' => 5,
            'source' => 'achat',
            'reference' => 'PUR-MOV-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('stock.movements'));

        $response->assertStatus(200);
        $response->assertSee('PUR-MOV-001');
        $response->assertSee('achat');
    }

    public function test_stock_movements_source_filter_shows_only_matching_source(): void
    {
        $admin = $this->adminUser();

        $productId = $this->createProduct('REF-MOV-FILTER-001');

        DB::table('factures')->insert([
            'code_facture' => 'FAC-MOV-FILTER-001',
            'client_name' => 'Client Movement Filter Test',
            'total' => 300,
            'paid_amount' => 0,
            'remaining_amount' => 300,
            'status' => 'non payée',
            'date_facture' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Fournisseur Movement Filter Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            'purchase_code' => 'PUR-MOV-FILTER-001',
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'total' => 200,
            'status' => 'reçu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_movements')->insert([
            [
                'product_id' => $productId,
                'type' => 'sortie',
                'quantity' => 2,
                'source' => 'facture',
                'reference' => 'FAC-MOV-FILTER-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => $productId,
                'type' => 'entree',
                'quantity' => 4,
                'source' => 'achat',
                'reference' => 'PUR-MOV-FILTER-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('stock.movements', ['source' => 'facture']));

        $response->assertStatus(200);
        $response->assertSee('FAC-MOV-FILTER-001');
        $response->assertDontSee('PUR-MOV-FILTER-001');
    }
}