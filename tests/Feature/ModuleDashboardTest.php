<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_and_purchase_dashboards_show_their_own_current_month_order_counts(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $expectedSales = SalesOrder::where('company_id', $user->company_id)
            ->whereBetween('created_at', [$from, $to])->count();
        $expectedPurchases = PurchaseOrder::where('company_id', $user->company_id)
            ->whereBetween('created_at', [$from, $to])->count();

        $saleMetrics = collect($this->actingAs($user)->getJson('/api/dashboard/sale')
            ->assertOk()->json('data.metrics'));
        $purchaseMetrics = collect($this->getJson('/api/dashboard/purchase')
            ->assertOk()->json('data.metrics'));

        $this->assertSame($expectedSales, $saleMetrics->firstWhere('label', 'Đơn bán tháng này')['value']);
        $this->assertSame($expectedPurchases, $purchaseMetrics->firstWhere('label', 'Đơn mua tháng này')['value']);
    }

    public function test_each_business_module_has_a_real_dashboard_api(): void
    {
        $user = User::factory()->create(['company_id' => null]);
        $company = Company::create([
            'name' => 'Công ty kiểm thử dashboard',
            'address' => 'TP.HCM',
            'phone' => '0900000000',
            'owner_id' => $user->id,
        ]);
        $user->update(['company_id' => $company->id]);
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo([
            'don_mua.xem',
            'don_ban.xem',
            'kho.xem',
            'giao_dich.xem',
        ]);

        foreach (['purchase', 'sale', 'warehouse', 'accountant'] as $module) {
            $this->actingAs($user)
                ->getJson("/api/dashboard/{$module}")
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonCount(4, 'data.metrics')
                ->assertJsonStructure([
                    'data' => ['metrics', 'trend', 'recent', 'ranking', 'currency'],
                ]);
        }
    }

    public function test_warehouse_dashboard_counts_only_active_products_with_positive_stock(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();

        $expected = DB::table('products')
            ->where('products.company_id', $user->company_id)
            ->where('products.status', 'active')
            ->whereIn('products.id', DB::table('warehouse_product_stocks')
                ->select('product_id')
                ->where('company_id', $user->company_id)
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) > 0'))
            ->count();

        $metrics = collect($this->actingAs($user)
            ->getJson('/api/dashboard/warehouse')
            ->assertOk()
            ->json('data.metrics'));

        $this->assertSame(
            $expected,
            $metrics->firstWhere('label', 'Sản phẩm hoạt động')['value']
        );
    }

    public function test_module_user_cannot_access_overall_or_another_module_dashboard(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $warehouseUser = User::where('email', 'warehouse@demo.vn')->firstOrFail();

        $this->actingAs($warehouseUser)
            ->getJson('/api/dashboard/overview')
            ->assertForbidden();

        $this->actingAs($warehouseUser)
            ->getJson('/api/dashboard/accountant')
            ->assertForbidden();

        $this->actingAs($warehouseUser)
            ->getJson('/api/dashboard/warehouse')
            ->assertOk();

        $this->actingAs($warehouseUser)
            ->get('/dashboard')
            ->assertRedirect('/warehouse');
    }
}
