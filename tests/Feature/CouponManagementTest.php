<?php

namespace Tests\Feature;

use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\PosCoupon;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\CouponService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CouponManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($this->user);
    }

    public function test_authorized_user_can_manage_a_multi_channel_coupon(): void
    {
        $payload = [
            'code' => 'summer10', 'name' => 'Ưu đãi mùa hè', 'description' => 'Dùng chung ba kênh',
            'type' => 'percent', 'value' => 10, 'minimum_order_amount' => 100000,
            'maximum_discount' => 50000, 'status' => 'active', 'channels' => ['pos', 'web', 'admin'],
            'usage_limit' => 100, 'usage_limit_per_customer' => 1,
        ];

        $id = $this->postJson('/api/sale/coupons', $payload)->assertCreated()
            ->assertJsonPath('data.code', 'SUMMER10')->json('data.id');

        $this->getJson('/api/sale/coupons?search=SUMMER')->assertOk()->assertJsonPath('total', 1);
        $this->putJson("/api/sale/coupons/{$id}", [...$payload, 'status' => 'paused'])
            ->assertOk()->assertJsonPath('data.status', 'paused');
        $this->deleteJson("/api/sale/coupons/{$id}")->assertOk();
    }

    public function test_coupon_enforces_channel_and_customer_limit_and_releases_usage(): void
    {
        $customer = Customer::where('company_id', $this->user->company_id)->firstOrFail();
        $coupon = PosCoupon::create([
            'company_id' => $this->user->company_id, 'code' => 'WEBONLY', 'name' => 'Chỉ website',
            'type' => 'fixed', 'value' => 20000, 'minimum_order_amount' => 0,
            'status' => 'active', 'is_active' => true, 'channels' => ['web'], 'usage_limit_per_customer' => 1,
        ]);
        $service = app(CouponService::class);

        try {
            $service->resolve($this->user->company_id, 'WEBONLY', 'pos', 100000, $customer->id);
            $this->fail('Phiếu sai kênh phải bị từ chối.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('coupon_code', $e->errors());
        }

        $order = SalesOrder::create([
            'company_id' => $this->user->company_id, 'customer_id' => $customer->id,
            'currency_id' => $customer->currency_id, 'subtotal' => 100000, 'vat_amount' => 10000,
            'total_amount' => 90000, 'status' => 'pending', 'sales_channel' => 'storefront',
            'created_by' => $this->user->id,
        ]);
        ['discount' => $discount] = $service->resolve($this->user->company_id, 'WEBONLY', 'web', 100000, $customer->id);
        $service->applyToOrder($order, $coupon, $discount, 'web');

        $this->assertDatabaseHas('coupon_usages', ['sales_order_id' => $order->id, 'discount_amount' => 20000]);
        $this->assertSame('WEBONLY', $order->fresh()->coupon_code_snapshot);
        $this->expectException(ValidationException::class);
        try {
            $service->resolve($this->user->company_id, 'WEBONLY', 'web', 100000, $customer->id);
        } finally {
            $service->reverseForOrder($order);
            $this->assertNotNull(CouponUsage::where('sales_order_id', $order->id)->value('reversed_at'));
            $this->assertSame(0, $coupon->fresh()->used_count);
        }
    }

    public function test_personal_coupon_is_auto_coded_reserved_then_redeemed_for_assigned_customer(): void
    {
        $assignedCustomer = Customer::where('company_id', $this->user->company_id)->firstOrFail();
        $otherCustomer = $assignedCustomer->replicate();
        $otherCustomer->code = 'KH-OTHER';
        $otherCustomer->name = 'Khách hàng khác';
        $otherCustomer->email = 'other-customer@example.test';
        $otherCustomer->phone = '0900000002';
        $otherCustomer->save();

        $couponId = $this->postJson('/api/sale/coupons', [
            'code' => '', 'name' => 'Phiếu khách thân thiết', 'type' => 'percent', 'value' => 15,
            'minimum_order_amount' => 100000, 'maximum_discount' => 50000, 'status' => 'active',
            'channels' => ['pos', 'web'], 'scope' => 'personal', 'customer_ids' => [$assignedCustomer->id],
        ])->assertCreated()->assertJsonPath('data.code', 'PGG0001')->json('data.id');

        $coupon = PosCoupon::findOrFail($couponId);
        $service = app(CouponService::class);
        $this->assertFalse($service->eligibility($coupon, 'pos', 200000, $otherCustomer->id)['eligible']);
        $this->assertTrue($service->eligibility($coupon, 'pos', 200000, $assignedCustomer->id)['eligible']);

        $order = SalesOrder::create([
            'company_id' => $this->user->company_id, 'customer_id' => $assignedCustomer->id,
            'currency_id' => $assignedCustomer->currency_id, 'subtotal' => 200000, 'vat_amount' => 20000,
            'total_amount' => 190000, 'status' => 'pending', 'sales_channel' => 'storefront',
            'created_by' => $this->user->id,
        ]);
        $service->applyToOrder($order, $coupon, 30000, 'web', false);

        $this->assertDatabaseHas('coupon_usages', ['sales_order_id' => $order->id, 'status' => 'reserved']);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $service->redeemForOrder($order);
        $this->assertDatabaseHas('coupon_usages', ['sales_order_id' => $order->id, 'status' => 'redeemed']);
        $this->assertDatabaseHas('coupon_customer_assignments', ['coupon_id' => $coupon->id, 'customer_id' => $assignedCustomer->id, 'status' => 'redeemed']);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }
}
