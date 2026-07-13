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

    private function percentChange(float $previous, float $current): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
