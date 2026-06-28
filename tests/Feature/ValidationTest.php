<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_customer_creation_requires_name(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->post('/Customer', [
                'address' => 'Adresse Test',
                'contact' => '0611111111',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);

        $this->assertDatabaseMissing('customers', [
            'contact' => '0611111111',
        ]);
    }

    public function test_customer_creation_accepts_empty_address_and_contact(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->post('/Customer', [
            'name' => 'Client Sans Contact',
            'address' => '',
            'contact' => '',
        ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('customers', [
        'name' => 'Client Sans Contact',
        'address' => '',
        'contact' => '',
    ]);
}
public function test_supplier_creation_requires_name(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->post('/suppliers', [
            'phone' => '0611111111',
            'address' => 'Adresse fournisseur test',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['name']);

    $this->assertDatabaseMissing('suppliers', [
        'phone' => '0611111111',
    ]);
}
public function test_product_creation_requires_designation(): void
{
    $admin = $this->adminUser();

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Validation Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post('/product', [
            'Category_ID' => $categoryId,
            'code' => 'P-VALID-001',
            'Referonce' => 'REF-VALID-001',
            // 'Designation' ناقصة هنا
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['Designation']);

    $this->assertDatabaseMissing('products', [
        'Referonce' => 'REF-VALID-001',
    ]);
}
public function test_product_creation_requires_referonce(): void
{
    $admin = $this->adminUser();

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Validation Test 2',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post('/product', [
            'Category_ID' => $categoryId,
            'code' => 'P-VALID-002',
            // 'Referonce' ناقصة هنا
            'Designation' => 'Produit Validation Test',
            'prace_bay' => 100,
            'prace_sell' => 150,
            'Quantite' => 10,
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['Referonce']);

    $this->assertDatabaseMissing('products', [
        'code' => 'P-VALID-002',
    ]);
}
public function test_product_creation_requires_buy_and_sell_prices(): void
{
    $admin = $this->adminUser();

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Validation Test 3',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post('/product', [
            'Category_ID' => $categoryId,
            'code' => 'P-VALID-003',
            'Referonce' => 'REF-VALID-003',
            'Designation' => 'Produit Prix Validation',
            // 'prace_bay' ناقصة
            // 'prace_sell' ناقصة
            'Quantite' => 10,
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'prace_bay',
        'prace_sell',
    ]);

    $this->assertDatabaseMissing('products', [
        'Referonce' => 'REF-VALID-003',
    ]);
}
public function test_product_creation_requires_quantite(): void
{
    $admin = $this->adminUser();

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Validation Test 4',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post('/product', [
            'Category_ID' => $categoryId,
            'code' => 'P-VALID-004',
            'Referonce' => 'REF-VALID-004',
            'Designation' => 'Produit Quantité Validation',
            'prace_bay' => 100,
            'prace_sell' => 150,
            // 'Quantite' ناقصة
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['Quantite']);

    $this->assertDatabaseMissing('products', [
        'Referonce' => 'REF-VALID-004',
    ]);
}

}