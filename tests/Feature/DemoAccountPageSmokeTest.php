<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAccountPageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_demo_module_account_can_open_its_pages_and_list_apis(): void
    {
        $this->seed(DatabaseSeeder::class);

        $matrix = [
            'admin@demo.vn' => [
                'pages' => [
                    '/user', '/role', '/permission', '/audit-logs',
                    '/purchase', '/purchase/suppliers', '/purchase/categories', '/purchase/units', '/purchase/products', '/purchase/orders',
                    '/warehouse', '/warehouse/list', '/warehouse/categories', '/warehouse/units', '/warehouse/products', '/warehouse/orders', '/warehouse/slips', '/warehouse/transfers', '/warehouse/inventory-movements',
                    '/sale', '/sale/customers', '/sale/orders',
                    '/accountant', '/accountant/accounts', '/accountant/currencies', '/accountant/banks', '/accountant/transactions', '/accountant/customers-debt', '/accountant/suppliers-debt', '/accountant/transaction-categories', '/accountant/account-ledgers',
                ],
                'apis' => [
                    '/api/users/user', '/api/roles', '/api/permissions', '/api/audit-logs',
                    '/api/purchase/suppliers', '/api/purchase/categories', '/api/purchase/units', '/api/purchase/products', '/api/purchase/orders',
                    '/api/warehouses', '/api/warehouse/categories', '/api/warehouse/units', '/api/warehouse/products', '/api/warehouse/slips', '/api/warehouse/transfers', '/api/warehouse/inventory-movements',
                    '/api/sale/customers', '/api/sale/orders',
                    '/api/accountant/accounts', '/api/accountant/currencies', '/api/accountant/banks', '/api/accountant/transactions', '/api/accountant/customers-debt', '/api/accountant/suppliers-debt', '/api/accountant/transaction-categories', '/api/accountant/account-ledgers',
                ],
            ],
            'hr@demo.vn' => [
                'pages' => ['/user', '/role', '/permission', '/audit-logs'],
                'apis' => ['/api/users/user', '/api/roles', '/api/permissions', '/api/audit-logs'],
            ],
            'purchase@demo.vn' => [
                'pages' => ['/purchase', '/purchase/suppliers', '/purchase/categories', '/purchase/units', '/purchase/products', '/purchase/orders'],
                'apis' => ['/api/purchase/suppliers', '/api/purchase/categories', '/api/purchase/units', '/api/purchase/products', '/api/purchase/orders', '/api/warehouses/all'],
            ],
            'warehouse@demo.vn' => [
                'pages' => ['/warehouse', '/warehouse/list', '/warehouse/categories', '/warehouse/units', '/warehouse/products', '/warehouse/orders', '/warehouse/slips', '/warehouse/transfers', '/warehouse/inventory-movements'],
                'apis' => ['/api/warehouses', '/api/warehouse/categories', '/api/warehouse/units', '/api/warehouse/products', '/api/warehouse/slips', '/api/warehouse/transfers', '/api/warehouse/inventory-movements'],
            ],
            'sales@demo.vn' => [
                'pages' => ['/sale', '/sale/customers', '/sale/orders'],
                'apis' => ['/api/sale/customers', '/api/sale/customers/1/detail', '/api/sale/orders'],
            ],
            'accountant@demo.vn' => [
                'pages' => ['/accountant', '/accountant/accounts', '/accountant/currencies', '/accountant/banks', '/accountant/transactions', '/accountant/customers-debt', '/accountant/suppliers-debt', '/accountant/transaction-categories', '/accountant/account-ledgers'],
                'apis' => ['/api/accountant/accounts', '/api/accountant/currencies', '/api/accountant/banks', '/api/accountant/transactions', '/api/accountant/customers-debt', '/api/accountant/suppliers-debt', '/api/accountant/transaction-categories', '/api/accountant/account-ledgers'],
            ],
        ];

        foreach ($matrix as $email => $targets) {
            $user = User::where('email', $email)->firstOrFail();

            foreach ($targets['pages'] as $uri) {
                $this->actingAs($user)->get($uri)->assertSuccessful();
            }

            foreach ($targets['apis'] as $uri) {
                $this->actingAs($user)->getJson($uri)->assertSuccessful();
            }
        }
    }

    public function test_demo_module_accounts_cannot_open_other_module_pages(): void
    {
        $this->seed(DatabaseSeeder::class);

        $matrix = [
            'hr@demo.vn' => ['/purchase', '/warehouse', '/sale', '/accountant'],
            'purchase@demo.vn' => ['/user', '/warehouse', '/sale', '/accountant'],
            'warehouse@demo.vn' => ['/user', '/manage/user', '/purchase', '/sale', '/accountant'],
            'sales@demo.vn' => ['/user', '/purchase', '/warehouse', '/accountant'],
            'accountant@demo.vn' => ['/user', '/manage/user', '/purchase', '/warehouse', '/sale'],
        ];

        foreach ($matrix as $email => $uris) {
            $user = User::where('email', $email)->firstOrFail();
            foreach ($uris as $uri) {
                $this->actingAs($user)->get($uri)->assertForbidden();
            }
        }
    }

    public function test_warehouse_account_sees_approved_purchase_orders_waiting_for_stock_in(): void
    {
        $this->seed(DatabaseSeeder::class);

        $warehouseUser = User::where('email', 'warehouse@demo.vn')->firstOrFail();

        $this->actingAs($warehouseUser)
            ->getJson('/api/warehouse/orders')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.code', 'PO-DEMO-001');
    }

    public function test_sales_account_can_view_complete_customer_detail_summary(): void
    {
        $this->seed(DatabaseSeeder::class);
        $salesUser = User::where('email', 'sales@demo.vn')->firstOrFail();

        $response = $this->actingAs($salesUser)
            ->getJson('/api/sale/customers/1/detail')
            ->assertOk()
            ->assertJsonPath('can_view_debt', true)
            ->assertJsonCount(2, 'debt_history');

        $this->assertSame(
            (float) $response->json('customer.opening_debt'),
            (float) $response->json('debt_summary.opening_debt')
        );
        $this->assertSame(1958000.0, (float) $response->json('debt_summary.total_receivable'));
        $this->assertSame(500000.0, (float) $response->json('debt_summary.total_paid'));
        $this->assertSame(1958000.0, (float) $response->json('debt_summary.remaining_debt'));
    }
}
