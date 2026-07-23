<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseToPaymentEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_to_stock_in_debt_and_supplier_payment_is_complete(): void
    {
        $this->seed(DatabaseSeeder::class);

        $purchaseUser = User::where('email', 'purchase@demo.vn')->firstOrFail();
        $warehouseUser = User::where('email', 'warehouse@demo.vn')->firstOrFail();
        $accountant = User::where('email', 'accountant@demo.vn')->firstOrFail();
        $supplier = Supplier::where('code', 'NCC-DEMO')->firstOrFail();
        $product = Product::where('sku', 'DEMO-SP-001')->firstOrFail();
        $warehouse = Warehouse::where('code', 'KHO-DEMO')->firstOrFail();
        $account = Account::where('code', 'NH-DEMO')->firstOrFail();
        $category = TransactionCategory::where('code', 'CHI_NCC')->firstOrFail();
        $currencyId = $supplier->currency_id;

        $stockBefore = (float) WarehouseProductStock::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)->value('quantity');
        $balanceBefore = (float) $account->current_balance;
        $debtBefore = (float) SupplierDebt::where('supplier_id', $supplier->id)->sum('amount');

        $orderId = $this->actingAs($purchaseUser)->postJson('/api/purchase/orders', [
            'supplier_id' => $supplier->id,
            'currency_id' => $currencyId,
            'expected_received_date' => '2026-07-30',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => 100000,
                'vat_percent' => 10,
            ]],
        ])->assertOk()->json('id');

        $this->actingAs($purchaseUser)->putJson("/api/purchase/orders/{$orderId}", [
            'supplier_id' => $supplier->id,
            'currency_id' => $currencyId,
            'expected_received_date' => '2026-07-31',
            'note' => 'Kiểm tra cập nhật đơn mua',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => 100000,
                'vat_percent' => 10,
            ]],
        ])->assertOk();

        $this->assertGreaterThan(0, (float) PurchaseOrder::findOrFail($orderId)->exchange_rate);

        $this->actingAs($purchaseUser)
            ->postJson("/api/purchase/orders/{$orderId}/approve")
            ->assertOk();
        $this->assertSame('approved', PurchaseOrder::findOrFail($orderId)->status);
        $this->actingAs($warehouseUser)
            ->getJson('/api/warehouse/orders')
            ->assertOk()
            ->assertJsonPath('data.0.items.0.product.unit.symbol', $product->unit->symbol);
        $this->assertEquals($stockBefore, WarehouseProductStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertEquals($debtBefore, SupplierDebt::where('supplier_id', $supplier->id)->sum('amount'));

        $slipId = $this->actingAs($warehouseUser)->postJson('/api/warehouse/slips', [
            'type' => 'import',
            'warehouse_id' => $warehouse->id,
            'purchase_order_id' => $orderId,
            'note' => 'Nhập kho luồng E2E',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertOk()->json('slip.id');

        // Tạo trước phiếu nhập phần còn lại nhưng chưa duyệt để bảo đảm
        // trạng thái đơn chỉ dựa trên số lượng của các phiếu đã duyệt.
        $remainingSlipId = $this->actingAs($warehouseUser)->postJson('/api/warehouse/slips', [
            'type' => 'import',
            'warehouse_id' => $warehouse->id,
            'purchase_order_id' => $orderId,
            'note' => 'Nhập phần còn lại',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertOk()->json('slip.id');

        $this->actingAs($warehouseUser)
            ->postJson("/api/warehouse/slips/{$slipId}/approve")
            ->assertOk();
        $this->assertSame('partial', PurchaseOrder::findOrFail($orderId)->status);
        $this->assertEquals(1, PurchaseOrder::findOrFail($orderId)->items()->firstOrFail()->received_quantity);
        $this->assertEquals($stockBefore + 1, WarehouseProductStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity'));

        $this->actingAs($warehouseUser)
            ->postJson("/api/warehouse/slips/{$remainingSlipId}/approve")
            ->assertOk();
        $this->assertSame('completed', PurchaseOrder::findOrFail($orderId)->status);
        $this->assertEquals(2, PurchaseOrder::findOrFail($orderId)->items()->firstOrFail()->received_quantity);
        $this->assertEquals($stockBefore + 2, WarehouseProductStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertEquals($debtBefore + 220000, SupplierDebt::where('supplier_id', $supplier->id)->sum('amount'));

        $transactionId = $this->actingAs($accountant)->postJson('/api/accountant/transactions', [
            'type' => 'payment',
            'payment_method' => 'bank_transfer',
            'amount' => 220000,
            'currency_id' => $currencyId,
            'category_id' => $category->id,
            'from_account_id' => $account->id,
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $orderId,
            'transaction_date' => '2026-07-21',
            'description' => 'Thanh toán luồng E2E',
        ])->assertOk()->json('data.id');

        $this->actingAs($accountant)
            ->postJson("/api/accountant/transactions/{$transactionId}/approve")
            ->assertOk();

        $this->assertEquals($balanceBefore - 220000, $account->fresh()->current_balance);
        $this->assertEquals($debtBefore, SupplierDebt::where('supplier_id', $supplier->id)->sum('amount'));
        $this->assertDatabaseHas('account_ledgers', ['transaction_id' => $transactionId, 'credit' => 220000]);
    }
}
