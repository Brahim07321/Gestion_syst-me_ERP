<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_create_purchase_en_attente_does_not_add_stock(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplierId = DB::table('suppliers')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Fournisseur Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'company_id' => $companyId,
            'Category_ID' => $categoryId,
            'code' => 'P-ATT-001',
            'Referonce' => 'REF-ATT-001',
            'Designation' => 'Produit En Attente Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('purchases.store'), [
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'status' => 'en attente',
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 5,
                    'buy_price' => 100,
                ],
            ],
        ]);

        $response->assertStatus(302);

        // Stock خاصو يبقى 10 حيث achat en attente مازال ما توصلش
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'Quantite' => 10,
        ]);

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplierId,
            'company_id' => $companyId,
            'status' => 'en attente',
            'total' => 500,
        ]);

        $purchaseId = DB::table('purchases')->where('supplier_id', $supplierId)->value('id');

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchaseId,
            'product_id' => $productId,
            'quantity' => 5,
            'buy_price' => 100,
            'line_total' => 500,
        ]);
    }
    public function test_mark_purchase_as_received_adds_stock(): void
{
    $admin = $this->adminUser();
    $companyId = $admin->company_id;

    $categoryId = DB::table('categories')->insertGetId([
        'company_id' => $companyId,
        'Category' => 'Catégorie Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $supplierId = DB::table('suppliers')->insertGetId([
        'company_id' => $companyId,
        'name' => 'Fournisseur Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'company_id' => $companyId,
        'Category_ID' => $categoryId,
        'code' => 'P-REC-001',
        'Referonce' => 'REF-REC-001',
        'Designation' => 'Produit Reçu Test',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $purchaseId = DB::table('purchases')->insertGetId([
        'company_id' => $companyId,
        'purchase_code' => 'ACH-REC-001',
        'supplier_id' => $supplierId,
        'purchase_date' => now()->toDateString(),
        'total' => 500,
        'status' => 'en attente',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('purchase_items')->insert([
        'purchase_id' => $purchaseId,
        'product_id' => $productId,
        'quantity' => 5,
        'buy_price' => 100,
        'line_total' => 500,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('purchases.status', $purchaseId));

    $response->assertStatus(302);

    // Stock كان 10، ملي achat ولى reçu خاصو يزيد 5
    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'Quantite' => 15,
    ]);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchaseId,
        'company_id' => $companyId,
        'status' => 'reçu',
    ]);
}
public function test_deleted_purchase_appears_in_documents_archives(): void
{
    $admin = $this->adminUser();
    $companyId = $admin->company_id;

    $supplierId = DB::table('suppliers')->insertGetId([
        'company_id' => $companyId,
        'name' => 'Fournisseur Archive Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $purchaseId = DB::table('purchases')->insertGetId([
        'company_id' => $companyId,
        'purchase_code' => 'ACH-ARCHIVE-001',
        'supplier_id' => $supplierId,
        'purchase_date' => now()->toDateString(),
        'total' => 500,
        'status' => 'annulé',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('purchases.destroy', $purchaseId));

    $response->assertStatus(302);

    // خاص bon d'achat يكون supprimé soft delete
    $this->assertSoftDeleted('purchases', [
        'id' => $purchaseId,
    ]);

    // خاص يبان ف documents archives
    $archiveResponse = $this->actingAs($admin)
        ->get(route('documents.archives'));

    $archiveResponse->assertStatus(200);
    $archiveResponse->assertSee('ACH-ARCHIVE-001');
    $archiveResponse->assertSee('Fournisseur Archive Test');
}
public function test_cancel_purchase_en_attente_marks_as_cancelled_without_changing_stock(): void
{
    $admin = $this->adminUser();
    $companyId = $admin->company_id;

    $categoryId = DB::table('categories')->insertGetId([
        'company_id' => $companyId,
        'Category' => 'Catégorie Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $supplierId = DB::table('suppliers')->insertGetId([
        'company_id' => $companyId,
        'name' => 'Fournisseur Cancel Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'company_id' => $companyId,
        'Category_ID' => $categoryId,
        'code' => 'P-CANCEL-001',
        'Referonce' => 'REF-CANCEL-001',
        'Designation' => 'Produit Cancel Test',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $purchaseId = DB::table('purchases')->insertGetId([
        'company_id' => $companyId,
        'purchase_code' => 'ACH-CANCEL-001',
        'supplier_id' => $supplierId,
        'purchase_date' => now()->toDateString(),
        'total' => 500,
        'status' => 'en attente',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('purchase_items')->insert([
        'purchase_id' => $purchaseId,
        'product_id' => $productId,
        'quantity' => 5,
        'buy_price' => 100,
        'line_total' => 500,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('purchases.cancel', $purchaseId));

    $response->assertStatus(302);

    // Status خاصو يولي annulé
    $this->assertDatabaseHas('purchases', [
        'id' => $purchaseId,
        'company_id' => $companyId,
        'status' => 'annulé',
    ]);

    // Stock خاصو يبقى 10 حيث bon كان en attente وما تزادش stock من قبل
    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'Quantite' => 10,
    ]);
}
}