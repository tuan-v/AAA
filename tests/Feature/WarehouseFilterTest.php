<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarehouseFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouses_can_be_filtered_by_name_code_and_inventory_value(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->assertGreaterThan(0, (float) DB::table('warehouse_product_stocks')->sum('stock_value'));

        $this->actingAs($owner)
            ->getJson('/api/warehouses?search=KHO-DEMO')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Kho trung tâm');

        $this->actingAs($owner)
            ->getJson('/api/warehouses?min_inventory_value=1&max_inventory_value=1000000000')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($owner)
            ->getJson('/api/warehouses?min_inventory_value=1000000001')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_max_inventory_value_must_not_be_less_than_minimum(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();

        $this->actingAs($owner)
            ->getJson('/api/warehouses?min_inventory_value=1000&max_inventory_value=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_inventory_value');
    }

    public function test_purchase_account_can_load_warehouse_options_and_filter_products(): void
    {
        $this->seed(DatabaseSeeder::class);

        $purchaseUser = User::where('email', 'purchase@demo.vn')->firstOrFail();
        $warehouse = Warehouse::where('code', 'KHO-DEMO')->firstOrFail();

        $this->actingAs($purchaseUser)
            ->getJson('/api/warehouses/all')
            ->assertOk()
            ->assertJsonFragment(['id' => $warehouse->id, 'name' => 'Kho trung tâm']);

        $this->actingAs($purchaseUser)
            ->getJson('/api/purchase/products?warehouse_id='.$warehouse->id)
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->actingAs($purchaseUser)
            ->getJson('/api/warehouses')
            ->assertForbidden();
    }
}
