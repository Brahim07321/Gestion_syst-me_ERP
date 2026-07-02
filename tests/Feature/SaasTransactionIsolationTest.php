<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaasTransactionIsolationTest extends TestCase
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

    public function test_company_user_sees_only_own_expenses(): void
    {
        $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');
        $adminB = $this->createCompanyAdmin('Company B', 'company-b', 'admin-b@example.com');

        DB::table('expenses')->insert([
            [
                'company_id' => $adminA->company_id,
                'name' => 'Expense Company A',
                'amount' => 100,
                'expense_date' => now()->toDateString(),
                'description' => 'Expense visible A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $adminB->company_id,
                'name' => 'Expense Company B',
                'amount' => 200,
                'expense_date' => now()->toDateString(),
                'description' => 'Expense hidden B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($adminA)->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertSee('Expense Company A');
        $response->assertDontSee('Expense Company B');
    }

    public function test_expense_creation_sets_company_id(): void
    {
        $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');

        $response = $this->actingAs($adminA)
            ->post(route('expenses.store'), [
                'name' => 'Expense Created Company A',
                'amount' => 150,
                'expense_date' => now()->toDateString(),
                'description' => 'Created expense',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('expenses', [
            'company_id' => $adminA->company_id,
            'name' => 'Expense Created Company A',
            'amount' => 150,
        ]);
    }

    public function test_company_user_cannot_delete_other_company_expense(): void
    {
        $adminA = $this->createCompanyAdmin('Company A', 'company-a', 'admin-a@example.com');
        $adminB = $this->createCompanyAdmin('Company B', 'company-b', 'admin-b@example.com');

        $expenseBId = DB::table('expenses')->insertGetId([
            'company_id' => $adminB->company_id,
            'name' => 'Expense Protected Company B',
            'amount' => 300,
            'expense_date' => now()->toDateString(),
            'description' => 'Protected expense',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($adminA)
            ->delete(route('expenses.destroy', $expenseBId));

        $response->assertStatus(404);

        $this->assertDatabaseHas('expenses', [
            'id' => $expenseBId,
            'company_id' => $adminB->company_id,
            'name' => 'Expense Protected Company B',
        ]);
    }
}