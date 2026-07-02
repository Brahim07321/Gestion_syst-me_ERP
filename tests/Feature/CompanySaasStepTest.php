<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanySaasStepTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_company_is_created_by_seeder(): void
    {
        $this->seed();

        $this->assertDatabaseHas('companies', [
            'slug' => 'default-company',
            'name' => 'Default Company',
        ]);
    }

    public function test_admin_user_belongs_to_default_company(): void
    {
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();

        $this->assertNotNull($admin->company_id);
        $this->assertEquals('default-company', $admin->company->slug);
    }

    public function test_company_has_many_users_relation(): void
    {
        $this->seed();

        $company = Company::where('slug', 'default-company')->firstOrFail();

        $this->assertTrue($company->users()->exists());
    }
}