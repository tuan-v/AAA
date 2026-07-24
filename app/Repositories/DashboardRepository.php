<?php

namespace App\Repositories;

use App\Repositories\DashboardRepositoryInterface;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\CustomerDebt;
use App\Models\SupplierDebt;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseSlip;
use App\Models\Transaction;
use App\Models\Account;
use App\Services\CompanyCurrencyService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getSalesRevenue(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) SalesOrder::query()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                'approved',
                'partial',
                'completed'
            ])
            ->whereBetween('created_at', [$from, $to])
            ->sum(DB::raw('total_amount * COALESCE(NULLIF(exchange_rate, 0), 1)'));
    }

    public function getPurchaseCost(int $companyId, Carbon $from, Carbon $to): float
    {
        return (float) DB::table('purchase_orders')
            ->join(
                'purchase_order_items',
                'purchase_orders.id',
                '=',
                'purchase_order_items.purchase_order_id'
            )
            ->where('purchase_orders.company_id', $companyId)
            ->whereIn('purchase_orders.status', ['approved', 'partial', 'completed'])
            ->whereBetween('purchase_orders.created_at', [$from, $to])
            ->sum(DB::raw(
                'purchase_order_items.company_price * purchase_order_items.quantity * '
                .'(1 + COALESCE(purchase_order_items.vat_percent, 0) / 100)'
            ));
    }

    public function getTotalReceivableDebt(int $companyId): float
    {
        // customer_debts không có company_id trực tiếp -> lọc qua quan hệ customer.
        $movements = (float) CustomerDebt::query()
            ->whereHas('customer', fn($q) => $q->where('company_id', $companyId))
            ->sum('amount');
        $opening = (float) Customer::where('company_id', $companyId)->sum('opening_debt_base');

        return $opening + $movements;
    }

    public function getTotalPayableDebt(int $companyId): float
    {
        $movements = (float) SupplierDebt::query()
            ->whereHas('supplier', fn($q) => $q->where('company_id', $companyId))
            ->sum('amount');
        $opening = (float) Supplier::where('company_id', $companyId)->sum('opening_debt_base');

        return $opening + $movements;
    }

    public function getTotalAccountBalanceBase(int $companyId): float
    {
        $rates = app(CompanyCurrencyService::class);

        return round((float) Account::with('currency')
            ->where('company_id', $companyId)
            ->get()
            ->sum(function (Account $account) use ($rates, $companyId) {
                return (float) $account->current_balance
                    * $rates->rate($companyId, (int) $account->currency_id, now());
            }), 2);
    }

    public function getOperationCounts(int $companyId): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $salesOrdersThisMonth = SalesOrder::where('company_id', $companyId)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $purchaseOrdersThisMonth = PurchaseOrder::where('company_id', $companyId)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $activeWarehouseProducts = Product::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereIn('id', DB::table('warehouse_product_stocks')
                ->select('product_id')
                ->where('company_id', $companyId)
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) > 0'))
            ->count();

        return [
            'users' => User::where('company_id', $companyId)->count(),
            'customers' => Customer::where('company_id', $companyId)->where('status', 'active')->count(),
            'suppliers' => Supplier::where('company_id', $companyId)->where('status', 'active')->count(),
            'products' => Product::where('company_id', $companyId)->where('status', 'active')->count(),
            'warehouse_products' => $activeWarehouseProducts,
            'warehouses' => Warehouse::where('company_id', $companyId)->where('status', 'active')->count(),
            'sales_orders_this_month' => $salesOrdersThisMonth,
            'purchase_orders_this_month' => $purchaseOrdersThisMonth,
            'orders_this_month' => $salesOrdersThisMonth + $purchaseOrdersThisMonth,
        ];
    }

    public function getMonthlyFinance(int $companyId, int $months = 6): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $from = $monthDate->copy()->startOfMonth();
            $to = $monthDate->copy()->endOfMonth();

            $revenue = $this->getSalesRevenue($companyId, $from, $to);
            $purchase = $this->getPurchaseCost($companyId, $from, $to);

            $result[] = [
                'month' => 'T' . $monthDate->format('n') . '/' . $monthDate->format('Y'),
                'revenue' => $revenue,
                'purchase' => $purchase,
            ];
        }

        return $result;
    }

    public function getMonthlyCashFlow(int $companyId, int $months = 6): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $from = $monthDate->copy()->startOfMonth();
            $to = $monthDate->copy()->endOfMonth();

            $in = (float) Transaction::where('company_id', $companyId)
                ->where('type', 'receipt')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$from, $to])
                ->sum('amount_base');

            $out = (float) Transaction::where('company_id', $companyId)
                ->where('type', 'payment')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$from, $to])
                ->sum('amount_base');

            $result[] = [
                'month' => 'T' . $monthDate->format('n'),
                'in' => $in,
                'out' => $out,
            ];
        }

        return $result;
    }

    public function getMonthlyDebtTrend(int $companyId, int $months = 6): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $endOfMonth = $monthDate->copy()->endOfMonth();

            $receivable = (float) CustomerDebt::query()
                ->whereHas('customer', fn($q) => $q->where('company_id', $companyId))
                ->where('created_at', '<=', $endOfMonth)
                ->sum('amount');

            $payable = (float) SupplierDebt::query()
                ->whereHas('supplier', fn($q) => $q->where('company_id', $companyId))
                ->where('created_at', '<=', $endOfMonth)
                ->sum('amount');

            $result[] = [
                'month' => 'T' . $monthDate->format('n'),
                'receivable' => $receivable,
                'payable' => $payable,
            ];
        }

        return $result;
    }

    public function getMonthlyWarehouseFlow(int $companyId, int $months = 6): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $from = $monthDate->copy()->startOfMonth();
            $to = $monthDate->copy()->endOfMonth();

            $import = WarehouseSlip::where('company_id', $companyId)
                ->where('type', 'import')
                ->where('status', 'approved')
                ->whereBetween('approved_at', [$from, $to])
                ->count();

            $export = WarehouseSlip::where('company_id', $companyId)
                ->where('type', 'export')
                ->where('status', 'approved')
                ->whereBetween('approved_at', [$from, $to])
                ->count();

            $result[] = [
                'month' => 'T' . $monthDate->format('n'),
                'import' => $import,
                'export' => $export,
            ];
        }

        return $result;
    }

    public function getOrderStatusBreakdown(int $companyId): array
    {
        $pending = SalesOrder::where('company_id', $companyId)->where('status', 'pending')->count()
            + PurchaseOrder::where('company_id', $companyId)->where('status', 'pending')->count();

        $approved =
            SalesOrder::where('company_id', $companyId)
            ->whereIn('status', [
                'approved',
                'partial',
                'completed'
            ])
            ->count()
            +
            PurchaseOrder::where('company_id', $companyId)
            ->whereIn('status', [
                'approved',
                'partial',
                'completed'
            ])
            ->count();

        $cancelled = SalesOrder::where('company_id', $companyId)->where('status', 'cancelled')->count()
            + PurchaseOrder::where('company_id', $companyId)->where('status', 'cancelled')->count();

        return [
            ['label' => 'Chờ xử lý', 'value' => $pending, 'color' => '#f59e0b'],
            ['label' => 'Đã duyệt', 'value' => $approved, 'color' => '#22c55e'],
            ['label' => 'Đã hủy', 'value' => $cancelled, 'color' => '#ef4444'],
        ];
    }

    public function getTopCustomers(int $companyId, int $limit = 5): array
    {
        return SalesOrder::query()
            ->where('sales_orders.company_id', $companyId)
            ->whereIn('sales_orders.status', [
                'approved',
                'partial',
                'completed'
            ])
            ->join('customers', 'customers.id', '=', 'sales_orders.customer_id')
            ->groupBy('customers.id', 'customers.name')
            ->select(
                'customers.name as name',
                DB::raw('SUM(sales_orders.total_amount * COALESCE(NULLIF(sales_orders.exchange_rate, 0), 1)) as value')
            )
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn($row) => ['name' => $row->name, 'value' => (float) $row->value])
            ->toArray();
    }

    public function getTopSuppliers(int $companyId, int $limit = 5): array
    {
        return PurchaseOrder::query()
            ->where('purchase_orders.company_id', $companyId)
            ->whereIn('purchase_orders.status', ['approved', 'partial', 'completed'])
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->join('purchase_order_items', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->select(
                'suppliers.name as name',
                DB::raw(
                    'SUM(purchase_order_items.company_price * purchase_order_items.quantity * '
                    .'(1 + COALESCE(purchase_order_items.vat_percent, 0) / 100)) as value'
                )
            )
            ->orderByDesc('value')
            ->limit($limit)
            ->get()
            ->map(fn($row) => ['name' => $row->name, 'value' => (float) $row->value])
            ->toArray();
    }

    public function getRecentSalesOrders(int $companyId, int $limit = 5): array
    {
        return SalesOrder::with('customer:id,name')
            ->where('company_id', $companyId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($order) => [
                'code' => $order->code,
                'customer' => $order->customer->name ?? '—',
                'date' => $order->created_at->format('d/m/Y'),
                'total' => round(
                    (float) $order->total_amount * (float) ($order->exchange_rate ?: 1),
                    2
                ),
                'status' => $order->status,
            ])
            ->toArray();
    }

    public function getRecentPurchaseOrders(int $companyId, int $limit = 5): array
    {
        return PurchaseOrder::with('supplier:id,name')
            ->withSum(
                'items as items_total',
                DB::raw('company_price * quantity * (1 + COALESCE(vat_percent, 0) / 100)')
            )
            ->where('company_id', $companyId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($order) => [
                'code' => $order->code,
                'supplier' => $order->supplier->name ?? '—',
                'date' => $order->created_at->format('d/m/Y'),
                'total' => (float)$order->items_total,
                'status' => $order->status,
            ])
            ->toArray();
    }

    public function getRecentTransactions(int $companyId, int $limit = 5): array
    {
        return Transaction::with(['customer:id,name', 'supplier:id,name'])
            ->where('company_id', $companyId)
            ->latest('transaction_date')
            ->limit($limit)
            ->get()
            ->map(fn($t) => [
                'code' => $t->code,
                'type' => match ($t->type) {
                    'receipt' => 'Thu tiền',
                    'payment' => 'Chi tiền',
                    'transfer' => 'Chuyển quỹ',
                    default => $t->type,
                },
                'target' => $t->customer->name ?? $t->supplier->name ?? '—',
                'amount' => (float) $t->amount_base,
                'date' => $t->transaction_date->format('d/m/Y'),
            ])
            ->toArray();
    }

    public function getLowStockProducts(int $companyId, int $threshold = 10, int $limit = 10): array
    {
        // TODO: hiện chưa có cột min_quantity trong schema (products / warehouse_product_stocks).
        // Đang dùng ngưỡng cố định $threshold. Nên bổ sung cột min_quantity để chính xác theo từng sản phẩm.
        return DB::table('warehouse_product_stocks as wps')
            ->join('products as p', 'p.id', '=', 'wps.product_id')
            ->join('warehouses as w', 'w.id', '=', 'wps.warehouse_id')
            ->where('wps.company_id', $companyId)
            ->where('wps.quantity', '<', $threshold)
            ->select(
                'p.name as name',
                'w.name as warehouse',
                'wps.quantity as quantity',
            )
            ->orderBy('wps.quantity')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'name' => $row->name,
                'warehouse' => $row->warehouse,
                'quantity' => (int) $row->quantity,
                'unit' => '', // TODO: join thêm bảng units qua products.unit_id nếu cần hiển thị đơn vị tính
                'minQuantity' => $threshold,
            ])
            ->toArray();
    }
}
