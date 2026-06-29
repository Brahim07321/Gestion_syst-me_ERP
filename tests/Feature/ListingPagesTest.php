<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ListingPagesTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_products_page_shows_product(): void
    {
        $admin = $this->adminUser();

        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Catégorie Listing Product Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'Category_ID' => $categoryId,
            'code' => 'P-LIST-001',
            'Referonce' => 'REF-LIST-PROD-001',
            'Designation' => 'Produit Listing Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertSee('Produit Listing Test');
        $response->assertSee('REF-LIST-PROD-001');
    }

    public function test_customers_page_shows_customer(): void
    {
        $admin = $this->adminUser();

        DB::table('customers')->insert([
            'name' => 'Client Listing Test',
            'address' => 'Adresse Listing Test',
            'contact' => '0611111111',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertSee('Client Listing Test');
    }

    public function test_stock_page_shows_product(): void
    {
        $admin = $this->adminUser();

        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Catégorie Stock Listing Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'Category_ID' => $categoryId,
            'code' => 'P-STOCK-LIST-001',
            'Referonce' => 'REF-STOCK-LIST-001',
            'Designation' => 'Produit Stock Listing Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('stock.index'));

        $response->assertStatus(200);
        $response->assertSee('Produit Stock Listing Test');
        $response->assertSee('REF-STOCK-LIST-001');
    }
}