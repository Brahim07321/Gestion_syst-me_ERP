<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FactureRestoreTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_restore_cancelled_facture_decreases_stock_and_reactivates_facture(): void
    {
        $admin = $this->adminUser();

        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Catégorie Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'Category_ID' => $categoryId,
            'code' => 'P002',
            'Referonce' => 'REF-FAC-001',
            'Designation' => 'Produit Facture Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $factureId = DB::table('factures')->insertGetId([
            'code_facture' => 'FAC-TEST-001',
            'client_name' => 'Client Test',
            'total' => 750,
            'date_facture' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'annulée',
            'paid_amount' => 0,
            'remaining_amount' => 0,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('facture_items')->insert([
            'facture_id' => $factureId,
            'referonce' => 'REF-FAC-001',
            'designation' => 'Produit Facture Test',
            'price' => 150,
            'quantity' => 5,
            'line_total' => 750,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('documents.archives.facture.restore', $factureId));

        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'Quantite' => 5,
        ]);

        $this->assertDatabaseHas('factures', [
            'id' => $factureId,
            'status' => 'non payée',
            'paid_amount' => 0,
            'remaining_amount' => 750,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productId,
            'type' => 'sortie',
            'quantity' => 5,
            'source' => 'restauration facture',
            'reference' => 'FAC-TEST-001',
        ]);
    }
}