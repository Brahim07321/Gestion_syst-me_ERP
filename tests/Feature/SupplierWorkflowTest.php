<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_create_supplier_stores_supplier(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->post(route('suppliers.store'), [
                'name' => 'Fournisseur Create Test',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Fournisseur Create Test',
        ]);
    }

    public function test_suppliers_page_shows_supplier(): void
    {
        $admin = $this->adminUser();

        DB::table('suppliers')->insert([
            'name' => 'Fournisseur Page Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('Fournisseur Page Test');
    }

    public function test_delete_supplier_removes_supplier(): void
    {
        $admin = $this->adminUser();

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Fournisseur Delete Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('suppliers.destroy', $supplierId));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplierId,
        ]);
    }
}