<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_out_of_stock_product_is_hidden_from_warehouse_and_sale_but_kept_for_purchase(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($owner);

        $product = Product::unguarded(fn () => Product::create([
            'company_id' => $owner->company_id,
            'name' => 'Sản phẩm đã hết tồn',
            'sku' => 'OUT-OF-STOCK-TEST',
            'category_id' => Category::firstOrFail()->id,
            'unit_id' => Unit::firstOrFail()->id,
            'type' => 'hang_hoa',
            'quantity' => 0,
            'purchase_price' => 100,
            'sell_price' => 120,
            'color' => 'Mặc định',
            'status' => 'active',
        ]));

        $purchase = $this->getJson('/api/purchase/products?per_page=100')->assertOk();
        $warehouse = $this->getJson('/api/warehouse/products?per_page=100')->assertOk();
        $saleSelect = $this->getJson('/api/products/for-select')->assertOk();

        $this->assertContains($product->id, collect($purchase->json('data'))->pluck('id')->all());
        $this->assertNotContains($product->id, collect($warehouse->json('data'))->pluck('id')->all());
        $this->assertNotContains($product->id, collect($saleSelect->json())->pluck('id')->all());
    }

    public function test_used_product_update_returns_a_clear_business_message(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $usedProduct = Product::whereHas('stocks')->firstOrFail();

        $this->actingAs($owner)
            ->putJson("/api/warehouse/products/{$usedProduct->id}", [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Sản phẩm đã phát sinh giao dịch, không thể chỉnh sửa. Bạn chỉ có thể khóa hoặc mở khóa.');
    }

    public function test_sales_account_can_load_products_for_sale_order_selector(): void
    {
        $this->seed(DatabaseSeeder::class);
        $salesUser = User::where('email', 'sales@demo.vn')->firstOrFail();

        $this->actingAs($salesUser)
            ->getJson('/api/products/for-select')
            ->assertOk();

        $this->getJson('/api/currencies/for-select')
            ->assertOk();
    }
}
