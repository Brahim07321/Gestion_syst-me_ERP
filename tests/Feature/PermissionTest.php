<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    private function normalUser(): User
    {
        return User::factory()->create([
            'role' => 'user',
        ]);
    }

    public function test_normal_user_cannot_delete_purchase(): void
    {
        $user = $this->normalUser();

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Fournisseur Permission Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $purchaseId = DB::table('purchases')->insertGetId([
            'purchase_code' => 'ACH-PERM-001',
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'total' => 500,
            'status' => 'en attente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->delete(route('purchases.destroy', $purchaseId));

        // حسب middleware ديالك ممكن يرجع 302 أو 403
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 403])
        );

        // Bon d’achat ما خاصوش يتحذف
        $this->assertDatabaseHas('purchases', [
            'id' => $purchaseId,
            'purchase_code' => 'ACH-PERM-001',
            'deleted_at' => null,
        ]);
    }
    public function test_normal_user_cannot_delete_facture(): void
{
    $user = $this->normalUser();

    $factureId = DB::table('factures')->insertGetId([
        'code_facture' => 'FAC-PERM-001',
        'client_name' => 'Client Permission Test',
        'total' => 750,
        'date_facture' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'status' => 'non payée',
        'paid_amount' => 0,
        'remaining_amount' => 750,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->delete(route('factures.destroy', $factureId));

    // حسب middleware ديالك ممكن يرجع 302 أو 403
    $this->assertTrue(
        in_array($response->getStatusCode(), [302, 403])
    );

    // Facture ما خاصهاش تتحذف
    $this->assertDatabaseHas('factures', [
        'id' => $factureId,
        'code_facture' => 'FAC-PERM-001',
        'deleted_at' => null,
    ]);
}
public function test_normal_user_cannot_restore_purchase(): void
{
    $user = $this->normalUser();

    $supplierId = DB::table('suppliers')->insertGetId([
        'name' => 'Fournisseur Restore Permission Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $purchaseId = DB::table('purchases')->insertGetId([
        'purchase_code' => 'ACH-REST-PERM-001',
        'supplier_id' => $supplierId,
        'purchase_date' => now()->toDateString(),
        'total' => 500,
        'status' => 'annulé',
        'deleted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->post(route('documents.archives.purchase.restore', $purchaseId));

    $response->assertStatus(302);
    $response->assertSessionHas('error');

    // Bon d’achat خاصو يبقى supprimé وما يرجعش
    $this->assertSoftDeleted('purchases', [
        'id' => $purchaseId,
    ]);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchaseId,
        'status' => 'annulé',
    ]);
}
public function test_normal_user_cannot_restore_facture(): void
{
    $user = $this->normalUser();

    $factureId = DB::table('factures')->insertGetId([
        'code_facture' => 'FAC-REST-PERM-001',
        'client_name' => 'Client Restore Permission Test',
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

    $response = $this->actingAs($user)
        ->post(route('documents.archives.facture.restore', $factureId));

    $response->assertStatus(302);
    $response->assertSessionHas('error');

    // Facture خاصها تبقى supprimée وما ترجعش
    $this->assertSoftDeleted('factures', [
        'id' => $factureId,
    ]);

    $this->assertDatabaseHas('factures', [
        'id' => $factureId,
        'status' => 'annulée',
    ]);
}

}