<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FactureWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_create_facture_decreases_stock(): void
    {
        $admin = $this->adminUser();

        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Catégorie Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'Category_ID' => $categoryId,
            'code' => 'P-FAC-001',
            'Referonce' => 'REF-FAC-WF-001',
            'Designation' => 'Produit Facture Workflow',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('facture.store'), [
            'invoice_number' => 'FAC-WF-001',
            'invoice_date' => now()->toDateString(),
            'customer_search' => 'Client Test',
            'paid_amount' => 0,
            'items' => [
                [
                    'referonce' => 'REF-FAC-WF-001',
                    'designation' => 'Produit Facture Workflow',
                    'price' => 150,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(302);

        // Stock كان 10، facture باعت 5، خاصو يبقى 5
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'Quantite' => 5,
        ]);

        $this->assertDatabaseHas('factures', [
            'code_facture' => 'FAC-WF-001',
            'client_name' => 'Client Test',
            'total' => 750,
            'status' => 'non payée',
            'paid_amount' => 0,
            'remaining_amount' => 750,
        ]);

        $factureId = DB::table('factures')
            ->where('code_facture', 'FAC-WF-001')
            ->value('id');

        $this->assertDatabaseHas('facture_items', [
            'facture_id' => $factureId,
            'referonce' => 'REF-FAC-WF-001',
            'designation' => 'Produit Facture Workflow',
            'price' => 150,
            'quantity' => 5,
            'line_total' => 750,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productId,
            'type' => 'sortie',
            'quantity' => 5,
            'source' => 'facture',
            'reference' => 'FAC-WF-001',
        ]);
    }
    public function test_create_facture_fails_when_stock_is_insufficient(): void
{
    $admin = $this->adminUser();

    $categoryId = DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'Category_ID' => $categoryId,
        'code' => 'P-STOCK-001',
        'Referonce' => 'REF-STOCK-001',
        'Designation' => 'Produit Stock Insuffisant',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post(route('facture.store'), [
        'invoice_number' => 'FAC-STOCK-001',
        'invoice_date' => now()->toDateString(),
        'customer_search' => 'Client Test',
        'paid_amount' => 0,
        'items' => [
            [
                'referonce' => 'REF-STOCK-001',
                'designation' => 'Produit Stock Insuffisant',
                'price' => 150,
                'quantity' => 5,
            ],
        ],
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error');

    // Facture ما خاصهاش تتسجل
    $this->assertDatabaseMissing('factures', [
        'code_facture' => 'FAC-STOCK-001',
    ]);

    // Stock خاصو يبقى 3
    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'Quantite' => 3,
    ]);

    // ما خاصش يتسجل stock movement
    $this->assertDatabaseMissing('stock_movements', [
        'product_id' => $productId,
        'source' => 'facture',
        'reference' => 'FAC-STOCK-001',
    ]);
}
public function test_cancel_facture_restores_stock_and_marks_as_cancelled(): void
{
    $admin = $this->adminUser();

    $categoryId = DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'Category_ID' => $categoryId,
        'code' => 'P-CAN-FAC-001',
        'Referonce' => 'REF-CAN-FAC-001',
        'Designation' => 'Produit Annulation Facture',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $factureId = DB::table('factures')->insertGetId([
        'code_facture' => 'FAC-CANCEL-001',
        'client_name' => 'Client Cancel Test',
        'total' => 750,
        'date_facture' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'status' => 'non payée',
        'paid_amount' => 0,
        'remaining_amount' => 750,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('facture_items')->insert([
        'facture_id' => $factureId,
        'referonce' => 'REF-CAN-FAC-001',
        'designation' => 'Produit Annulation Facture',
        'price' => 150,
        'quantity' => 5,
        'line_total' => 750,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('factures.cancel', $factureId));

    $response->assertStatus(302);

    // Stock كان 5، ملي facture تلغات خاصو يرجع +5 = 10
    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'Quantite' => 10,
    ]);

    // Facture خاصها تولي annulée
    $this->assertDatabaseHas('factures', [
        'id' => $factureId,
        'status' => 'annulée',
        'paid_amount' => 0,
        'remaining_amount' => 0,
    ]);

    // Stock movement ديال annulation facture
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $productId,
        'type' => 'entree',
        'quantity' => 5,
        'source' => 'annulation facture',
        'reference' => 'FAC-CANCEL-001',
    ]);
}
public function test_deleted_cancelled_facture_appears_in_documents_archives(): void
{
    $admin = $this->adminUser();

    $factureId = DB::table('factures')->insertGetId([
        'code_facture' => 'FAC-ARCHIVE-001',
        'client_name' => 'Client Archive Test',
        'total' => 750,
        'date_facture' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'status' => 'annulée',
        'paid_amount' => 0,
        'remaining_amount' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('factures.destroy', $factureId));

    $response->assertStatus(302);

    // Facture خاصها تكون soft deleted
    $this->assertSoftDeleted('factures', [
        'id' => $factureId,
    ]);

    // خاصها تبان ف documents archives
    $archiveResponse = $this->actingAs($admin)
        ->get(route('documents.archives'));

    $archiveResponse->assertStatus(200);
    $archiveResponse->assertSee('FAC-ARCHIVE-001');
    $archiveResponse->assertSee('Client Archive Test');
}
public function test_restore_cancelled_deleted_facture_decreases_stock_and_reactivates_facture(): void
{
    $admin = $this->adminUser();

    $categoryId = DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = DB::table('products')->insertGetId([
        'Category_ID' => $categoryId,
        'code' => 'P-REST-FAC-001',
        'Referonce' => 'REF-REST-FAC-001',
        'Designation' => 'Produit Restore Facture',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $factureId = DB::table('factures')->insertGetId([
        'code_facture' => 'FAC-RESTORE-001',
        'client_name' => 'Client Restore Test',
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
        'referonce' => 'REF-REST-FAC-001',
        'designation' => 'Produit Restore Facture',
        'price' => 150,
        'quantity' => 5,
        'line_total' => 750,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('documents.archives.facture.restore', $factureId));

    $response->assertStatus(302);

    // Stock كان 10، restore facture خاصو ينقص 5
    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'Quantite' => 5,
    ]);

    // Facture خاصها ترجع active وما تبقاش annulée
    $this->assertDatabaseHas('factures', [
        'id' => $factureId,
        'status' => 'non payée',
        'paid_amount' => 0,
        'remaining_amount' => 750,
        'deleted_at' => null,
    ]);

    // Stock movement ديال restauration facture
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $productId,
        'type' => 'sortie',
        'quantity' => 5,
        'source' => 'restauration facture',
        'reference' => 'FAC-RESTORE-001',
    ]);
}
public function test_create_facture_with_partial_payment_marks_as_partially_paid(): void
{
    $admin = $this->adminUser();

    $categoryId = DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Paiement Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        'Category_ID' => $categoryId,
        'code' => 'P-PAY-001',
        'Referonce' => 'REF-PAY-001',
        'Designation' => 'Produit Paiement Test',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Paiement Test',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'paid_amount' => 100,
            'items' => [
                [
                    'referonce' => 'REF-PAY-001',
                    'designation' => 'Produit Paiement Test',
                    'price' => 150,
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('factures', [
        'client_name' => 'Client Paiement Test',
        'total' => 300,
        'paid_amount' => 100,
        'remaining_amount' => 200,
        'status' => 'partiellement payée',
    ]);

    $this->assertDatabaseHas('payments', [
        'amount' => 100,
        'note' => 'Paiement initial',
    ]);
}
public function test_create_facture_with_full_payment_marks_as_paid(): void
{
    $admin = $this->adminUser();

    $categoryId = DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Paiement Total Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('products')->insert([
        'Category_ID' => $categoryId,
        'code' => 'P-FULL-PAY-001',
        'Referonce' => 'REF-FULL-PAY-001',
        'Designation' => 'Produit Paiement Total Test',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Paiement Total Test',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'paid_amount' => 300,
            'items' => [
                [
                    'referonce' => 'REF-FULL-PAY-001',
                    'designation' => 'Produit Paiement Total Test',
                    'price' => 150,
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('factures', [
        'client_name' => 'Client Paiement Total Test',
        'total' => 300,
        'paid_amount' => 300,
        'remaining_amount' => 0,
        'status' => 'payée',
    ]);

    $this->assertDatabaseHas('payments', [
        'amount' => 300,
        'note' => 'Paiement initial',
    ]);
}
}