<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Notification;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_read_and_delete_only_their_notifications(): void
    {
        $user = User::create([
            'name' => 'Người nhận', 'username' => 'receiver',
            'email' => 'receiver@example.com', 'password' => Hash::make('password'),
            'type' => 'user', 'status' => 'active',
        ]);
        $other = User::create([
            'name' => 'Người khác', 'username' => 'other',
            'email' => 'other@example.com', 'password' => Hash::make('password'),
            'type' => 'user', 'status' => 'active',
        ]);
        $company = Company::create([
            'name' => 'Công ty thông báo', 'address' => 'Hà Nội',
            'phone' => '0900000000', 'owner_id' => $user->id,
        ]);
        $user->update(['company_id' => $company->id]);
        $other->update(['company_id' => $company->id]);

        $mine = Notification::create([
            'user_id' => $user->id, 'company_id' => $company->id,
            'title' => 'Giao dịch mới', 'message' => 'Có giao dịch chờ duyệt.',
            'category' => 'order', 'subdomain' => 'main',
        ]);
        $notMine = Notification::create([
            'user_id' => $other->id, 'company_id' => $company->id,
            'title' => 'Thông báo riêng', 'message' => 'Không thuộc người dùng hiện tại.',
        ]);

        $this->withServerVariables(['HTTP_HOST' => '127.0.0.1:8000'])
            ->actingAs($user)->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $mine->id)
            ->assertJsonMissing(['id' => $notMine->id]);

        $this->getJson('/api/notifications/unread-count')
            ->assertOk()->assertJsonPath('count', 1);
        $this->postJson("/api/notifications/{$mine->id}/mark-as-read")->assertOk();
        $this->postJson("/api/notifications/{$notMine->id}/mark-as-read")->assertNotFound();
        $this->deleteJson("/api/notifications/{$mine->id}")->assertOk();
    }

    public function test_sales_order_creator_is_notified_when_they_cancel_their_own_order(): void
    {
        $this->seed(DatabaseSeeder::class);

        $creator = User::where('email', 'admin@demo.vn')->firstOrFail();
        $company = $creator->company ?? $creator->companies()->firstOrFail();
        $customer = Customer::where('company_id', $company->id)->firstOrFail();
        $currency = Currency::whereHas(
            'companies',
            fn ($query) => $query->where('companies.id', $company->id)
        )->firstOrFail();

        $this->actingAs($creator);

        $order = SalesOrder::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'currency_id' => $currency->id,
            'status' => 'pending',
            'created_by' => $creator->id,
        ]);

        $this->postJson("/api/sale/orders/{$order->id}/cancel")
            ->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $creator->id,
            'company_id' => $company->id,
            'title' => 'Đơn bán đã bị hủy',
            'category' => 'sale',
        ]);

        $notification = Notification::where('user_id', $creator->id)->latest('id')->firstOrFail();
        $this->assertSame('sales_order_cancelled', $notification->data['event_type']);
        $this->assertSame('error', $notification->data['toast_type']);
    }

    public function test_purchase_order_creator_can_read_notification_when_director_cancels(): void
    {
        $this->seed(DatabaseSeeder::class);

        $director = User::where('email', 'admin@demo.vn')->firstOrFail();
        $company = $director->company ?? $director->companies()->firstOrFail();
        $creator = User::where('email', 'purchase@demo.vn')->firstOrFail();
        $supplier = Supplier::where('company_id', $company->id)->firstOrFail();

        $this->actingAs($creator);
        $order = PurchaseOrder::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $supplier->currency_id,
            'expected_received_date' => '2026-07-31',
            'status' => 'pending',
            'created_by' => $creator->id,
        ]);

        $this->actingAs($director)
            ->postJson("/api/purchase/orders/{$order->id}/cancel")
            ->assertOk();

        $this->actingAs($creator)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.data.0.user_id', $creator->id)
            ->assertJsonPath('data.data.0.category', 'purchase')
            ->assertJsonPath('data.data.0.data.purchase_order_id', $order->id)
            ->assertJsonPath('data.data.0.data.status', 'cancelled')
            ->assertJsonPath('data.data.0.data.event_type', 'purchase_order_cancelled')
            ->assertJsonPath('data.data.0.data.toast_type', 'error');
    }

    public function test_purchase_and_sales_order_creators_receive_success_toasts_after_approval(): void
    {
        $this->seed(DatabaseSeeder::class);

        $director = User::where('email', 'admin@demo.vn')->firstOrFail();
        $purchaseCreator = User::where('email', 'purchase@demo.vn')->firstOrFail();
        $salesCreator = User::where('email', 'sales@demo.vn')->firstOrFail();
        $company = $director->company ?? $director->companies()->firstOrFail();
        $supplier = Supplier::where('company_id', $company->id)->firstOrFail();
        $customer = Customer::where('company_id', $company->id)->firstOrFail();
        $currency = Currency::whereHas(
            'companies',
            fn ($query) => $query->where('companies.id', $company->id)
        )->firstOrFail();

        $this->actingAs($director);

        $purchaseOrder = PurchaseOrder::create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'currency_id' => $supplier->currency_id,
            'expected_received_date' => '2026-07-31',
            'status' => 'pending',
            'created_by' => $purchaseCreator->id,
        ]);
        $salesOrder = SalesOrder::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'currency_id' => $currency->id,
            'status' => 'pending',
            'created_by' => $salesCreator->id,
        ]);

        $this->actingAs($director)
            ->postJson("/api/purchase/orders/{$purchaseOrder->id}/approve")
            ->assertOk();
        $this->postJson("/api/sale/orders/{$salesOrder->id}/approve")
            ->assertOk();

        $purchaseNotification = Notification::where('user_id', $purchaseCreator->id)
            ->where('title', 'Đơn mua đã được duyệt')->latest('id')->firstOrFail();
        $salesNotification = Notification::where('user_id', $salesCreator->id)
            ->where('title', 'Đơn bán đã được duyệt')->latest('id')->firstOrFail();

        $this->assertSame('purchase_order_approved', $purchaseNotification->data['event_type']);
        $this->assertSame('success', $purchaseNotification->data['toast_type']);
        $this->assertSame('sales_order_approved', $salesNotification->data['event_type']);
        $this->assertSame('success', $salesNotification->data['toast_type']);
    }
}
