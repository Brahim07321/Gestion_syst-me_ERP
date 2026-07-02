<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchFiltersTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_products_search_filter_shows_matching_product(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie Search Product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            [
                'Category_ID' => $categoryId,
                'code' => 'PROD-SEARCH-KEEP',
                'Referonce' => 'REF-PROD-KEEP',
                'Designation' => 'Produit Search Keep',
                'prace_bay' => 100,
                'prace_sell' => 150,
                'Quantite' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Category_ID' => $categoryId,
                'code' => 'PROD-SEARCH-HIDE',
                'Referonce' => 'REF-PROD-HIDE',
                'Designation' => 'Produit Search Hide',
                'prace_bay' => 100,
                'prace_sell' => 150,
                'Quantite' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('product.index', ['search' => 'REF-PROD-KEEP']));

        $response->assertStatus(200);
        $response->assertSee('REF-PROD-KEEP');
        $response->assertDontSee('REF-PROD-HIDE');
    }

    public function test_customers_search_filter_shows_matching_customer(): void
    {
          $admin = $this->adminUser();
    $companyId = $admin->company_id;

        DB::table('customers')->insert([
            [
                'company_id' => $companyId,
                'name' => 'Client Search Keep',
                'address' => 'Adresse 1',
                'contact' => '0600000001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'name' => 'Client Search Hide',
                'address' => 'Adresse 2',
                'contact' => '0600000002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('customers.index', ['search' => 'Client Search Keep']));

        $response->assertStatus(200);
        $response->assertSee('Client Search Keep');
        $response->assertDontSee('Client Search Hide');
    }

    public function test_suppliers_search_filter_shows_matching_supplier(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        DB::table('suppliers')->insert([
            [
                'company_id' => $companyId,
                'name' => 'Fournisseur Search Keep',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $companyId,
                'name' => 'Fournisseur Search Hide',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('suppliers.index', ['search' => 'Fournisseur Search Keep']));

        $response->assertStatus(200);
        $response->assertSee('Fournisseur Search Keep');
        $response->assertDontSee('Fournisseur Search Hide');
    }
}