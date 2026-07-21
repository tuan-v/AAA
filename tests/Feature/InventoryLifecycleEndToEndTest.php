<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryLifecycleEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_transfer_export_and_both_payments_keep_inventory_and_debts_consistent(): void
    {
        $this->seed(DatabaseSeeder::class);

        $purchaseUser = User::where('email', 'purchase@demo.vn')->firstOrFail();
        $warehouseUser = User::where('email', 'warehouse@demo.vn')->firstOrFail();
        $salesUser = User::where('email', 'sales@demo.vn')->firstOrFail();
        $accountant = User::where('email', 'accountant@demo.vn')->firstOrFail();
        $supplier = Supplier::where('code', 'NCC-DEMO')->firstOrFail();
        $customer = Customer::where('code', 'KH-DEMO')->firstOrFail();
        $product = Product::where('sku', 'DEMO-SP-001')->firstOrFail();
        $source = Warehouse::where('code', 'KHO-DEMO')->firstOrFail();
        $destination = $source->replicate();
        $destination->code = 'KHO-E2E';
        $destination->name = 'Kho nhận E2E';
        $destination->total_inventory_value = 0;
        $destination->save();

        $currencyId = $supplier->currency_id;
        $sourceStock = WarehouseProductStock::where('warehouse_id', $source->id)
            ->where('product_id', $product->id)->firstOrFail();
        $initialQuantity = (float) $sourceStock->quantity;
        $initialValue = (float) $sourceStock->stock_value;
        $supplierDebtBefore = (float) SupplierDebt::where('supplier_id', $supplier->id)->sum('amount');
        $customerDebtBefore = (float) CustomerDebt::where('customer_id', $customer->id)->sum('amount');

        $purchaseOrderId = $this->actingAs($purchaseUser)->postJson('/api/purchase/orders', [
            'supplier_id' => $supplier->id,
            'currency_id' => $currencyId,
            'expected_received_date' => '2026-07-30',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 4,
                'price' => 100000,
                'vat_percent' => 10,
            ]],
        ])->assertOk()->json('id');
        $this->actingAs($purchaseUser)->postJson("/api/purchase/orders/{$purchaseOrderId}/approve")->assertOk();

        $importSlipId = $this->actingAs($warehouseUser)->postJson('/api/warehouse/slips', [
            'type' => 'import',
            'warehouse_id' => $source->id,
            'purchase_order_id' => $purchaseOrderId,
            'note' => 'Nhập kho E2E',
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ])->assertOk()->json('slip.id');
        $this->actingAs($warehouseUser)->postJson("/api/warehouse/slips/{$importSlipId}/approve")->assertOk();

        $this->assertSame('completed', PurchaseOrder::findOrFail($purchaseOrderId)->status);
        $this->assertEquals($initialQuantity + 4, $sourceStock->fresh()->quantity);
        $this->assertEquals($initialValue + 400000, $sourceStock->fresh()->stock_value);
        $this->assertEquals($supplierDebtBefore + 440000, SupplierDebt::where('supplier_id', $supplier->id)->sum('amount'));

        $transferId = $this->actingAs($warehouseUser)->postJson('/api/warehouse/transfers', [
            'from_warehouse_id' => $source->id,
            'to_warehouse_id' => $destination->id,
            'note' => 'Chuyển kho E2E',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertCreated()->json('data.id');
        $this->actingAs($warehouseUser)->postJson("/api/warehouse/transfers/{$transferId}/approve")->assertOk();

        $sourceAfterTransfer = $sourceStock->fresh();
        $destinationStock = WarehouseProductStock::where('warehouse_id', $destination->id)
            ->where('product_id', $product->id)->firstOrFail();
        $this->assertEquals($initialQuantity + 2, $sourceAfterTransfer->quantity);
        $this->assertEquals(2, $destinationStock->quantity);
        $this->assertEqualsWithDelta(
            $initialValue + 400000,
            (float) $sourceAfterTransfer->stock_value + (float) $destinationStock->stock_value,
            0.01
        );

        $saleOrderId = $this->actingAs($salesUser)->postJson('/api/sale/orders', [
            'customer_id' => $customer->id,
            'currency_id' => $currencyId,
            'province_id' => $customer->province_id,
            'ward_id' => $customer->ward_id,
            'address_detail' => $customer->address_detail,
            'expected_delivery_date' => '2026-07-31',
            'subtotal' => 600000,
            'vat_amount' => 60000,
            'total_amount' => 660000,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 300000,
                'vat_percent' => 10,
                'amount' => 600000,
            ]],
        ])->assertOk()->json('id');
        $this->actingAs($salesUser)->postJson("/api/sale/orders/{$saleOrderId}/approve")->assertOk();

        $exportSlipId = $this->actingAs($warehouseUser)->postJson('/api/warehouse/slips', [
            'type' => 'export',
            'warehouse_id' => $destination->id,
            'sales_order_id' => $saleOrderId,
            'note' => 'Xuất kho E2E',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertOk()->json('slip.id');
        $this->actingAs($warehouseUser)->postJson("/api/warehouse/slips/{$exportSlipId}/approve")->assertOk();

        $this->assertSame('completed', SalesOrder::findOrFail($saleOrderId)->status);
        $this->assertEquals(0, $destinationStock->fresh()->quantity);
        $this->assertEquals(0, $destinationStock->fresh()->stock_value);
        $this->assertEquals($customerDebtBefore + 660000, CustomerDebt::where('customer_id', $customer->id)->sum('amount'));
        $this->assertEqualsCanonicalizing(
            ['export', 'import', 'transfer_in', 'transfer_out'],
            InventoryMovement::where('product_id', $product->id)->pluck('type')->unique()->values()->all()
        );

        $bank = Account::where('code', 'NH-DEMO')->firstOrFail();
        $cash = Account::where('code', 'TM-DEMO')->firstOrFail();
        $bankBefore = (float) $bank->current_balance;
        $cashBefore = (float) $cash->current_balance;
        $paymentCategory = TransactionCategory::where('code', 'CHI_NCC')->firstOrFail();
        $receiptCategory = TransactionCategory::where('code', 'THU_KH')->firstOrFail();

        $paymentId = $this->actingAs($accountant)->postJson('/api/accountant/transactions', [
            'type' => 'payment', 'payment_method' => 'bank_transfer', 'amount' => 440000,
            'currency_id' => $currencyId, 'category_id' => $paymentCategory->id,
            'from_account_id' => $bank->id, 'supplier_id' => $supplier->id,
            'purchase_order_id' => $purchaseOrderId, 'transaction_date' => '2026-07-21',
        ])->assertOk()->json('data.id');
        $this->actingAs($accountant)->postJson("/api/accountant/transactions/{$paymentId}/approve")->assertOk();

        $receiptId = $this->actingAs($accountant)->postJson('/api/accountant/transactions', [
            'type' => 'receipt', 'payment_method' => 'cash', 'amount' => 660000,
            'currency_id' => $currencyId, 'category_id' => $receiptCategory->id,
            'to_account_id' => $cash->id, 'customer_id' => $customer->id,
            'sales_order_id' => $saleOrderId, 'transaction_date' => '2026-07-21',
        ])->assertOk()->json('data.id');
        $this->actingAs($accountant)->postJson("/api/accountant/transactions/{$receiptId}/approve")->assertOk();

        $this->assertEquals($bankBefore - 440000, $bank->fresh()->current_balance);
        $this->assertEquals($cashBefore + 660000, $cash->fresh()->current_balance);
        $this->assertEquals($supplierDebtBefore, SupplierDebt::where('supplier_id', $supplier->id)->sum('amount'));
        $this->assertEquals($customerDebtBefore, CustomerDebt::where('customer_id', $customer->id)->sum('amount'));
        $this->assertDatabaseHas('account_ledgers', ['transaction_id' => $paymentId, 'credit' => 440000]);
        $this->assertDatabaseHas('account_ledgers', ['transaction_id' => $receiptId, 'debit' => 660000]);
    }
}
