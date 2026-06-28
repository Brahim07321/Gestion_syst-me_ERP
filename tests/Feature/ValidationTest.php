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
public function test_purchase_creation_requires_supplier(): void
{
    $admin = $this->adminUser();

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Purchase Validation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'Category_ID' => $categoryId,
        'code' => 'P-PUR-VALID-001',
        'Referonce' => 'REF-PUR-VALID-001',
        'Designation' => 'Produit Purchase Validation',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('purchases.store'), [
            // 'supplier_id' ناقص
            'purchase_date' => now()->toDateString(),
            'status' => 'en attente',
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                    'buy_price' => 100,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['supplier_id']);

    $this->assertDatabaseCount('purchases', 0);
}
public function test_purchase_creation_requires_items(): void
{
    $admin = $this->adminUser();

    $supplierId = \Illuminate\Support\Facades\DB::table('suppliers')->insertGetId([
        'name' => 'Fournisseur Purchase Validation',
        'phone' => '0611111111',
        'address' => 'Adresse Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('purchases.store'), [
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'status' => 'en attente',
            // 'items' ناقصة هنا
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items']);

    $this->assertDatabaseCount('purchases', 0);
}
public function test_purchase_item_requires_product_id(): void
{
    $admin = $this->adminUser();

    $supplierId = \Illuminate\Support\Facades\DB::table('suppliers')->insertGetId([
        'name' => 'Fournisseur Item Validation',
        'phone' => '0611111111',
        'address' => 'Adresse Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('purchases.store'), [
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'status' => 'en attente',
            'items' => [
                [
                    // 'product_id' ناقص هنا
                    'quantity' => 2,
                    'buy_price' => 100,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items.0.product_id']);

    $this->assertDatabaseCount('purchases', 0);
}
public function test_purchase_item_requires_quantity(): void
{
    $admin = $this->adminUser();

    $supplierId = \Illuminate\Support\Facades\DB::table('suppliers')->insertGetId([
        'name' => 'Fournisseur Quantity Validation',
        'phone' => '0611111111',
        'address' => 'Adresse Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Quantity Validation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'Category_ID' => $categoryId,
        'code' => 'P-PUR-QTY-001',
        'Referonce' => 'REF-PUR-QTY-001',
        'Designation' => 'Produit Quantity Validation',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('purchases.store'), [
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'status' => 'en attente',
            'items' => [
                [
                    'product_id' => $productId,
                    // 'quantity' ناقصة هنا
                    'buy_price' => 100,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items.0.quantity']);

    $this->assertDatabaseCount('purchases', 0);
}
public function test_purchase_item_requires_buy_price(): void
{
    $admin = $this->adminUser();

    $supplierId = \Illuminate\Support\Facades\DB::table('suppliers')->insertGetId([
        'name' => 'Fournisseur Buy Price Validation',
        'phone' => '0611111111',
        'address' => 'Adresse Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Buy Price Validation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $productId = \Illuminate\Support\Facades\DB::table('products')->insertGetId([
        'Category_ID' => $categoryId,
        'code' => 'P-PUR-PRICE-001',
        'Referonce' => 'REF-PUR-PRICE-001',
        'Designation' => 'Produit Buy Price Validation',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('purchases.store'), [
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'status' => 'en attente',
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                    // 'buy_price' ناقصة هنا
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items.0.buy_price']);

    $this->assertDatabaseCount('purchases', 0);
}

public function test_facture_creation_requires_customer_search(): void
{
    $admin = $this->adminUser();

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Facture Validation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\DB::table('products')->insert([
        'Category_ID' => $categoryId,
        'code' => 'P-FAC-VALID-001',
        'Referonce' => 'REF-FAC-VALID-001',
        'Designation' => 'Produit Facture Validation',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            // 'customer_search' ناقصة هنا
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'referonce' => 'REF-FAC-VALID-001',
                    'designation' => 'Produit Facture Validation',
                    'price' => 150,
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['customer_search']);

    $this->assertDatabaseCount('factures', 0);
}

public function test_facture_creation_requires_invoice_date(): void
{
    $admin = $this->adminUser();

    $categoryId = \Illuminate\Support\Facades\DB::table('categories')->insertGetId([
        'Category' => 'Catégorie Facture Date Validation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\DB::table('products')->insert([
        'Category_ID' => $categoryId,
        'code' => 'P-FAC-DATE-001',
        'Referonce' => 'REF-FAC-DATE-001',
        'Designation' => 'Produit Facture Date Validation',
        'prace_bay' => 100,
        'prace_sell' => 150,
        'Quantite' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Date Validation',
            // 'invoice_date' ناقصة هنا
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'referonce' => 'REF-FAC-DATE-001',
                    'designation' => 'Produit Facture Date Validation',
                    'price' => 150,
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['invoice_date']);

    $this->assertDatabaseCount('factures', 0);
}

public function test_facture_creation_requires_items(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Items Validation',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            // 'items' ناقصة هنا
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items']);

    $this->assertDatabaseCount('factures', 0);
}
public function test_facture_item_requires_referonce(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Reference Validation',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    // 'referonce' ناقصة هنا
                    'designation' => 'Produit Sans Référence',
                    'price' => 150,
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items.0.referonce']);

    $this->assertDatabaseCount('factures', 0);
}
public function test_facture_item_requires_designation(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Designation Validation',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'referonce' => 'REF-FAC-DES-001',
                    // 'designation' ناقصة هنا
                    'price' => 150,
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items.0.designation']);

    $this->assertDatabaseCount('factures', 0);
}
public function test_facture_item_requires_price(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Price Validation',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'referonce' => 'REF-FAC-PRICE-001',
                    'designation' => 'Produit Price Validation',
                    // 'price' ناقصة هنا
                    'quantity' => 2,
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items.0.price']);

    $this->assertDatabaseCount('factures', 0);
}
public function test_facture_item_requires_quantity(): void
{
    $admin = $this->adminUser();

    $response = $this->actingAs($admin)
        ->post(route('facture.store'), [
            'customer_search' => 'Client Quantity Validation',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'referonce' => 'REF-FAC-QTY-001',
                    'designation' => 'Produit Quantity Validation',
                    'price' => 150,
                    // 'quantity' ناقصة هنا
                ],
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['items.0.quantity']);

    $this->assertDatabaseCount('factures', 0);
}

}