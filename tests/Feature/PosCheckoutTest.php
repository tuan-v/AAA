<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CompanyCurrencyRate;
use App\Models\Currency;
use App\Models\PosCoupon;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\WarehouseProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private WarehouseProductStock $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($this->user);
        $this->stock = WarehouseProductStock::query()
            ->where('company_id', $this->user->company_id)
            ->where('quantity', '>', 0)
            ->firstOrFail();
        $this->customer = Customer::query()->where('company_id', $this->user->company_id)->firstOrFail();
        $this->customer->orders()->delete();
    }

    private function payload(float $paidAmount): array
    {
        return [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->stock->warehouse_id,
            'payment_method' => 'cash',
            'invoice_type' => 'retail',
            'paid_amount' => $paidAmount,
            'items' => [[
                'product_id' => $this->stock->product_id,
                'quantity' => 1,
                'vat_percent' => 0,
            ]],
        ];
    }

    public function test_new_customer_can_create_a_fully_paid_pos_order(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');

        $orderId = $this->actingAs($this->user)
            ->postJson('/api/sale/pos/orders', $this->payload($price))
            ->assertCreated()
            ->assertJsonPath('data.code', SalesOrder::latest('id')->value('code'))
            ->json('data.id');

        $this->assertDatabaseHas('sales_orders', [
            'customer_id' => $this->customer->id,
            'sales_channel' => 'pos',
            'paid_amount' => $price,
        ]);

        $this->getJson('/api/saleorders/warehouse')
            ->assertOk()
            ->assertJsonMissing(['id' => $orderId]);

        $this->getJson('/api/sale/orders')
            ->assertOk()
            ->assertJsonFragment(['id' => $orderId, 'sales_channel' => 'pos']);
    }

    public function test_checkout_completes_the_existing_pending_draft(): void
    {
        $draftId = $this->postJson('/api/sale/pos/drafts')
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->json('data.id');
        $this->getJson('/api/sale/orders')
            ->assertOk()
            ->assertJsonMissing(['id' => $draftId]);
        $price = (float) $this->stock->product()->value('sell_price');
        $payload = $this->payload($price);
        $payload['draft_id'] = $draftId;

        $this->postJson('/api/sale/pos/orders', $payload)
            ->assertCreated()
            ->assertJsonPath('data.id', $draftId)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.invoice_type', 'retail');

        $this->assertSame(1, SalesOrder::whereKey($draftId)->count());
        $this->assertDatabaseHas('sales_orders', ['id' => $draftId, 'status' => 'completed']);
    }

    public function test_pending_draft_can_be_saved_and_loaded_again(): void
    {
        $draftId = $this->postJson('/api/sale/pos/drafts')->assertCreated()->json('data.id');

        $this->putJson("/api/sale/pos/drafts/{$draftId}", [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->stock->warehouse_id,
            'invoice_type' => 'retail',
            'items' => [[
                'product_id' => $this->stock->product_id,
                'quantity' => 1,
            ]],
        ])->assertOk()
            ->assertJsonPath('data.id', $draftId)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.items.0.product_id', $this->stock->product_id)
            ->assertJsonPath('data.items.0.quantity', 1);

        $this->getJson('/api/sale/pos/drafts')
            ->assertOk()
            ->assertJsonPath('data.0.id', $draftId)
            ->assertJsonPath('data.0.items.0.quantity', 1);
    }

    public function test_pending_draft_can_be_cancelled(): void
    {
        $draftId = $this->postJson('/api/sale/pos/drafts')->assertCreated()->json('data.id');

        $this->deleteJson("/api/sale/pos/drafts/{$draftId}")
            ->assertOk()
            ->assertJsonPath('message', 'Đã hủy hóa đơn chờ.');

        $this->assertDatabaseHas('sales_orders', [
            'id' => $draftId,
            'status' => 'cancelled',
            'sales_channel' => 'pos',
        ]);
        $this->getJson('/api/sale/pos/drafts')
            ->assertOk()
            ->assertJsonMissing(['id' => $draftId]);
    }

    public function test_pos_can_quick_create_a_customer(): void
    {
        $customerId = $this->postJson('/api/sale/pos/customers', [
            'name' => 'Khách POS mới',
            'phone' => '0901234567',
            'email' => 'pos-new@example.test',
        ])->assertCreated()
            ->assertJsonPath('data.debt_eligible', false)
            ->json('data.id');

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'company_id' => $this->user->company_id,
            'name' => 'Khách POS mới',
        ]);
    }

    public function test_walk_in_customer_is_excluded_from_customer_debt_reports(): void
    {
        $currencyId = $this->user->company->default_currency->id;
        $walkIn = Customer::firstOrCreate(
            ['company_id' => $this->user->company_id, 'code' => 'KH_LE'],
            ['name' => 'Khách lẻ', 'currency_id' => $currencyId, 'opening_debt' => 0, 'status' => 'active']
        );

        $response = $this->getJson('/api/accountant/customers-debt?per_page=100')->assertOk();
        $this->assertNotContains('KH_LE', collect($response->json('data'))->pluck('code')->all());

        $this->getJson("/api/accountant/customers-debt/{$walkIn->id}/detail")
            ->assertNotFound();
    }

    public function test_new_customer_cannot_buy_on_debt(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/sale/pos/orders', $this->payload(0))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('paid_amount');

        $this->assertDatabaseMissing('sales_orders', [
            'customer_id' => $this->customer->id,
            'sales_channel' => 'pos',
        ]);
    }

    public function test_customer_with_a_completed_order_can_buy_on_debt(): void
    {
        SalesOrder::create([
            'company_id' => $this->user->company_id,
            'customer_id' => $this->customer->id,
            'currency_id' => $this->user->company->default_currency->id,
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/sale/pos/orders', $this->payload(0))
            ->assertCreated();

        $this->assertDatabaseHas('sales_orders', [
            'customer_id' => $this->customer->id,
            'sales_channel' => 'pos',
            'paid_amount' => 0,
        ]);
    }

    public function test_walk_in_customer_can_pay_with_momo_and_stock_is_reduced(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');
        $quantityBefore = (float) $this->stock->quantity;
        $payload = $this->payload($price);
        unset($payload['customer_id']);
        $payload['payment_method'] = 'momo';
        $payload['payment_reference'] = 'MOMO-TEST-001';

        $orderId = $this->postJson('/api/sale/pos/orders', $payload)
            ->assertCreated()
            ->assertJsonPath('data.customer.name', 'Khách lẻ')
            ->assertJsonPath('data.payment_method', 'momo')
            ->json('data.id');

        $this->assertEquals(
            $quantityBefore - 1,
            (float) $this->stock->fresh()->quantity
        );
        $this->getJson('/api/sale/pos/history')
            ->assertOk()
            ->assertJsonPath('data.0.id', $orderId)
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('data.0.effective_status', 'completed');
    }

    public function test_completed_walk_in_pos_order_detail_shows_sold_quantity_as_exported(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');
        $payload = $this->payload($price);
        unset($payload['customer_id']);

        $orderId = $this->postJson('/api/sale/pos/orders', $payload)
            ->assertCreated()
            ->json('data.id');

        $this->getJson("/api/sale/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('customer.code', 'KH_LE')
            ->assertJsonPath('items.0.quantity', 1)
            ->assertJsonPath('items.0.exported_quantity', 1);
    }

    public function test_pos_order_detail_keeps_coupon_discount_in_total(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');
        PosCoupon::create([
            'company_id' => $this->user->company_id,
            'code' => 'DETAIL10',
            'name' => 'Giảm detail 10%',
            'type' => 'percent',
            'value' => 10,
            'minimum_order_amount' => 0,
            'is_active' => true,
        ]);
        $payload = $this->payload(round($price * 0.9, 2));
        $payload['coupon_code'] = 'DETAIL10';
        $orderId = $this->postJson('/api/sale/pos/orders', $payload)->assertCreated()->json('data.id');

        $response = $this->getJson("/api/sale/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('discount_amount', number_format($price * 0.1, 2, '.', ''));
        $this->assertEquals(round($price * 0.9, 2), $response->json('total_amount'));
    }

    public function test_valid_coupon_reduces_pos_invoice_total(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');
        PosCoupon::create([
            'company_id' => $this->user->company_id,
            'code' => 'GIAM10',
            'name' => 'Giảm 10%',
            'type' => 'percent',
            'value' => 10,
            'minimum_order_amount' => 0,
            'is_active' => true,
        ]);
        $payload = $this->payload(round($price * 0.9, 2));
        $payload['coupon_code'] = 'GIAM10';

        $response = $this->postJson('/api/sale/pos/orders', $payload)->assertCreated();
        $this->assertEquals(round($price * 0.1, 2), $response->json('data.discount_amount'));
        $this->assertEquals(round($price * 0.9, 2), $response->json('data.total_amount'));
    }

    public function test_cash_checkout_returns_change_without_overpaying_the_order(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');
        $payload = $this->payload($price + 50000);

        $response = $this->postJson('/api/sale/pos/orders', $payload)->assertCreated();

        $this->assertEquals($price, $response->json('data.paid_amount'));
        $this->assertEquals($price + 50000, $response->json('data.tendered_amount'));
        $this->assertEquals(50000, $response->json('data.change_amount'));
    }

    public function test_customer_can_pay_pos_order_in_another_company_currency(): void
    {
        $usd = Currency::firstOrCreate(
            ['code' => 'USD'],
            ['name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 25000, 'is_active' => true]
        );
        CompanyCurrencyRate::updateOrCreate(
            ['company_id' => $this->user->company_id, 'currency_id' => $usd->id, 'effective_date' => now()->toDateString()],
            ['rate_to_base' => 25000, 'created_by' => $this->user->id]
        );
        $price = (float) $this->stock->product()->value('sell_price');
        $payload = $this->payload(round($price / 25000, 2));
        $payload['payment_currency_id'] = $usd->id;

        $this->getJson('/api/sale/pos/options')
            ->assertOk()
            ->assertJsonFragment(['code' => 'USD']);

        $this->postJson('/api/sale/pos/orders', $payload)
            ->assertCreated()
            ->assertJsonPath('data.payment_currency.code', 'USD')
            ->assertJsonPath('data.payment_exchange_rate', 25000)
            ->assertJsonPath('data.payment_tendered_amount', round($price / 25000, 2));
    }

    public function test_coupon_usage_limit_is_enforced(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');
        PosCoupon::create([
            'company_id' => $this->user->company_id, 'code' => 'ONCE', 'name' => 'One use',
            'type' => 'fixed', 'value' => 1000, 'minimum_order_amount' => 0,
            'usage_limit' => 1, 'used_count' => 1, 'is_active' => true,
        ]);
        $payload = $this->payload($price - 1000);
        $payload['coupon_code'] = 'ONCE';

        $this->postJson('/api/sale/pos/orders', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('coupon_code');
    }

    public function test_insufficient_stock_rolls_back_the_entire_checkout(): void
    {
        $quantityBefore = (float) $this->stock->quantity;
        $ordersBefore = SalesOrder::where('sales_channel', 'pos')->count();
        $payload = $this->payload(999999999);
        $payload['items'][0]['quantity'] = $quantityBefore + 1;

        $this->postJson('/api/sale/pos/orders', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertEquals($quantityBefore, (float) $this->stock->fresh()->quantity);
        $this->assertSame($ordersBefore, SalesOrder::where('sales_channel', 'pos')->count());
    }

    public function test_momo_checkout_requires_a_payment_reference(): void
    {
        $price = (float) $this->stock->product()->value('sell_price');
        $payload = $this->payload($price);
        $payload['payment_method'] = 'momo';

        $this->postJson('/api/sale/pos/orders', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_reference');
    }
}
