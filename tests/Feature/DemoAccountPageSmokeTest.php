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
                    '/warehouse', '/warehouse/list', '/warehouse/categories', '/warehouse/units', '/warehouse/products', '/warehouse/orders', '/warehouse/slips',
                    '/sale', '/sale/customers', '/sale/orders',
                    '/accountant', '/accountant/accounts', '/accountant/currencies', '/accountant/banks', '/accountant/transactions', '/accountant/customers-debt', '/accountant/suppliers-debt', '/accountant/transaction-categories', '/accountant/account-ledgers',
                ],
                'apis' => [
                    '/api/users/user', '/api/roles', '/api/permissions', '/api/audit-logs',
                    '/api/purchase/suppliers', '/api/purchase/categories', '/api/purchase/units', '/api/purchase/products', '/api/purchase/orders',
                    '/api/warehouses', '/api/warehouse/categories', '/api/warehouse/units', '/api/warehouse/products', '/api/warehouse/slips',
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
                'apis' => ['/api/purchase/suppliers', '/api/purchase/categories', '/api/purchase/units', '/api/purchase/products', '/api/purchase/orders'],
            ],
            'warehouse@demo.vn' => [
                'pages' => ['/warehouse', '/warehouse/list', '/warehouse/categories', '/warehouse/units', '/warehouse/products', '/warehouse/orders', '/warehouse/slips'],
                'apis' => ['/api/warehouses', '/api/warehouse/categories', '/api/warehouse/units', '/api/warehouse/products', '/api/warehouse/slips'],
            ],
            'sales@demo.vn' => [
                'pages' => ['/sale', '/sale/customers', '/sale/orders'],
                'apis' => ['/api/sale/customers', '/api/sale/orders'],
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
            'warehouse@demo.vn' => ['/user', '/purchase', '/sale', '/accountant'],
            'sales@demo.vn' => ['/user', '/purchase', '/warehouse', '/accountant'],
            'accountant@demo.vn' => ['/user', '/purchase', '/warehouse', '/sale'],
        ];

        foreach ($matrix as $email => $uris) {
            $user = User::where('email', $email)->firstOrFail();
            foreach ($uris as $uri) {
                $this->actingAs($user)->get($uri)->assertForbidden();
            }
        }
    }
}
