<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_create_product_stores_product(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;
    
        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie Product Create Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        $response = $this->actingAs($admin)
            ->post(route('product.store'), [
                'Category_ID' => $categoryId,
                'code' => 'P-CREATE-001',
                'Referonce' => 'REF-PROD-CREATE-001',
                'Designation' => 'Produit Create Test',
                'prace_bay' => 100,
                'prace_sell' => 150,
                'Quantite' => 10,
            ]);
    
        $response->assertStatus(302);
    
        $this->assertDatabaseHas('products', [
            'company_id' => $companyId,
            'Category_ID' => $categoryId,
            'code' => 'P-CREATE-001',
            'Referonce' => 'REF-PROD-CREATE-001',
            'Designation' => 'Produit Create Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
        ]);
    }

    public function test_update_product_changes_product_data(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie Product Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'company_id' => $companyId,
            'Category_ID' => $categoryId,
            'code' => 'P-UPDATE-001',
            'Referonce' => 'REF-PROD-UPDATE-001',
            'Designation' => 'Produit Avant Update',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->put(route('product.update', $productId), [
                'Category_ID' => $categoryId,
                'code' => 'P-UPDATE-002',
                'Referonce' => 'REF-PROD-UPDATE-002',
                'Designation' => 'Produit Après Update',
                'prace_bay' => 120,
                'prace_sell' => 180,
                'Quantite' => 15,
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'company_id' => $companyId,
            'code' => 'P-UPDATE-002',
            'Referonce' => 'REF-PROD-UPDATE-002',
            'Designation' => 'Produit Après Update',
            'prace_bay' => 120,
            'prace_sell' => 180,
            'Quantite' => 15,
        ]);
    }

    public function test_delete_product_removes_product(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie Product Delete Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = DB::table('products')->insertGetId([
            'company_id' => $companyId,
            'Category_ID' => $categoryId,
            'code' => 'P-DELETE-001',
            'Referonce' => 'REF-PROD-DELETE-001',
            'Designation' => 'Produit Delete Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('product.destroy', $productId));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('products', [
            'id' => $productId,
        ]);
    }
}