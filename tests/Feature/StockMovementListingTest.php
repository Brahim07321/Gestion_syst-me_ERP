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

    private function createProduct(string $reference, int $companyId): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie ' . $reference,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return DB::table('products')->insertGetId([
            'company_id' => $companyId,
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
        $companyId = $admin->company_id;        

        $productId = $this->createProduct('REF-MOV-FAC-001', $companyId);

        DB::table('factures')->insert([
            'company_id' => $companyId,
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
            'company_id' => $companyId,

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
        $companyId = $admin->company_id;
        $productId = $this->createProduct('REF-MOV-PUR-001', $companyId);

        $supplierId = DB::table('suppliers')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Fournisseur Movement Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            'company_id' => $companyId,

            'purchase_code' => 'PUR-MOV-001',
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'total' => 500,
            'status' => 'reçu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('stock_movements')->insert([
            'company_id' => $companyId,

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
        $companyId = $admin->company_id;
        $productId = $this->createProduct('REF-MOV-FILTER-001', $companyId);

        DB::table('factures')->insert([
            'company_id' => $companyId,
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
            'company_id' => $companyId,

            'name' => 'Fournisseur Movement Filter Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('purchases')->insert([
            'company_id' => $companyId,

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
                'company_id' => $companyId,

                'product_id' => $productId,
                'type' => 'sortie',
                'quantity' => 2,
                'source' => 'facture',
                'reference' => 'FAC-MOV-FILTER-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,

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

    public function test_stock_movements_search_filter_by_reference(): void
{
    $admin = $this->adminUser();
    $companyId = $admin->company_id;

    $productId = $this->createProduct('REF-SEARCH-KEEP-001', $companyId);
    $otherProductId = $this->createProduct('REF-SEARCH-HIDE-001', $companyId);

    DB::table('factures')->insert([
        [
            'company_id' => $companyId,

            'code_facture' => 'FAC-SEARCH-KEEP-001',
            'client_name' => 'Client Search Keep',
            'total' => 300,
            'paid_amount' => 0,
            'remaining_amount' => 300,
            'status' => 'non payée',
            'date_facture' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'company_id' => $companyId,
            'code_facture' => 'FAC-SEARCH-HIDE-001',
            'client_name' => 'Client Search Hide',
            'total' => 200,
            'paid_amount' => 0,
            'remaining_amount' => 200,
            'status' => 'non payée',
            'date_facture' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('stock_movements')->insert([
        [
            'company_id' => $companyId,

            'product_id' => $productId,
            'type' => 'sortie',
            'quantity' => 2,
            'source' => 'facture',
            'reference' => 'FAC-SEARCH-KEEP-001',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'company_id' => $companyId,

            'product_id' => $otherProductId,
            'type' => 'sortie',
            'quantity' => 1,
            'source' => 'facture',
            'reference' => 'FAC-SEARCH-HIDE-001',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('stock.movements', ['search' => 'FAC-SEARCH-KEEP-001']));

    $response->assertStatus(200);
    $response->assertSee('FAC-SEARCH-KEEP-001');
    $response->assertDontSee('FAC-SEARCH-HIDE-001');
}

public function test_stock_movements_type_filter_shows_only_sortie(): void
{
    $admin = $this->adminUser();
    $companyId = $admin->company_id;

    $productId = $this->createProduct('REF-TYPE-FILTER-001', $companyId);

    DB::table('factures')->insert([
        'company_id' => $companyId,

        'code_facture' => 'FAC-TYPE-FILTER-001',
        'client_name' => 'Client Type Filter',
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
        'company_id' => $companyId,

        'name' => 'Fournisseur Type Filter',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('purchases')->insert([
        'company_id' => $companyId,

        'purchase_code' => 'PUR-TYPE-FILTER-001',
        'supplier_id' => $supplierId,
        'purchase_date' => now()->toDateString(),
        'total' => 500,
        'status' => 'reçu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('stock_movements')->insert([
        [
            'company_id' => $companyId,

            'product_id' => $productId,
            'type' => 'sortie',
            'quantity' => 3,
            'source' => 'facture',
            'reference' => 'FAC-TYPE-FILTER-001',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'company_id' => $companyId,

            'product_id' => $productId,
            'type' => 'entree',
            'quantity' => 5,
            'source' => 'achat',
            'reference' => 'PUR-TYPE-FILTER-001',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('stock.movements', ['type' => 'sortie']));

    $response->assertStatus(200);
    $response->assertSee('FAC-TYPE-FILTER-001');
    $response->assertDontSee('PUR-TYPE-FILTER-001');
}

public function test_stock_movements_date_filter_shows_only_selected_period(): void
{
    $admin = $this->adminUser();
    $companyId = $admin->company_id;

    $productId = $this->createProduct('REF-DATE-FILTER-001', $companyId);

    DB::table('factures')->insert([
        [
            'company_id' => $companyId,

            'code_facture' => 'FAC-DATE-IN-001',
            'client_name' => 'Client Date In',
            'total' => 300,
            'paid_amount' => 0,
            'remaining_amount' => 300,
            'status' => 'non payée',
            'date_facture' => '2026-06-15',
            'due_date' => '2026-06-30',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'company_id' => $companyId,
            'code_facture' => 'FAC-DATE-OUT-001',
            'client_name' => 'Client Date Out',
            'total' => 200,
            'paid_amount' => 0,
            'remaining_amount' => 200,
            'status' => 'non payée',
            'date_facture' => '2026-07-15',
            'due_date' => '2026-07-30',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('stock_movements')->insert([
        [
            'company_id' => $companyId,

            'product_id' => $productId,
            'type' => 'sortie',
            'quantity' => 2,
            'source' => 'facture',
            'reference' => 'FAC-DATE-IN-001',
            'created_at' => '2026-06-15 10:00:00',
            'updated_at' => '2026-06-15 10:00:00',
        ],
        [
            'company_id' => $companyId,

            'product_id' => $productId,
            'type' => 'sortie',
            'quantity' => 1,
            'source' => 'facture',
            'reference' => 'FAC-DATE-OUT-001',
            'created_at' => '2026-07-15 10:00:00',
            'updated_at' => '2026-07-15 10:00:00',
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('stock.movements', [
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30',
        ]));

    $response->assertStatus(200);
    $response->assertSee('FAC-DATE-IN-001');
    $response->assertDontSee('FAC-DATE-OUT-001');
}
}