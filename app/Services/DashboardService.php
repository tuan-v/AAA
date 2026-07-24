<?php

namespace App\Services;

use App\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function getOverview(int $companyId): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $revenueThisMonth = $this->dashboardRepository->getSalesRevenue($companyId, $monthStart, $monthEnd);
        $revenueLastMonth = $this->dashboardRepository->getSalesRevenue($companyId, $lastMonthStart, $lastMonthEnd);

        $purchaseThisMonth = $this->dashboardRepository->getPurchaseCost($companyId, $monthStart, $monthEnd);
        $purchaseLastMonth = $this->dashboardRepository->getPurchaseCost($companyId, $lastMonthStart, $lastMonthEnd);

        $receivableDebt = $this->dashboardRepository->getTotalReceivableDebt($companyId);
        $payableDebt = $this->dashboardRepository->getTotalPayableDebt($companyId);

        return [
            'finance' => [
                'revenue_this_month' => $revenueThisMonth,
                'revenue_trend_percent' => $this->percentChange($revenueLastMonth, $revenueThisMonth),
                'purchase_this_month' => $purchaseThisMonth,
                'purchase_trend_percent' => $this->percentChange($purchaseLastMonth, $purchaseThisMonth),
                'receivable_debt' => $receivableDebt,
                'payable_debt' => $payableDebt,
                'account_balance_base' => $this->dashboardRepository->getTotalAccountBalanceBase($companyId),
            ],
            'operations' => $this->dashboardRepository->getOperationCounts($companyId),
            'monthly_finance' => $this->dashboardRepository->getMonthlyFinance($companyId, 6),
            'cash_flow' => $this->dashboardRepository->getMonthlyCashFlow($companyId, 6),
            'debt_trend' => $this->dashboardRepository->getMonthlyDebtTrend($companyId, 6),
            'warehouse_flow' => $this->dashboardRepository->getMonthlyWarehouseFlow($companyId, 6),
            'order_status' => $this->dashboardRepository->getOrderStatusBreakdown($companyId),
            'top_customers' => $this->dashboardRepository->getTopCustomers($companyId, 5),
            'top_suppliers' => $this->dashboardRepository->getTopSuppliers($companyId, 5),
            'recent_sales_orders' => $this->dashboardRepository->getRecentSalesOrders($companyId, 5),
            'recent_purchase_orders' => $this->dashboardRepository->getRecentPurchaseOrders($companyId, 5),
            'recent_transactions' => $this->dashboardRepository->getRecentTransactions($companyId, 5),
            'low_stock_products' => $this->dashboardRepository->getLowStockProducts($companyId, 10, 10),
        ];
    }

    public function getModuleOverview(int $companyId, string $module): array
    {
        $overview = $this->getOverview($companyId);

        return match ($module) {
            'purchase' => [
                'metrics' => [
                    ['label' => 'Giá trị mua tháng này', 'value' => $overview['finance']['purchase_this_month'], 'type' => 'money'],
                    ['label' => 'Đơn mua tháng này', 'value' => $overview['operations']['purchase_orders_this_month'], 'type' => 'number'],
                    ['label' => 'Công nợ phải trả', 'value' => $overview['finance']['payable_debt'], 'type' => 'money'],
                    ['label' => 'Nhà cung cấp hoạt động', 'value' => $overview['operations']['suppliers'], 'type' => 'number'],
                ],
                'trend' => array_map(fn ($row) => ['label' => $row['month'], 'primary' => $row['purchase'], 'secondary' => 0], $overview['monthly_finance']),
                'recent' => $overview['recent_purchase_orders'],
                'ranking' => $overview['top_suppliers'],
            ],
            'sale' => [
                'metrics' => [
                    ['label' => 'Doanh thu tháng này', 'value' => $overview['finance']['revenue_this_month'], 'type' => 'money'],
                    ['label' => 'Công nợ phải thu', 'value' => $overview['finance']['receivable_debt'], 'type' => 'money'],
                    ['label' => 'Khách hàng hoạt động', 'value' => $overview['operations']['customers'], 'type' => 'number'],
                    ['label' => 'Đơn bán tháng này', 'value' => $overview['operations']['sales_orders_this_month'], 'type' => 'number'],
                ],
                'trend' => array_map(fn ($row) => ['label' => $row['month'], 'primary' => $row['revenue'], 'secondary' => 0], $overview['monthly_finance']),
                'recent' => $overview['recent_sales_orders'],
                'ranking' => $overview['top_customers'],
            ],
            'warehouse' => [
                'metrics' => [
                    ['label' => 'Kho hoạt động', 'value' => $overview['operations']['warehouses'], 'type' => 'number'],
                    ['label' => 'Sản phẩm hoạt động', 'value' => $overview['operations']['warehouse_products'], 'type' => 'number'],
                    ['label' => 'Sản phẩm sắp hết', 'value' => count($overview['low_stock_products']), 'type' => 'number'],
                    ['label' => 'Đơn tháng này', 'value' => $overview['operations']['orders_this_month'], 'type' => 'number'],
                ],
                'trend' => array_map(fn ($row) => ['label' => $row['month'], 'primary' => $row['import'], 'secondary' => $row['export']], $overview['warehouse_flow']),
                'recent' => $overview['low_stock_products'],
                'ranking' => [],
            ],
            'accountant' => [
                'metrics' => [
                    ['label' => 'Doanh thu tháng này', 'value' => $overview['finance']['revenue_this_month'], 'type' => 'money'],
                    ['label' => 'Công nợ phải thu', 'value' => $overview['finance']['receivable_debt'], 'type' => 'money'],
                    ['label' => 'Công nợ phải trả', 'value' => $overview['finance']['payable_debt'], 'type' => 'money'],
                    ['label' => 'Tổng số dư tài khoản', 'value' => $overview['finance']['account_balance_base'], 'type' => 'money'],
                ],
                'trend' => array_map(fn ($row) => ['label' => $row['month'], 'primary' => $row['in'], 'secondary' => $row['out']], $overview['cash_flow']),
                'recent' => $overview['recent_transactions'],
                'ranking' => [],
            ],
            default => throw new \InvalidArgumentException('Phân hệ dashboard không hợp lệ.'),
        };
    }

    private function percentChange(float $previous, float $current): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
