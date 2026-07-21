<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use App\Models\WarehouseTransfer;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAccountingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_preserves_inventory_value_and_writes_two_ledger_entries(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($owner);
        $source = WarehouseProductStock::where('quantity', '>', 2)->firstOrFail();
        $targetWarehouse = Warehouse::where('company_id', $source->company_id)
            ->whereKeyNot($source->warehouse_id)->first();
        $targetWarehouse ??= Warehouse::unguarded(fn () => Warehouse::create([
            'company_id' => $source->company_id, 'name' => 'Kho đích kiểm thử',
            'address_id' => $source->warehouse->address_id,
            'address_detail' => 'Kiểm thử', 'status' => 'active',
        ]));

        $target = WarehouseProductStock::firstOrCreate([
            'company_id' => $source->company_id, 'warehouse_id' => $targetWarehouse->id,
            'product_id' => $source->product_id,
        ], ['quantity' => 0, 'stock_value' => 0]);
        $valueBefore = (float) $source->stock_value + (float) $target->stock_value;

        $transfer = WarehouseTransfer::create([
            'company_id' => $source->company_id, 'code' => 'TRF-TEST',
            'from_warehouse_id' => $source->warehouse_id, 'to_warehouse_id' => $targetWarehouse->id,
            'created_by' => $owner->id,
        ]);
        $transfer->items()->create(['product_id' => $source->product_id, 'quantity' => 2]);

        $this->postJson("/api/warehouse/transfers/{$transfer->id}/approve")->assertOk();

        $valueAfter = (float) $source->fresh()->stock_value + (float) $target->fresh()->stock_value;
        $this->assertEqualsWithDelta($valueBefore, $valueAfter, 0.01);
        $this->assertSame(2, InventoryMovement::where('reference_type', WarehouseTransfer::class)
            ->where('reference_id', $transfer->id)->count());
        $this->assertDatabaseHas('inventory_movements', ['type' => 'transfer_out']);
        $this->assertDatabaseHas('inventory_movements', ['type' => 'transfer_in']);
    }
}
