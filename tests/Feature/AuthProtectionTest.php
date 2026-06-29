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
public function test_guest_cannot_access_stock_page(): void
{
    $response = $this->get(route('stock.index'));

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_documents_archives_page(): void
{
    $response = $this->get(route('documents.archives'));

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_reports_page(): void
{
    $response = $this->get(route('reports'));

    $response->assertRedirect(route('login'));
}
public function test_guest_cannot_access_expenses_page(): void
{
    $response = $this->get('/expenses');

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_stock_movements_page(): void
{
    $response = $this->get(route('stock.movements'));

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_ai_purchase_import_page(): void
{
    $response = $this->get(route('purchases.import.ai.create'));

    $response->assertRedirect(route('login'));
}
public function test_guest_cannot_access_factures_excel_export(): void
{
    $response = $this->get(route('factures.export.excel'));

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_factures_pdf_export(): void
{
    $response = $this->get(route('factures.export.pdf'));

    $response->assertRedirect(route('login'));
}

public function test_guest_cannot_access_purchases_excel_export(): void
{
    $response = $this->get(route('purchases.export.excel'));

    $response->assertRedirect(route('login'));
}
}