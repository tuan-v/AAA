<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\PosCoupon;
use App\Models\Product;
use App\Models\Province;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_store_directory_and_company_shop(): void
    {
        $this->seed(DatabaseSeeder::class);
        $company = $this->storeCompany();

        $this->get('/shop')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Storefront/Directory')->has('stores'));
        $this->get("/shop/{$company->storefront_slug}")->assertOk()
            ->assertInertia(fn ($page) => $page->component('Storefront/Shop')
                ->where('store.slug', $company->storefront_slug));
    }

    public function test_product_api_only_returns_products_from_the_slug_company(): void
    {
        $this->seed(DatabaseSeeder::class);
        $company = $this->storeCompany();

        $ids = collect($this->getJson("/shop/{$company->storefront_slug}/products")
            ->assertOk()->json('products.data'))->pluck('id');

        $foreignIds = Product::where('company_id', '!=', $company->id)->pluck('id');
        $this->assertEmpty($ids->intersect($foreignIds));
    }

    public function test_guest_checkout_creates_a_pending_order_for_the_slug_company(): void
    {
        $this->seed(DatabaseSeeder::class);
        $company = $this->storeCompany();
        $productId = DB::table('warehouse_product_stocks')->where('company_id', $company->id)
            ->where('quantity', '>', 0)->value('product_id');
        $this->assertNotNull($productId);

        $response = $this->postJson("/shop/{$company->storefront_slug}/checkout", [
            'customer' => ['name' => 'Khách mua online', 'phone' => '0912345678',
                'email' => 'online@example.com', 'address' => '123 Đường thử nghiệm'],
            'payment_method' => 'cod',
            'shipping_method' => 'express',
            'items' => [['product_id' => $productId, 'quantity' => 1]],
        ])->assertCreated()->assertJsonPath('message', 'Đặt hàng thành công.');

        $order = SalesOrder::where('code', $response->json('order.code'))->firstOrFail();
        $this->assertSame($company->id, $order->company_id);
        $this->assertSame('storefront', $order->sales_channel);
        $this->assertSame('pending', $order->status);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(30000.0, (float) $order->shipping_fee);

        $salesManager = User::where('company_id', $company->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Quản lý bán hàng'))
            ->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'user_id' => $salesManager->id,
            'company_id' => $company->id,
            'title' => 'Có đơn đặt hàng mới từ website',
            'category' => 'sale',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $company->owner_id,
            'title' => 'Có đơn đặt hàng mới từ website',
        ]);

        $admin = User::where('company_id', $company->id)->where('email', 'admin@demo.vn')->firstOrFail();
        $detail = $this->actingAs($admin)->getJson("/api/sale/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('shipping_fee', 30000);
        $this->assertEquals((float) $order->total_amount, (float) $detail->json('total_amount'));
    }

    public function test_checkout_rejects_a_product_owned_by_another_company(): void
    {
        $this->seed(DatabaseSeeder::class);
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

    public function test_customer_account_receives_notification_when_manager_cancels_web_order(): void
    {
        $this->seed(DatabaseSeeder::class);
        $company = $this->storeCompany();
        $productId = DB::table('warehouse_product_stocks')->where('company_id', $company->id)
            ->where('quantity', '>', 0)->value('product_id');

        $this->postJson("/shop/{$company->storefront_slug}/account/register", [
            'name' => 'Khách nhận thông báo', 'phone' => '0903333333', 'email' => 'notify@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated();
        $response = $this->postJson("/shop/{$company->storefront_slug}/checkout", [
            'customer' => ['name' => 'Khách nhận thông báo', 'phone' => '0903333333',
                'email' => 'notify@example.com', 'address' => 'Địa chỉ nhận thông báo'],
            'payment_method' => 'cod', 'shipping_method' => 'standard',
            'items' => [['product_id' => $productId, 'quantity' => 1]],
        ])->assertCreated();
        $order = SalesOrder::where('code', $response->json('order.code'))->firstOrFail();

        $manager = User::where('company_id', $company->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Quản lý bán hàng'))
            ->firstOrFail();
        $this->actingAs($manager)->postJson("/api/sale/orders/{$order->id}/cancel", [
            'reason' => 'Cửa hàng tạm hết hàng',
        ])->assertOk();

        $this->getJson("/shop/{$company->storefront_slug}/account/notifications/unread-count")
            ->assertOk()
            ->assertJsonPath('count', 1);

        $this->getJson("/shop/{$company->storefront_slug}/account/notifications")
            ->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.data.order_code', $order->code)
            ->assertJsonPath('notifications.0.data.cancellation_reason', 'Cửa hàng tạm hết hàng');
        $this->get("/shop/{$company->storefront_slug}/my-account/notifications")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Storefront/Notifications')
                ->where('store.slug', $company->storefront_slug));
        $history = $this->getJson("/shop/{$company->storefront_slug}/account/notification-history?status=unread")
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'notifications.data');
        $notificationId = $history->json('notifications.data.0.id');
        $this->postJson("/shop/{$company->storefront_slug}/account/notifications/{$notificationId}/read")
            ->assertOk();
        $this->getJson("/shop/{$company->storefront_slug}/account/notification-history?status=unread")
            ->assertOk()
            ->assertJsonPath('unread_count', 0)
            ->assertJsonCount(0, 'notifications.data');
        $this->deleteJson("/shop/{$company->storefront_slug}/account/notifications/{$notificationId}")
            ->assertOk();
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $company->owner_id,
            'data->event_type' => 'storefront_order_cancelled_by_manager',
        ]);
    }

    public function test_customer_account_is_separate_and_can_track_its_storefront_order(): void
    {
        $this->seed(DatabaseSeeder::class);
        $company = $this->storeCompany();
        $productId = DB::table('warehouse_product_stocks')->where('company_id', $company->id)
            ->where('quantity', '>', 0)->value('product_id');

        $this->postJson("/shop/{$company->storefront_slug}/account/register", [
            'name' => 'Khách có tài khoản', 'phone' => '0908888888', 'email' => 'customer@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonPath('account.email', 'customer@example.com');

        $this->postJson("/shop/{$company->storefront_slug}/checkout", [
            'customer' => ['name' => 'Người nhận khác', 'phone' => '0909999999',
                'email' => 'customer@example.com', 'address' => '456 Đường Online'],
            'payment_method' => 'cod', 'shipping_method' => 'express',
            'items' => [['product_id' => $productId, 'quantity' => 1]],
        ])->assertCreated();

        $this->getJson("/shop/{$company->storefront_slug}/account/orders")
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.shipping_method', 'express');
        $this->assertDatabaseHas('customer_accounts', ['company_id' => $company->id, 'email' => 'customer@example.com']);
        $this->assertDatabaseHas('customers', ['company_id' => $company->id, 'name' => 'Khách có tài khoản', 'phone' => '0908888888']);
        $this->assertDatabaseHas('sales_orders', [
            'company_id' => $company->id, 'sales_channel' => 'storefront',
            'recipient_name' => 'Người nhận khác', 'recipient_phone' => '0909999999',
        ]);

        $code = SalesOrder::where('company_id', $company->id)->where('sales_channel', 'storefront')->latest('id')->value('code');
        $this->get("/shop/{$company->storefront_slug}/my-account/orders/{$code}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Storefront/OrderDetail')
                ->where('order.code', $code)
                ->where('order.recipient.name', 'Người nhận khác')
                ->where('order.recipient.phone', '0909999999')
                ->has('order.items', 1));

        $province = Province::firstOrFail();
        $ward = Ward::where('province_id', $province->id)->firstOrFail();
        $address = $this->postJson("/shop/{$company->storefront_slug}/account/addresses", [
            'label' => 'Nhà riêng', 'recipient_name' => 'Người nhận A', 'phone' => '0909999999',
            'province_id' => $province->id, 'ward_id' => $ward->id,
            'address_detail' => 'Số 10', 'is_default' => true,
        ])->assertCreated()->json('address');
        $this->putJson("/shop/{$company->storefront_slug}/account/addresses/{$address['id']}", [
            'label' => 'Văn phòng', 'recipient_name' => 'Người nhận B', 'phone' => '0909999999',
            'province_id' => $province->id, 'ward_id' => $ward->id,
            'address_detail' => 'Số 20', 'is_default' => true,
        ])->assertOk()->assertJsonPath('address.label', 'Văn phòng');
        $this->assertDatabaseHas('customers', [
            'company_id' => $company->id,
            'email' => 'customer@example.com',
            'province_id' => $province->id,
            'ward_id' => $ward->id,
            'address_detail' => 'Số 20',
        ]);
        $this->postJson("/shop/{$company->storefront_slug}/account/orders/{$code}/cancel", ['reason' => 'Tôi muốn đổi sản phẩm khác'])
            ->assertOk();
        $this->assertDatabaseHas('sales_orders', ['code' => $code, 'status' => 'cancelled',
            'cancellation_reason' => 'Tôi muốn đổi sản phẩm khác']);
        $this->getJson("/shop/{$company->storefront_slug}/account/orders")
            ->assertOk()
            ->assertJsonPath('data.0.repurchasable', true)
            ->assertJsonPath('data.0.items.0.repurchase.available', true)
            ->assertJsonPath('data.0.items.0.repurchase.id', $productId);
        $salesManager = User::where('company_id', $company->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Quản lý bán hàng'))
            ->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'user_id' => $salesManager->id,
            'company_id' => $company->id,
            'title' => 'Khách hàng đã hủy đơn trên website',
            'category' => 'sale',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $company->owner_id,
            'title' => 'Khách hàng đã hủy đơn trên website',
        ]);
    }

    public function test_storefront_coupon_is_recalculated_on_the_server(): void
    {
        $this->seed(DatabaseSeeder::class);
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
