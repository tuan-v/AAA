<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\User;
use App\Repositories\DashboardRepository;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_finance_does_not_skip_shorter_month_at_month_end(): void
    {
        Carbon::setTestNow('2026-07-31 12:00:00');

        try {
            $months = collect(app(DashboardRepository::class)->getMonthlyFinance(1, 6))
                ->pluck('month')
                ->all();

            $this->assertSame([
                'T2/2026',
                'T3/2026',
                'T4/2026',
                'T5/2026',
                'T6/2026',
                'T7/2026',
            ], $months);
        } finally {
            Carbon::setTestNow();
        }
    }

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

    public function test_overview_dashboard_includes_the_company_currency(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $currency = $user->company->default_currency;

        $this->actingAs($user)
            ->getJson('/api/dashboard/overview')
            ->assertOk()
            ->assertJsonPath('data.currency.code', $currency->code)
            ->assertJsonPath('data.currency.symbol', $currency->symbol);
    }

    public function test_dashboard_filters_transactional_data_by_requested_date_range(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();

        $this->actingAs($user)
            ->getJson('/api/dashboard/overview?date_from=2000-01-01&date_to=2000-01-31')
            ->assertOk()
            ->assertJsonPath('data.period.date_from', '2000-01-01')
            ->assertJsonPath('data.period.date_to', '2000-01-31')
            ->assertJsonPath('data.operations.sales_orders_this_month', 0)
            ->assertJsonPath('data.operations.purchase_orders_this_month', 0)
            ->assertJsonCount(0, 'data.recent_sales_orders')
            ->assertJsonCount(0, 'data.recent_purchase_orders')
            ->assertJsonCount(0, 'data.recent_transactions');
    }

    public function test_dashboard_rejects_an_inverted_date_range(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();

        $this->actingAs($user)
            ->getJson('/api/dashboard/overview?date_from=2026-08-10&date_to=2026-08-01')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_to');
    }

    public function test_dashboard_rejects_future_dates(): void
    {
        Carbon::setTestNow('2026-08-03 12:00:00');

        try {
            $this->seed(\Database\Seeders\DatabaseSeeder::class);
            $user = User::where('email', 'admin@demo.vn')->firstOrFail();

            $this->actingAs($user)
                ->getJson('/api/dashboard/overview?date_from=2026-08-04&date_to=2026-08-05')
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['date_from', 'date_to']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_defaults_to_the_current_month(): void
    {
        Carbon::setTestNow('2026-08-03 12:00:00');

        try {
            $this->seed(\Database\Seeders\DatabaseSeeder::class);
            $user = User::where('email', 'admin@demo.vn')->firstOrFail();

            $this->actingAs($user)
                ->getJson('/api/dashboard/overview')
                ->assertOk()
                ->assertJsonPath('data.period.date_from', '2026-08-01')
                ->assertJsonPath('data.period.date_to', '2026-08-03');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_debt_trend_reports_only_movements_inside_each_month(): void
    {
        Carbon::setTestNow('2026-08-05 12:00:00');

        try {
            $this->seed(\Database\Seeders\DatabaseSeeder::class);
            $user = User::where('email', 'admin@demo.vn')->firstOrFail();
            $repository = app(DashboardRepository::class);
            $trend = $repository->getMonthlyDebtTrend(
                $user->company_id,
                6,
                now()->startOfMonth(),
                now()->endOfDay(),
            );
            $point = collect($trend)->last();
            $expectedReceivable = (float) DB::table('customer_debts')
                ->join('customers', 'customers.id', '=', 'customer_debts.customer_id')
                ->where('customers.company_id', $user->company_id)
                ->where('customers.code', '!=', 'KH_LE')
                ->whereIn('customer_debts.type', ['opening', 'sale', 'payment', 'opening_payment', 'refund'])
                ->whereBetween('customer_debts.created_at', [now()->startOfMonth(), now()->endOfDay()])
                ->sum('customer_debts.amount');
            $expectedPayable = (float) DB::table('supplier_debts')
                ->join('suppliers', 'suppliers.id', '=', 'supplier_debts.supplier_id')
                ->where('suppliers.company_id', $user->company_id)
                ->whereIn('supplier_debts.type', ['invoice', 'payment', 'opening_payment', 'refund'])
                ->whereBetween('supplier_debts.created_at', [now()->startOfMonth(), now()->endOfDay()])
                ->sum('supplier_debts.amount');

            $this->assertSame($expectedReceivable, $point['receivable']);
            $this->assertSame($expectedPayable, $point['payable']);
            $this->assertNotSame($repository->getTotalReceivableDebt($user->company_id), $point['receivable']);
            $this->assertNotSame($repository->getTotalPayableDebt($user->company_id), $point['payable']);
        } finally {
            Carbon::setTestNow();
        }
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
