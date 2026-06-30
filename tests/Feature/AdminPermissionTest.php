<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    private function normalUser(): User
    {
        $this->seed();

        return User::factory()->create([
            'name' => 'Normal User Test',
            'email' => 'normal-user-test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }

    public function test_normal_user_cannot_create_category(): void
    {
        $user = $this->normalUser();

        $response = $this->actingAs($user)
            ->post(route('category.store'), [
                'Category' => 'Catégorie Refusée Test',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('categories', [
            'Category' => 'Catégorie Refusée Test',
        ]);
    }

    public function test_normal_user_cannot_delete_category(): void
    {
        $user = $this->normalUser();

        $categoryId = DB::table('categories')->insertGetId([
            'Category' => 'Catégorie Delete Refusée Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->delete(route('Category.destroy', $categoryId));

        $response->assertStatus(302);

        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
            'Category' => 'Catégorie Delete Refusée Test',
        ]);
    }

    public function test_normal_user_cannot_create_expense(): void
    {
        $user = $this->normalUser();

        $response = $this->actingAs($user)
            ->post(route('expenses.store'), [
                'name' => 'Dépense Refusée Test',
                'amount' => 100,
                'expense_date' => now()->toDateString(),
                'description' => 'Test',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('expenses', [
            'name' => 'Dépense Refusée Test',
        ]);
    }
}