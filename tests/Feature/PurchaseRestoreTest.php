<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseRestoreTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_restore_deleted_purchase_adds_stock_and_marks_as_received(): void
    {
        $admin = $this->adminUser();

        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Catégorie Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Fournisseur Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'Category_ID' => $categoryId,
            'code' => 'P001',
            'Referonce' => 'REF-001',
            'Designation' => 'Produit Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $purchaseId = DB::table('purchases')->insertGetId([
            'purchase_code' => 'ACH-TEST-001',
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'total' => 500,
            'status' => 'annulé',
            'deleted_at' => now(),
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
            ->post(route('documents.archives.purchase.restore', $purchaseId));

        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'Quantite' => 5,
        ]);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'status' => 'reçu',
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productId,
            'type' => 'entree',
            'quantity' => 5,
            'source' => 'restauration achat',
            'reference' => 'ACH-TEST-001',
        ]);
    }
}