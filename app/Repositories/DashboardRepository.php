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
use App\Models\CodReconciliation;
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
            ->whereHas('customer', fn($q) => $q->where('company_id', $companyId)->where('code', '!=', 'KH_LE'))
            ->whereIn('type', ['opening', 'sale', 'payment', 'opening_payment', 'refund'])
            ->sum('amount');
        $opening = (float) Customer::where('company_id', $companyId)
            ->where('code', '!=', 'KH_LE')->sum('opening_debt_base');

        return $opening + $movements;
    }

    public function getTotalPayableDebt(int $companyId): float
    {
        $movements = (float) SupplierDebt::query()
            ->whereHas('supplier', fn($q) => $q->where('company_id', $companyId))
            ->whereIn('type', [
                SupplierDebt::TYPE_INVOICE,
                SupplierDebt::TYPE_PAYMENT,
                SupplierDebt::TYPE_OPENING_PAYMENT,
                SupplierDebt::TYPE_REFUND,
            ])
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

    public function getOperationCounts(int $companyId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $monthStart = $from ?? Carbon::now()->startOfMonth();
        $monthEnd = $to ?? Carbon::now()->endOfMonth();

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

    public function getMonthlyFinance(int $companyId, int $months = 6, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $result = [];

        foreach ($this->monthlyPeriods($months, $from, $to) as [$monthDate, $periodFrom, $periodTo]) {

            $revenue = $this->getSalesRevenue($companyId, $periodFrom, $periodTo);
            $purchase = $this->getPurchaseCost($companyId, $periodFrom, $periodTo);

            $result[] = [
                'month' => 'T' . $monthDate->format('n') . '/' . $monthDate->format('Y'),
                'revenue' => $revenue,
                'purchase' => $purchase,
            ];
        }

        return $result;
    }

    public function getMonthlyCashFlow(int $companyId, int $months = 6, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $result = [];

        foreach ($this->monthlyPeriods($months, $from, $to) as [$monthDate, $periodFrom, $periodTo]) {

            $in = (float) Transaction::where('company_id', $companyId)
                ->where('type', 'receipt')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$periodFrom, $periodTo])
                ->sum('amount_base');

            $out = (float) Transaction::where('company_id', $companyId)
                ->where('type', 'payment')
                ->where('status', 'approved')
                ->whereBetween('transaction_date', [$periodFrom, $periodTo])
                ->sum('amount_base');

            $result[] = [
                'month' => 'T' . $monthDate->format('n'),
                'in' => $in,
                'out' => $out,
            ];
        }

        return $result;
    }

    public function getMonthlyDebtTrend(int $companyId, int $months = 6, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $result = [];

        foreach ($this->monthlyPeriods($months, $from, $to) as [$monthDate, $periodFrom, $periodTo]) {

            $receivable = (float) CustomerDebt::query()
                ->whereHas('customer', fn($q) => $q->where('company_id', $companyId)->where('code', '!=', 'KH_LE'))
                ->whereIn('type', ['opening', 'sale', 'payment', 'opening_payment', 'refund'])
                ->whereBetween('created_at', [$periodFrom, $periodTo])
                ->sum('amount');

            $payable = (float) SupplierDebt::query()
                ->whereHas('supplier', fn($q) => $q->where('company_id', $companyId))
                ->whereIn('type', [
                    SupplierDebt::TYPE_INVOICE,
                    SupplierDebt::TYPE_PAYMENT,
                    SupplierDebt::TYPE_OPENING_PAYMENT,
                    SupplierDebt::TYPE_REFUND,
                ])
                ->whereBetween('created_at', [$periodFrom, $periodTo])
                ->sum('amount');

            $result[] = [
                'month' => 'T' . $monthDate->format('n'),
                'receivable' => $receivable,
                'payable' => $payable,
            ];
        }

        return $result;
    }

    public function getMonthlyWarehouseFlow(int $companyId, int $months = 6, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $result = [];

        foreach ($this->monthlyPeriods($months, $from, $to) as [$monthDate, $periodFrom, $periodTo]) {

            $import = WarehouseSlip::where('company_id', $companyId)
                ->where('type', 'import')
                ->where('status', 'approved')
                ->whereBetween('approved_at', [$periodFrom, $periodTo])
                ->count();

            $export = WarehouseSlip::where('company_id', $companyId)
                ->where('type', 'export')
                ->where('status', 'approved')
                ->whereBetween('approved_at', [$periodFrom, $periodTo])
                ->count();

            $result[] = [
                'month' => 'T' . $monthDate->format('n'),
                'import' => $import,
                'export' => $export,
            ];
        }

        return $result;
    }

    public function getOrderStatusBreakdown(int $companyId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $sales = fn () => SalesOrder::where('company_id', $companyId)
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]));
        $purchases = fn () => PurchaseOrder::where('company_id', $companyId)
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]));

        $pending = $sales()->where('status', 'pending')->count()
            + $purchases()->where('status', 'pending')->count();

        $approved =
            $sales()
            ->whereIn('status', [
                'approved',
                'partial',
                'completed'
            ])
            ->count()
            +
            $purchases()
            ->whereIn('status', [
                'approved',
                'partial',
                'completed'
            ])
            ->count();

        $cancelled = $sales()->where('status', 'cancelled')->count()
            + $purchases()->where('status', 'cancelled')->count();

        return [
            ['label' => 'Chờ xử lý', 'value' => $pending, 'color' => '#f59e0b'],
            ['label' => 'Đã duyệt', 'value' => $approved, 'color' => '#22c55e'],
            ['label' => 'Đã hủy', 'value' => $cancelled, 'color' => '#ef4444'],
        ];
    }

    public function getTopCustomers(int $companyId, int $limit = 5, ?Carbon $from = null, ?Carbon $to = null): array
    {
        return SalesOrder::query()
            ->where('sales_orders.company_id', $companyId)
            ->whereIn('sales_orders.status', [
                'approved',
                'partial',
                'completed'
            ])
            ->when($from && $to, fn ($q) => $q->whereBetween('sales_orders.created_at', [$from, $to]))
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

    public function getTopSuppliers(int $companyId, int $limit = 5, ?Carbon $from = null, ?Carbon $to = null): array
    {
        return PurchaseOrder::query()
            ->where('purchase_orders.company_id', $companyId)
            ->whereIn('purchase_orders.status', ['approved', 'partial', 'completed'])
            ->when($from && $to, fn ($q) => $q->whereBetween('purchase_orders.created_at', [$from, $to]))
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

    public function getRecentSalesOrders(int $companyId, int $limit = 5, ?Carbon $from = null, ?Carbon $to = null): array
    {
        return SalesOrder::with('customer:id,name')
            ->where('company_id', $companyId)
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
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

    public function getRecentPurchaseOrders(int $companyId, int $limit = 5, ?Carbon $from = null, ?Carbon $to = null): array
    {
        return PurchaseOrder::with('supplier:id,name')
            ->withSum(
                'items as items_total',
                DB::raw('company_price * quantity * (1 + COALESCE(vat_percent, 0) / 100)')
            )
            ->where('company_id', $companyId)
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
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

    public function getRecentTransactions(int $companyId, int $limit = 5, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $transactions = Transaction::with([
            'customer:id,name',
            'supplier:id,name',
            'category:id,code,name',
            'salesOrder:id,code,customer_id',
            'salesOrder.customer:id,name',
            'purchaseOrder:id,code,supplier_id',
            'purchaseOrder.supplier:id,name',
            'fromAccount:id,name',
            'toAccount:id,name',
        ])
            ->where('company_id', $companyId)
            ->when($from && $to, fn ($q) => $q->whereBetween('transaction_date', [$from, $to]))
            ->latest('transaction_date')
            ->limit($limit)
            ->get();

        $codReconciliations = CodReconciliation::with([
            'partner:id,name',
            'items:id,cod_reconciliation_id,sales_order_id',
            'items.order:id,code',
        ])
            ->where('company_id', $companyId)
            ->whereIn('id', $transactions
                ->where('reference_type', CodReconciliation::class)
                ->pluck('reference_id')
                ->filter())
            ->get()
            ->keyBy('id');

        return $transactions
            ->map(fn($t) => [
                'code' => $t->code,
                'type' => match ($t->type) {
                    'receipt' => 'Thu tiền',
                    'payment' => 'Chi tiền',
                    'transfer' => 'Chuyển quỹ',
                    default => $t->type,
                },
                'business_type' => $this->transactionBusinessType($t),
                'category_code' => $t->category?->code,
                'target' => $this->transactionCounterparty(
                    $t,
                    $codReconciliations->get($t->reference_id)
                ),
                'amount' => (float) $t->amount_base,
                'date' => $t->transaction_date->format('d/m/Y'),
                'status' => $t->status,
            ])
            ->toArray();
    }

    private function transactionCounterparty(Transaction $transaction, ?CodReconciliation $codReconciliation): string
    {
        if ($transaction->reference_type === CodReconciliation::class && $codReconciliation) {
            $partner = $codReconciliation->partner?->name ?? 'đơn vị vận chuyển chưa xác định';
            $orders = $codReconciliation->items
                ->pluck('order.code')
                ->filter()
                ->join(', ');

            return 'Thu COD từ '.$partner.($orders ? ' · '.$orders : '');
        }

        if ($transaction->customer) {
            return $transaction->type === 'payment'
                ? 'Hoàn cho KH '.$transaction->customer->name
                : 'Thu từ KH '.$transaction->customer->name;
        }

        if ($transaction->supplier) {
            return $transaction->type === 'receipt'
                ? 'Thu từ NCC '.$transaction->supplier->name
                : 'Chi cho NCC '.$transaction->supplier->name;
        }

        if ($transaction->salesOrder?->customer) {
            return 'Thu từ KH '.$transaction->salesOrder->customer->name.' · '.$transaction->salesOrder->code;
        }

        if ($transaction->purchaseOrder?->supplier) {
            return 'Chi cho NCC '.$transaction->purchaseOrder->supplier->name.' · '.$transaction->purchaseOrder->code;
        }

        if ($transaction->type === 'transfer') {
            return ($transaction->fromAccount?->name ?? 'Tài khoản nguồn chưa xác định')
                .' → '.($transaction->toAccount?->name ?? 'Tài khoản nhận chưa xác định');
        }

        if ($transaction->type === 'receipt') {
            return 'Thu vào '.($transaction->toAccount?->name ?? 'tài khoản chưa xác định')
                .' · Chưa khai báo người nộp';
        }

        return 'Chi từ '.($transaction->fromAccount?->name ?? 'tài khoản chưa xác định')
            .' · Chưa khai báo người nhận';
    }

    private function transactionBusinessType(Transaction $transaction): string
    {
        return match ($transaction->category?->code) {
            'CHI_NCC' => 'Thanh toán công nợ',
            'CHI_KHAC' => 'Thanh toán nợ đầu kỳ',
            'TAM_UNG_NCC' => 'Tạm ứng nhà cung cấp',
            'HOAN_TAM_UNG_NCC' => 'Hoàn tạm ứng NCC',
            'THU_KH' => 'Thu công nợ khách hàng',
            'THU_KHAC' => 'Thu công nợ đầu kỳ',
            'TAM_UNG_KH' => 'Khách hàng tạm ứng',
            'HOAN_TAM_UNG_KH' => 'Hoàn tạm ứng khách hàng',
            default => $transaction->category?->name ?? match ($transaction->type) {
                'transfer' => 'Chuyển quỹ nội bộ',
                'receipt' => 'Khoản thu khác',
                default => 'Khoản chi khác',
            },
        };
    }

    private function monthlyPeriods(int $months, ?Carbon $from, ?Carbon $to): array
    {
        if (! $from || ! $to) {
            $to = Carbon::now()->endOfMonth();
            $from = Carbon::now()->subMonthsNoOverflow($months - 1)->startOfMonth();
        }

        $periods = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to)) {
            $periods[] = [
                $cursor->copy(),
                $cursor->copy()->startOfMonth()->max($from),
                $cursor->copy()->endOfMonth()->min($to),
            ];
            $cursor->addMonthNoOverflow()->startOfMonth();
        }

        return $periods;
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
