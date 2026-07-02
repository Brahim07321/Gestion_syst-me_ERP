<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_create_category_stores_category(): void
    {
        $admin = $this->adminUser();
        

        $response = $this->actingAs($admin)
            ->post(route('category.store'), [
                'Category' => 'Catégorie Create Test',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('categories', [
            'Category' => 'Catégorie Create Test',
        ]);
    }

    public function test_categories_page_shows_category(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;
        
        DB::table('categories')->insert([
            'company_id' => $companyId,
            'Category' => 'Catégorie Page Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $response = $this->actingAs($admin)
            ->get(route('category.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Catégorie Page Test');
    }

    public function test_delete_category_removes_category(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;


        $categoryId = DB::table('categories')->insertGetId([
            'company_id' => $companyId,
            'Category' => 'Catégorie Delete Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('Category.destroy', $categoryId));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('categories', [
            'id' => $categoryId,
        ]);
    }
}