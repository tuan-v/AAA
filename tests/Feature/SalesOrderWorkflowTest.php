<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_sales_order_follows_the_delivery_workflow(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($user);
        $customer = Customer::where('company_id', $user->company_id)->where('code', '!=', 'KH_LE')->firstOrFail();
        $order = SalesOrder::create([
            'company_id' => $user->company_id,
            'customer_id' => $customer->id,
            'currency_id' => $user->company->default_currency->id,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->postJson("/api/sale/orders/{$order->id}/submit")
            ->assertOk();
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->submitted_at);

        $this->postJson("/api/sale/orders/{$order->id}/approve")
            ->assertOk();
        $this->assertSame('approved', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->approved_at);

    }

    public function test_workflow_does_not_allow_skipping_confirmation(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $this->actingAs($user);
        $customer = Customer::where('company_id', $user->company_id)->where('code', '!=', 'KH_LE')->firstOrFail();
        $order = SalesOrder::create([
            'company_id' => $user->company_id,
            'customer_id' => $customer->id,
            'currency_id' => $user->company->default_currency->id,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->postJson("/api/sale/orders/{$order->id}/approve")
            ->assertUnprocessable();
        $this->assertSame('draft', $order->fresh()->status);
    }
}
