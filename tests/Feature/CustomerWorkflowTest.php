<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed();

        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_create_customer_stores_customer(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->post(route('customers.store'), [
                'name' => 'Client Create Test',
                'address' => 'Adresse Client Create Test',
                'contact' => '0611111111',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('customers', [
            'company_id' => $admin->company_id,
            'name' => 'Client Create Test',
            'address' => 'Adresse Client Create Test',
            'contact' => '0611111111',
        ]);
    }

    public function test_update_customer_changes_customer_data(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        $customerId = DB::table('customers')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Client Avant Update',
            'address' => 'Ancienne Adresse',
            'contact' => '0611111111',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->put(route('customers.update', $customerId), [
                'name' => 'Client Après Update',
                'address' => 'Nouvelle Adresse',
                'contact' => '0622222222',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'name' => 'Client Après Update',
            'address' => 'Nouvelle Adresse',
            'contact' => '0622222222',
        ]);
    }

    public function test_delete_customer_removes_customer(): void
    {
        $admin = $this->adminUser();
        $companyId = $admin->company_id;

        $customerId = DB::table('customers')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Client Delete Test',
            'address' => 'Adresse Delete Test',
            'contact' => '0633333333',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('customers.destroy', $customerId));

        $response->assertStatus(302);

        $this->assertDatabaseMissing('customers', [
            'id' => $customerId,
        ]);
    }
}