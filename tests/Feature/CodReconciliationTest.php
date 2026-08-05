<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CodReconciliation;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\ShippingPartner;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseSlip;
use App\Models\WarehouseProductStock;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_cod_can_be_reconciled_without_creating_customer_debt(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($user);
        $company = $user->company;
        $customer = Customer::where('company_id', $company->id)->where('code', '!=', 'KH_LE')->firstOrFail();
        $account = Account::where('company_id', $company->id)->where('type', 'cash')->whereNull('bank_id')->firstOrFail();
        $balanceBefore = (float) $account->current_balance;
        $partner = ShippingPartner::create(['company_id' => $company->id, 'code' => 'GHN', 'name' => 'Giao Hàng Nhanh']);
        $order = SalesOrder::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'currency_id' => $account->currency_id,
            'status' => 'completed',
            'sales_channel' => 'storefront',
            'payment_method' => 'cod',
            'sales_channel' => 'storefront',
            'payment_status' => 'paid',
            'cod_status' => 'collected',
            'cod_amount' => 110000,
            'shipping_fee' => 10000,
            'carrier_shipping_fee' => 7000,
            'carrier_service_fee' => 5000,
            'total_amount' => 110000,
            'completed_at' => now(),
            'cod_collected_at' => now(),
            'created_by' => $user->id,
        ]);

        $this->getJson('/api/accountant/cod-reconciliations')
            ->assertOk()
            ->assertJsonPath('pending.0.customer_shipping_fee', 10000)
            ->assertJsonPath('pending.0.shipping_fee', 7000)
            ->assertJsonPath('pending.0.service_fee', 5000);

        $response = $this->postJson('/api/accountant/cod-reconciliations', [
            'order_ids' => [$order->id],
            'shipping_partner_id' => $partner->id,
            'account_id' => $account->id,
            'reconciliation_date' => today()->toDateString(),
            'shipping_fee' => 10000,
            'service_fee' => 5000,
            'insurance_fee' => 0,
            'adjustment_amount' => 0,
            'payment_reference' => 'BANK-COD-001',
        ])->assertCreated()
            ->assertJsonPath('data.cod_amount', '110000.00')
            ->assertJsonPath('data.received_amount', '95000.00');

        $this->assertSame('reconciled', $order->fresh()->cod_status);
        $this->assertEquals($balanceBefore + 95000, (float) $account->fresh()->current_balance);
        $this->assertDatabaseHas('transactions', ['type' => 'receipt', 'status' => 'approved', 'amount' => 95000]);
        $this->assertDatabaseHas('transactions', [
            'id' => $response->json('data.transaction_id'),
            'sales_order_id' => null,
            'reference_type' => CodReconciliation::class,
            'reference_id' => $response->json('data.id'),
        ]);
        $this->assertDatabaseMissing('customer_debts', [
            'reference_type' => Transaction::class,
            'reference_id' => $response->json('data.transaction_id'),
        ]);
        $this->getJson('/api/accountant/transactions/'.$response->json('data.transaction_id'))
            ->assertOk()
            ->assertJsonPath('cod_reconciliation.code', $response->json('data.code'))
            ->assertJsonPath('cod_reconciliation.items.0.order.code', $order->code);
    }

    public function test_same_cod_order_cannot_be_reconciled_twice(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($user);
        $company = $user->company;
        $customer = Customer::where('company_id', $company->id)->firstOrFail();
        $account = Account::where('company_id', $company->id)->where('type', 'cash')->whereNull('bank_id')->firstOrFail();
        $partner = ShippingPartner::create(['company_id' => $company->id, 'code' => 'GHTK', 'name' => 'Giao Hàng Tiết Kiệm']);
        $order = SalesOrder::create(['company_id' => $company->id, 'customer_id' => $customer->id,
            'currency_id' => $account->currency_id, 'status' => 'completed', 'sales_channel' => 'storefront',
            'payment_method' => 'cod', 'payment_status' => 'paid', 'cod_status' => 'collected',
            'cod_amount' => 100000, 'total_amount' => 100000, 'created_by' => $user->id]);
        $payload = ['order_ids' => [$order->id], 'shipping_partner_id' => $partner->id, 'account_id' => $account->id,
            'reconciliation_date' => today()->toDateString(), 'shipping_fee' => 0];

        $this->postJson('/api/accountant/cod-reconciliations', $payload)->assertCreated();
        $this->postJson('/api/accountant/cod-reconciliations', $payload)->assertUnprocessable();
    }

    public function test_cod_delivery_requires_and_accepts_unique_shipping_information(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($user);
        $company = $user->company;
        $customer = Customer::where('company_id', $company->id)->firstOrFail();
        $currencyId = $company->default_currency->id;
        $warehouse = Warehouse::where('company_id', $company->id)->firstOrFail();
        $partner = ShippingPartner::create([
            'company_id' => $company->id, 'code' => 'VTP', 'name' => 'Viettel Post',
            'tracking_url_template' => 'https://example.test/track/{tracking_code}',
        ]);
        $order = SalesOrder::create([
            'company_id' => $company->id, 'customer_id' => $customer->id, 'currency_id' => $currencyId,
            'status' => 'partial', 'sales_channel' => 'storefront', 'payment_method' => 'cod',
            'payment_status' => 'unpaid', 'cod_status' => 'shipping', 'cod_amount' => 220000,
            'total_amount' => 220000, 'created_by' => $user->id,
        ]);
        $slip = WarehouseSlip::create([
            'company_id' => $company->id, 'type' => 'export', 'warehouse_id' => $warehouse->id,
            'sales_order_id' => $order->id, 'status' => 'approved', 'created_by' => $user->id,
            'approved_by' => $user->id, 'approved_at' => now(),
        ]);

        $this->postJson("/api/warehouse/slips/{$slip->id}/confirm-delivery")
            ->assertUnprocessable()->assertJsonValidationErrors('shipping');

        $this->putJson("/api/warehouse/slips/{$slip->id}/shipping", [
            'shipping_partner_id' => $partner->id,
            'tracking_code' => 'VTP-123456',
            'shipping_note' => 'Gọi khách trước khi giao.',
            'carrier_shipping_fee' => 18000,
            'carrier_service_fee' => 2000,
            'carrier_insurance_fee' => 1000,
        ])->assertOk()->assertJsonPath('data.tracking_code', 'VTP-123456');

        $this->postJson("/api/warehouse/slips/{$slip->id}/confirm-delivery")->assertOk();
        $this->assertDatabaseHas('sales_orders', [
            'id' => $order->id, 'status' => 'completed', 'payment_status' => 'paid',
            'cod_status' => 'collected', 'tracking_code' => 'VTP-123456',
            'carrier_shipping_fee' => 18000, 'carrier_service_fee' => 2000,
            'carrier_insurance_fee' => 1000,
        ]);
    }

    public function test_storefront_cod_order_cannot_be_exported_partially(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($user);
        $company = $user->company;
        $customer = Customer::where('company_id', $company->id)->firstOrFail();
        $warehouse = Warehouse::where('company_id', $company->id)->firstOrFail();
        $product = Product::where('company_id', $company->id)->firstOrFail();
        $currencyId = $company->default_currency->id;

        WarehouseProductStock::updateOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
            ['quantity' => 10, 'stock_value' => 0]
        );
        $order = SalesOrder::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'currency_id' => $currencyId,
            'status' => 'approved',
            'sales_channel' => 'storefront',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'total_amount' => 300000,
            'created_by' => $user->id,
        ]);
        SalesOrderItem::create([
            'sales_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 100000,
            'amount' => 300000,
        ]);

        $partialResponse = $this->actingAs($user)->postJson('/api/warehouse/slips', [
            'type' => 'export',
            'warehouse_id' => $warehouse->id,
            'sales_order_id' => $order->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $partialResponse->assertUnprocessable()->assertJsonPath(
            'message',
            'Đơn COD phải xuất đầy đủ toàn bộ số lượng còn lại trong một phiếu, không được xuất một phần.'
        );
        $this->assertDatabaseMissing('warehouse_slips', ['sales_order_id' => $order->id]);

        $this->postJson('/api/warehouse/slips', [
            'type' => 'export',
            'warehouse_id' => $warehouse->id,
            'sales_order_id' => $order->id,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertOk();
    }
}
