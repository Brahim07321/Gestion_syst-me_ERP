<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArchiveStockLogicTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    private function createProductWithQuantity(int $quantity, int $companyId): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie Archive Stock Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('products')->insertGetId([
            'company_id' => $companyId,
            'Category_ID' => $categoryId,
            'code' => 'ARCH-STOCK-001',
            'Referonce' => 'ARCH-STOCK-REF-001',
            'Designation' => 'Produit Archive Stock Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_delete_facture_restores_stock_and_restore_facture_decreases_stock_again(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        // Stock initial après création facture déjà نقصناه: 10 - 3 = 7
        $productId = $this->createProductWithQuantity(7, $companyId);

        $factureId = DB::table('factures')->insertGetId([
            'company_id' => $companyId,
            'code_facture' => 'FAC-ARCH-STOCK-001',
            'client_name' => 'Client Archive Stock Test',
            'total' => 450,
            'paid_amount' => 0,
            'remaining_amount' => 450,
            'status' => 'non payée',
            'date_facture' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('facture_items')->insert([
            'facture_id' => $factureId,
            'referonce' => 'ARCH-STOCK-REF-001',
            'designation' => 'Produit Archive Stock Test',
            'price' => 150,
            'quantity' => 3,
            'line_total' => 450,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Delete facture خاصو يرجع 3 للstock: 7 + 3 = 10
        $deleteResponse = $this->actingAs($admin)
            ->delete(route('factures.destroy', $factureId));

        $deleteResponse->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'company_id' => $companyId,
            'Quantite' => 10,
        ]);

        // Restore facture خاصو ينقص 3 من stock: 10 - 3 = 7
        $restoreResponse = $this->actingAs($admin)
            ->post(route('documents.archives.facture.restore', $factureId));

        $restoreResponse->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'company_id' => $companyId,
            'Quantite' => 7,
        ]);
    }

    public function test_delete_received_purchase_decreases_stock_and_restore_purchase_adds_stock_again(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        // Stock initial فيه achat déjà تزاد: 10 + 5 = 15
        $productId = $this->createProductWithQuantity(15, $companyId);

        $supplierId = DB::table('suppliers')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Fournisseur Archive Stock Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $purchaseId = DB::table('purchases')->insertGetId([
            'company_id' => $companyId,
            'purchase_code' => 'PUR-ARCH-STOCK-001',
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'total' => 500,
            'status' => 'reçu',
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

        // Delete achat reçu خاصو ينقص 5 من stock: 15 - 5 = 10
        $deleteResponse = $this->actingAs($admin)
            ->delete(route('purchases.destroy', $purchaseId));

        $deleteResponse->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'company_id' => $companyId,
            'Quantite' => 10,
        ]);

        // Restore achat reçu خاصو يرجع يزيد 5 فالstock: 10 + 5 = 15
        $restoreResponse = $this->actingAs($admin)
            ->post(route('documents.archives.purchase.restore', $purchaseId));

        $restoreResponse->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'company_id' => $companyId,
            'Quantite' => 15,
        ]);
    }
}