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

    public function test_used_category_and_unit_are_marked_and_cannot_be_changed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $product = Product::whereNotNull('category_id')->whereNotNull('unit_id')->firstOrFail();

        $this->actingAs($owner);

        $categories = $this->getJson('/api/purchase/categories?per_page=100')->assertOk();
        $units = $this->getJson('/api/purchase/units?per_page=100')->assertOk();

        $this->assertTrue((bool) collect($categories->json('data'))->firstWhere('id', $product->category_id)['is_used']);
        $this->assertTrue((bool) collect($units->json('data'))->firstWhere('id', $product->unit_id)['is_used']);

        $this->putJson('/api/purchase/categories/'.$product->category_id, [])->assertStatus(422);
        $this->patchJson('/api/purchase/categories/'.$product->category_id.'/status')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Danh mục đã được sử dụng nên không thể thay đổi trạng thái.');

        $this->putJson('/api/purchase/units/'.$product->unit_id, [])->assertStatus(422);
        $this->patchJson('/api/purchase/units/'.$product->unit_id.'/status')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Đơn vị tính đã được sử dụng nên không thể thay đổi trạng thái.');
    }

    public function test_parent_category_with_children_is_used_and_cannot_be_changed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $parent = Category::whereHas('children')->firstOrFail();

        $this->actingAs($owner);

        $response = $this->getJson('/api/purchase/categories?per_page=100')->assertOk();
        $parentRow = collect($response->json('data'))->firstWhere('id', $parent->id);

        $this->assertTrue((bool) $parentRow['is_used']);
        $this->putJson('/api/purchase/categories/'.$parent->id, [])->assertStatus(422);
        $this->deleteJson('/api/purchase/categories/'.$parent->id)->assertStatus(422);
        $this->patchJson('/api/purchase/categories/'.$parent->id.'/status')->assertStatus(422);
    }

    public function test_product_requires_a_leaf_category_and_used_category_cannot_become_parent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $parent = Category::whereHas('children')->firstOrFail();
        $usedLeaf = Category::whereHas('products')->whereDoesntHave('children')->firstOrFail();
        $unit = Unit::where('company_id', $owner->company_id)->firstOrFail();

        $this->actingAs($owner);

        $select = $this->getJson('/api/purchase/categories/select?active_only=1')->assertOk();
        $parentOption = collect($select->json())->firstWhere('id', $parent->id);
        $this->assertFalse((bool) $parentOption['is_leaf']);

        $this->postJson('/api/purchase/products', [
            'name' => 'Sản phẩm kiểm tra danh mục cha',
            'sku' => 'PARENT-CATEGORY-TEST',
            'category_id' => $parent->id,
            'unit_id' => $unit->id,
            'type' => 'hang_hoa',
            'purchase_price' => 100,
            'sell_price' => 120,
            'quantity' => 0,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('category_id');

        $this->postJson('/api/purchase/categories', [
            'name' => 'Danh mục con không hợp lệ',
            'parent_id' => $usedLeaf->id,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('parent_id');
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
