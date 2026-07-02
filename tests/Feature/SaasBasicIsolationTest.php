<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaasBasicIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createCompanyAdmin(string $companyName, string $slug, string $email): User
    {
        $company = Company::create([
            'name' => $companyName,
            'slug' => $slug,
            'plan' => 'free',
            'status' => 'active',
        ]);

        return User::factory()->create([
            'name' => $companyName . ' Admin',
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'admin',
            'company_id' => $company->id,
        ]);
    }

    public function test_company_user_sees_only_own_categories(): void
    {
        $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');
        $adminB = $this->createCompanyAdmin('Company B', 'company-b', 'admin-b@example.com');

        DB::table('categories')->insert([
            [
                'company_id' => $adminA->company_id,
                'Category' => 'Catégorie Company A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $adminB->company_id,
                'Category' => 'Catégorie Company B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($adminA)->get(route('category.index'));

        $response->assertStatus(200);
        $response->assertSee('Catégorie Company A');
        $response->assertDontSee('Catégorie Company B');
    }

    public function test_company_user_sees_only_own_products(): void
    {
        $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');
        $adminB = $this->createCompanyAdmin('Company B', 'company-b', 'admin-b@example.com');

        $categoryA = DB::table('categories')->insertGetId([
            'company_id' => $adminA->company_id,
            'Category' => 'Catégorie Product A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryB = DB::table('categories')->insertGetId([
            'company_id' => $adminB->company_id,
            'Category' => 'Catégorie Product B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            [
                'company_id' => $adminA->company_id,
                'Category_ID' => $categoryA,
                'code' => 'SAAS-A-001',
                'Referonce' => 'SAAS-REF-A',
                'Designation' => 'Produit Company A',
                'prace_bay' => 100,
                'prace_sell' => 150,
                'Quantite' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $adminB->company_id,
                'Category_ID' => $categoryB,
                'code' => 'SAAS-B-001',
                'Referonce' => 'SAAS-REF-B',
                'Designation' => 'Produit Company B',
                'prace_bay' => 100,
                'prace_sell' => 150,
                'Quantite' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($adminA)->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertSee('Produit Company A');
        $response->assertDontSee('Produit Company B');
    }

    public function test_company_user_sees_only_own_customers(): void
    {
        $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');
        $adminB = $this->createCompanyAdmin('Company B', 'company-b', 'admin-b@example.com');

        DB::table('customers')->insert([
            [
                'company_id' => $adminA->company_id,
                'name' => 'Client Company A',
                'address' => 'Adresse A',
                'contact' => '0600000001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $adminB->company_id,
                'name' => 'Client Company B',
                'address' => 'Adresse B',
                'contact' => '0600000002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($adminA)->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertSee('Client Company A');
        $response->assertDontSee('Client Company B');
    }

    public function test_company_user_sees_only_own_suppliers(): void
{
    $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');
    $adminB = $this->createCompanyAdmin('Company B', 'company-b', 'admin-b@example.com');

    DB::table('suppliers')->insert([
        [
            'company_id' => $adminA->company_id,
            'name' => 'Fournisseur Company A',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'company_id' => $adminB->company_id,
            'name' => 'Fournisseur Company B',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($adminA)->get(route('suppliers.index'));

    $response->assertStatus(200);
    $response->assertSee('Fournisseur Company A');
    $response->assertDontSee('Fournisseur Company B');
}

public function test_supplier_creation_sets_company_id(): void
{
    $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');

    $response = $this->actingAs($adminA)
        ->post(route('suppliers.store'), [
            'name' => 'Fournisseur Created Company A',
        ]);

    $response->assertStatus(302);

    $this->assertDatabaseHas('suppliers', [
        'company_id' => $adminA->company_id,
        'name' => 'Fournisseur Created Company A',
    ]);
}

public function test_company_user_cannot_delete_other_company_supplier(): void
{
    $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');
    $adminB = $this->createCompanyAdmin('Company B', 'company-b', 'admin-b@example.com');

    $supplierBId = DB::table('suppliers')->insertGetId([
        'company_id' => $adminB->company_id,
        'name' => 'Fournisseur Protected Company B',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($adminA)
        ->delete(route('suppliers.destroy', $supplierBId));

    $response->assertStatus(404);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplierBId,
        'company_id' => $adminB->company_id,
        'name' => 'Fournisseur Protected Company B',
    ]);
}
}