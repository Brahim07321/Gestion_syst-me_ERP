<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_create_expense_stores_expense(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->post(route('expenses.store'), [
                'name' => 'Dépense Create Test',
                'amount' => 250,
                'expense_date' => now()->toDateString(),
                'description' => 'Description dépense create test',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('expenses', [
            'name' => 'Dépense Create Test',
            'amount' => 250,
            'description' => 'Description dépense create test',
        ]);
    }

    public function test_expenses_page_shows_expense(): void
    {
        $admin = $this->adminUser();

        DB::table('expenses')->insert([
            'name' => 'Dépense Page Test',
            'amount' => 300,
            'expense_date' => now()->toDateString(),
            'description' => 'Description dépense page test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertSee('Dépense Page Test');
    }

    public function test_delete_expense_removes_expense(): void
    {
        $admin = $this->adminUser();

        $expenseId = DB::table('expenses')->insertGetId([
            'name' => 'Dépense Delete Test',
            'amount' => 400,
            'expense_date' => now()->toDateString(),
            'description' => 'Description dépense delete test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('expenses.destroy', $expenseId));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('expenses', [
            'id' => $expenseId,
        ]);
    }
}