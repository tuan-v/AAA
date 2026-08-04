<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\PosCoupon;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_store_directory_and_company_shop(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $company = $this->storeCompany();

        $this->get('/shop')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Storefront/Directory')->has('stores'));
        $this->get("/shop/{$company->storefront_slug}")->assertOk()
            ->assertInertia(fn ($page) => $page->component('Storefront/Shop')
                ->where('store.slug', $company->storefront_slug));
    }

    public function test_product_api_only_returns_products_from_the_slug_company(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $company = $this->storeCompany();

        $ids = collect($this->getJson("/shop/{$company->storefront_slug}/products")
            ->assertOk()->json('products.data'))->pluck('id');

        $foreignIds = Product::where('company_id', '!=', $company->id)->pluck('id');
        $this->assertEmpty($ids->intersect($foreignIds));
    }

    public function test_guest_checkout_creates_a_pending_order_for_the_slug_company(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $company = $this->storeCompany();
        $productId = DB::table('warehouse_product_stocks')->where('company_id', $company->id)
            ->where('quantity', '>', 0)->value('product_id');
        $this->assertNotNull($productId);

        $response = $this->postJson("/shop/{$company->storefront_slug}/checkout", [
            'customer' => ['name' => 'Khách mua online', 'phone' => '0912345678',
                'email' => 'online@example.com', 'address' => '123 Đường thử nghiệm'],
            'payment_method' => 'cod',
            'shipping_method' => 'standard',
            'items' => [['product_id' => $productId, 'quantity' => 1]],
        ])->assertCreated()->assertJsonPath('message', 'Đặt hàng thành công.');

        $order = SalesOrder::where('code', $response->json('order.code'))->firstOrFail();
        $this->assertSame($company->id, $order->company_id);
        $this->assertSame('storefront', $order->sales_channel);
        $this->assertSame('pending', $order->status);
        $this->assertSame(1, $order->items()->count());
    }

    public function test_checkout_rejects_a_product_owned_by_another_company(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $company = $this->storeCompany();
        $owner = User::factory()->create();
        $other = Company::create(['name' => 'Cửa hàng khác', 'owner_id' => $owner->id]);
        $foreignProduct = Product::where('company_id', $company->id)->firstOrFail()->replicate();
        $foreignProduct->company_id = $other->id;
        $foreignProduct->sku = 'FOREIGN-'.uniqid();
        $foreignProduct->save();

        $this->postJson("/shop/{$company->storefront_slug}/checkout", [
            'customer' => ['name' => 'Khách thử', 'phone' => '0999999999', 'address' => 'Địa chỉ'],
            'payment_method' => 'cod',
            'shipping_method' => 'standard',
            'items' => [['product_id' => $foreignProduct->id, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');
    }

    public function test_customer_account_is_separate_and_can_track_its_storefront_order(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $company = $this->storeCompany();
        $productId = DB::table('warehouse_product_stocks')->where('company_id', $company->id)
            ->where('quantity', '>', 0)->value('product_id');

        $this->postJson("/shop/{$company->storefront_slug}/account/register", [
            'name' => 'Khách có tài khoản', 'phone' => '0908888888', 'email' => 'customer@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonPath('account.email', 'customer@example.com');

        $this->postJson("/shop/{$company->storefront_slug}/checkout", [
            'customer' => ['name' => 'Khách có tài khoản', 'phone' => '0908888888',
                'email' => 'customer@example.com', 'address' => '456 Đường Online'],
            'payment_method' => 'cod', 'shipping_method' => 'express',
            'items' => [['product_id' => $productId, 'quantity' => 1]],
        ])->assertCreated();

        $this->getJson("/shop/{$company->storefront_slug}/account/orders")
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.shipping_method', 'express');
        $this->assertDatabaseHas('customer_accounts', ['company_id' => $company->id, 'email' => 'customer@example.com']);
        $this->assertDatabaseHas('sales_orders', ['company_id' => $company->id, 'sales_channel' => 'storefront']);

        $code = SalesOrder::where('company_id', $company->id)->where('sales_channel', 'storefront')->latest('id')->value('code');
        $this->get("/shop/{$company->storefront_slug}/my-account/orders/{$code}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Storefront/OrderDetail')
                ->where('order.code', $code)
                ->where('order.recipient.phone', '0908888888')
                ->has('order.items', 1));
        $this->postJson("/shop/{$company->storefront_slug}/account/orders/{$code}/cancel", ['reason' => 'Tôi muốn đổi sản phẩm khác'])
            ->assertOk();
        $this->assertDatabaseHas('sales_orders', ['code' => $code, 'status' => 'cancelled',
            'cancellation_reason' => 'Tôi muốn đổi sản phẩm khác']);
    }

    public function test_storefront_coupon_is_recalculated_on_the_server(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $company = $this->storeCompany();
        $productId = DB::table('warehouse_product_stocks')->where('company_id', $company->id)
            ->where('quantity', '>', 0)->value('product_id');
        PosCoupon::create(['company_id' => $company->id, 'code' => 'WEB10', 'name' => 'Giảm web 10%',
            'type' => 'percent', 'value' => 10, 'minimum_order_amount' => 0, 'is_active' => true]);

        $response = $this->postJson("/shop/{$company->storefront_slug}/checkout", [
            'customer' => ['name' => 'Khách voucher', 'phone' => '0907777777', 'address' => 'Địa chỉ voucher'],
            'payment_method' => 'cod', 'shipping_method' => 'standard', 'coupon_code' => 'WEB10',
            'items' => [['product_id' => $productId, 'quantity' => 1]],
        ])->assertCreated();
        $order = SalesOrder::where('code', $response->json('order.code'))->firstOrFail();
        $this->assertGreaterThan(0, (float) $order->discount_amount);
        $this->assertSame(10.0, (float) $order->items()->value('vat_percent'));
        $this->assertSame(round((float) $order->subtotal * 0.1, 2), (float) $order->vat_amount);
        $this->assertSame(
            round((float) $order->subtotal + (float) $order->vat_amount - (float) $order->discount_amount, 2),
            (float) $order->total_amount
        );
    }

    private function storeCompany(): Company
    {
        $company = Company::firstOrFail();
        $company->update([
            'storefront_slug' => $company->storefront_slug ?: 'cua-hang-demo',
            'storefront_enabled' => true,
        ]);

        return $company->fresh();
    }
}
