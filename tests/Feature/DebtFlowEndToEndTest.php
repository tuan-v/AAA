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

    public function test_purchase_and_sales_debt_flow_with_payment_is_consistent(): void
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

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('symbol')->nullable();
            $table->decimal('exchange_rate', 18, 2)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

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

        $account = Account::create([
            'company_id' => $company->id,
            'code' => 'CASH01',
            'name' => 'Cash',
            'currency_id' => $currency->id,
            'type' => 'cash',
            'current_balance' => 1000000,
            'opening_balance' => 1000000,
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
        ]);

        $purchaseOrder = PurchaseOrder::create([
            'company_id' => $company->id,
            'code' => 'PO001',
            'supplier_id' => $supplier->id,
            'currency_id' => $currency->id,
            'exchange_rate' => 1,
            'total_amount' => 400000,
            'status' => 'pending',
        ]);

        $salesOrder->update(['status' => 'approved']);
        $purchaseOrder->update(['status' => 'approved']);

        $customerDebt = CustomerDebt::where('customer_id', $customer->id)->latest()->first();
        $supplierDebt = SupplierDebt::where('supplier_id', $supplier->id)->latest()->first();

        $this->assertNotNull($customerDebt);
        $this->assertSame(500000.0, (float) $customerDebt->amount);
        $this->assertNotNull($supplierDebt);
        $this->assertSame(400000.0, (float) $supplierDebt->amount);

        $transaction = Transaction::create([
            'company_id' => $company->id,
            'code' => 'TXN-TEST-001',
            'transaction_date' => now(),
            'type' => 'receipt',
            'category_id' => 1,
            'currency_id' => $currency->id,
            'amount' => 300000,
            'exchange_rate' => 1,
            'amount_base' => 300000,
            'to_account_id' => $account->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $salesOrder->id,
            'description' => 'Customer payment',
            'created_by' => $user->id,
        ]);

        $this->assertSame(200000.0, (float) CustomerDebt::where('customer_id', $customer->id)->sum('amount'));
        $this->assertSame(400000.0, (float) SupplierDebt::where('supplier_id', $supplier->id)->sum('amount'));
    }
}
