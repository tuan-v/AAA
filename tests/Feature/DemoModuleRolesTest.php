<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseSlip;
use App\Models\Supplier;
use App\Models\Customer;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoModuleRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_detail_returns_real_profile_and_the_users_own_activity_history(): void
    {
        $this->seed(DatabaseSeeder::class);

        $viewer = User::where('email', 'hr@demo.vn')->firstOrFail();
        $target = User::where('email', 'sales@demo.vn')->firstOrFail();

        ActivityLog::create([
            'user_id' => $target->id,
            'action' => 'xem_chi_tiet',
            'model_type' => 'App\\Models\\SalesOrder',
            'model_id' => 123,
            'description' => 'Xem chi tiết đơn bán',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($viewer)
            ->getJson('/api/users/user/'.$target->id)
            ->assertOk()
            ->assertJsonPath('id', $target->id)
            ->assertJsonPath('company.id', $target->company_id)
            ->assertJsonPath('activities.0.action', 'xem_chi_tiet')
            ->assertJsonPath('activities.0.model_id', 123)
            ->assertJsonStructure([
                'roles',
                'company',
                'department_record',
                'position_record',
                'activities',
                'recent_sessions',
                'activity_count',
            ]);
    }

    public function test_demo_module_roles_are_global_system_roles_with_dedicated_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $accounts = [
            'hr@demo.vn' => 'Quản lý nhân sự',
            'purchase@demo.vn' => 'Quản lý mua hàng',
            'warehouse@demo.vn' => 'Quản lý kho',
            'sales@demo.vn' => 'Quản lý bán hàng',
            'accountant@demo.vn' => 'Quản lý kế toán',
        ];

        foreach ($accounts as $email => $roleName) {
            $role = Role::where('name', $roleName)->firstOrFail();
            $this->assertSame('system', $role->type);
            $this->assertNull($role->company_id);

            $user = User::where('email', $email)->firstOrFail();
            $this->assertTrue($user->hasRole($roleName));
            $this->assertGreaterThan(0, $user->getAllPermissions()->count());
        }
    }

    public function test_warehouse_manager_can_load_product_category_and_unit_options(): void
    {
        $this->seed(DatabaseSeeder::class);

        $warehouseManager = User::where('email', 'warehouse@demo.vn')->firstOrFail();

        $categories = $this->actingAs($warehouseManager)
            ->getJson('/api/warehouse/categories/select?active_only=1')
            ->assertOk();

        $units = $this->actingAs($warehouseManager)
            ->getJson('/api/warehouse/units/select?active_only=1')
            ->assertOk();

        $this->assertNotEmpty($categories->json('data') ?? $categories->json());
        $this->assertNotEmpty($units->json('data') ?? $units->json());
    }

    public function test_authorized_company_owner_can_update_a_system_module_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $role = Role::where('name', 'Quản lý kế toán')->firstOrFail();
        $permissions = $role->permissions()->pluck('name')->all();

        $response = $this->actingAs($owner)->putJson("/api/roles/{$role->id}", [
            'name' => $role->name,
            'description' => 'Vai trò kế toán hệ thống đã cập nhật',
            'permissions' => $permissions,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'description' => 'Vai trò kế toán hệ thống đã cập nhật',
            'type' => 'system',
            'company_id' => null,
        ]);
    }

    public function test_lower_role_cannot_see_roles_above_its_hierarchy_level(): void
    {
        $this->seed(DatabaseSeeder::class);

        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();
        $response = $this->actingAs($hr)->getJson('/api/roles');

        $response->assertOk();
        $names = collect($response->json('data.system'))->pluck('name');

        $this->assertTrue($names->contains('Quản lý nhân sự'));
        $this->assertFalse($names->contains('Giám đốc'));
        $this->assertFalse($names->contains('Supper Admin'));
    }

    public function test_used_warehouse_can_update_its_basic_information(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $warehouse = Warehouse::whereHas('slips')->firstOrFail();

        $response = $this->actingAs($owner)->putJson("/api/warehouses/{$warehouse->id}", [
            'name' => 'Kho Demo Đã Cập Nhật',
            'province_code' => $warehouse->province_code,
            'ward_code' => $warehouse->ward_code,
            'address_detail' => 'Số 2, phố Demo',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Kho Demo Đã Cập Nhật');

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Kho Demo Đã Cập Nhật',
            'address_detail' => 'Số 2, phố Demo',
        ]);
    }

    public function test_warehouse_status_is_a_string_and_can_be_toggled(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $warehouse = Warehouse::firstOrFail();

        $this->assertSame('active', $warehouse->status);

        $this->actingAs($owner)
            ->patchJson("/api/warehouses/{$warehouse->id}/status")
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($owner)
            ->patchJson("/api/warehouses/{$warehouse->id}/status")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
    }

    public function test_export_slip_detail_contains_customer_and_sale_price(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $slip = WarehouseSlip::where('type', 'export')->firstOrFail();

        $response = $this->actingAs($owner)
            ->getJson("/api/warehouse/slips/{$slip->id}");

        $response->assertOk()
            ->assertJsonPath('type', 'export')
            ->assertJsonPath('sale_order.customer.name', 'Cửa hàng Minh Anh');

        $this->assertGreaterThan(0, (float) $response->json('items.0.price'));
    }

    public function test_supplier_recent_order_has_date_and_can_load_full_detail(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $supplier = Supplier::whereHas('purchaseOrders')->firstOrFail();

        $supplierResponse = $this->actingAs($owner)
            ->getJson("/api/purchase/suppliers/{$supplier->id}/detail");

        $supplierResponse->assertOk();
        $orderId = $supplierResponse->json('recent_orders.0.id');
        $this->assertNotEmpty($supplierResponse->json('recent_orders.0.order_date'));

        $this->actingAs($owner)
            ->getJson("/api/purchase/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('supplier.id', $supplier->id)
            ->assertJsonStructure([
                'items' => [['amount', 'vat_amount', 'total_amount', 'vat_percent']],
                'created_at',
                'total_amount',
            ]);
    }

    public function test_customer_recent_order_has_date_and_can_load_full_detail(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $customer = Customer::whereHas('orders')->firstOrFail();

        $customerResponse = $this->actingAs($owner)
            ->getJson("/api/sale/customers/{$customer->id}/detail");

        $customerResponse->assertOk();
        $orderId = $customerResponse->json('recent_orders.0.id');
        $this->assertNotEmpty($customerResponse->json('recent_orders.0.order_date'));

        $this->actingAs($owner)
            ->getJson("/api/sale/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonStructure([
                'items' => [[
                    'amount',
                    'vat_amount',
                    'total_amount',
                    'vat_percent',
                ]],
                'created_at',
                'total_amount',
            ]);
    }

    public function test_accountant_can_open_full_customer_and_supplier_details(): void
    {
        $this->seed(DatabaseSeeder::class);

        $accountant = User::where('email', 'accountant@demo.vn')->firstOrFail();
        $customer = Customer::whereHas('orders')->firstOrFail();
        $supplier = Supplier::whereHas('purchaseOrders')->firstOrFail();

        $this->actingAs($accountant)
            ->getJson("/api/sale/customers/{$customer->id}/detail")
            ->assertOk()
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonStructure(['debt_summary', 'recent_orders', 'debt_history']);

        $this->actingAs($accountant)
            ->getJson("/api/purchase/suppliers/{$supplier->id}/detail")
            ->assertOk()
            ->assertJsonPath('supplier.id', $supplier->id)
            ->assertJsonStructure(['debt_summary', 'recent_orders', 'debt_history']);

        $this->actingAs($accountant)
            ->getJson('/api/sale/orders/'.$customer->orders()->firstOrFail()->id)
            ->assertOk();

        $this->actingAs($accountant)
            ->getJson('/api/purchase/orders/'.$supplier->purchaseOrders()->firstOrFail()->id)
            ->assertOk();
    }

    public function test_accountant_can_load_transaction_party_and_order_options(): void
    {
        $this->seed(DatabaseSeeder::class);

        $accountant = User::where('email', 'accountant@demo.vn')->firstOrFail();

        $this->actingAs($accountant)->getJson('/api/sale/customers/all')
            ->assertOk()->assertJsonCount(1);
        $this->actingAs($accountant)->getJson('/api/purchase/suppliers/all')
            ->assertOk()->assertJsonCount(1);
        $this->actingAs($accountant)->getJson('/api/sale/orders')
            ->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($accountant)->getJson('/api/purchase/orders')
            ->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_used_supplier_can_update_all_form_fields(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $supplier = Supplier::whereHas('purchaseOrders')->firstOrFail();

        $response = $this->actingAs($owner)->putJson("/api/purchase/suppliers/{$supplier->id}", [
            'name' => $supplier->name,
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'currency_id' => $supplier->currency_id,
            'province_code' => $supplier->province_code,
            'province_name' => $supplier->province_name,
            'ward_code' => $supplier->ward_code,
            'ward_name' => $supplier->ward_name,
            'address_detail' => 'Địa chỉ NCC đã cập nhật',
            'opening_debt' => 150000,
            'opening_advance' => 50000,
            'note' => 'Ghi chú NCC đã cập nhật',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'address_detail' => 'Địa chỉ NCC đã cập nhật',
            'opening_debt' => 150000,
            'opening_advance' => 50000,
            'note' => 'Ghi chú NCC đã cập nhật',
        ]);
    }
}
