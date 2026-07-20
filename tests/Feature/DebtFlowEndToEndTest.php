<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DebtFlowEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_orders_does_not_create_debt_before_warehouse_slip_approval(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password'),
            'type' => 'user',
            'status' => 'active',
        ]);

        $company = Company::create([
            'name' => 'Test Company',
            'address' => 'Test Address',
            'phone' => '0123456789',
            'owner_id' => $user->id,
        ]);

        $currency = Currency::create([
            'name' => 'Vietnamese Dong',
            'code' => 'VND',
            'symbol' => '₫',
            'exchange_rate' => 1,
            'is_active' => true,
        ]);

        $user->update(['company_id' => $company->id]);

        $this->actingAs($user);

        $customer = Customer::create([
            'company_id' => $company->id,
            'code' => 'KH0001',
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'phone' => '0123456789',
            'currency_id' => $currency->id,
            'opening_debt' => 0,
            'status' => 'active',
        ]);

        $supplier = Supplier::create([
            'code' => 'NCC0001',
            'name' => 'Test Supplier',
            'phone' => '0987654321',
            'email' => 'supplier@test.com',
            'currency_id' => $currency->id,
            'address_detail' => 'Test Address',
            'total_debts' => 0,
            'total_advance' => 0,
            'status' => 'active',
        ]);

        $salesOrder = SalesOrder::create([
            'company_id' => $company->id,
            'code' => 'SO001',
            'customer_id' => $customer->id,
            'currency_id' => $currency->id,
            'exchange_rate' => 1,
            'total_amount' => 500000,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $purchaseOrder = PurchaseOrder::create([
            'company_id' => $company->id,
            'code' => 'PO001',
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'exchange_rate' => 1,
            'total_amount' => 400000,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $salesOrder->update(['status' => 'approved']);
        $purchaseOrder->update(['status' => 'approved']);

        $customerDebt = CustomerDebt::where('customer_id', $customer->id)->latest()->first();
        $supplierDebt = SupplierDebt::where('supplier_id', $supplier->id)->latest()->first();

        $this->assertNull($customerDebt, 'Duyệt đơn bán không được phát sinh công nợ.');
        $this->assertNull($supplierDebt, 'Duyệt đơn mua không được phát sinh công nợ.');

    }
}
