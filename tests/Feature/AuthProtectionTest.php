<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_products_page(): void
    {
        $response = $this->get(route('product.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_purchases_page(): void
    {
        $response = $this->get(route('purchases.index'));

        $response->assertRedirect(route('login'));
    }
    public function test_guest_cannot_access_factures_page(): void
{
    $response = $this->get(route('factures.index'));

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_customers_page(): void
{
    $response = $this->get(route('customers.index'));

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_suppliers_page(): void
{
    $response = $this->get(route('suppliers.index'));

    $response->assertRedirect(route('login'));
}
}