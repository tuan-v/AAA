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

    public function test_inventory_movement_ledger_filters_an_inclusive_date_range(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $stock = WarehouseProductStock::firstOrFail();
        $this->actingAs($owner);

        $oldMovement = InventoryMovement::create([
            'company_id' => $stock->company_id,
            'warehouse_id' => $stock->warehouse_id,
            'product_id' => $stock->product_id,
            'type' => 'import',
            'quantity' => 1,
            'created_by' => $owner->id,
        ]);
        $oldMovement->forceFill(['created_at' => '2026-07-01 08:00:00'])->save();

        $inRangeMovement = InventoryMovement::create([
            'company_id' => $stock->company_id,
            'warehouse_id' => $stock->warehouse_id,
            'product_id' => $stock->product_id,
            'type' => 'export',
            'quantity' => 1,
            'created_by' => $owner->id,
        ]);
        $inRangeMovement->forceFill(['created_at' => '2026-07-15 18:30:00'])->save();

        $response = $this->getJson(
            '/api/warehouse/inventory-movements?date_from=2026-07-15&date_to=2026-07-15'
        )->assertOk();

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($inRangeMovement->id));
        $this->assertFalse($ids->contains($oldMovement->id));

        $this->getJson(
            '/api/warehouse/inventory-movements?date_from=2026-07-16&date_to=2026-07-15'
        )->assertUnprocessable()
            ->assertJsonValidationErrors('date_to');

        $this->getJson(
            '/api/warehouse/inventory-movements?date_to='.now()->addDay()->toDateString()
        )->assertUnprocessable()
            ->assertJsonValidationErrors('date_to');

        $this->getJson(
            '/api/warehouse/inventory-movements?date_from='.now()->addDay()->toDateString()
        )->assertUnprocessable()
            ->assertJsonValidationErrors('date_from');
    }
}
