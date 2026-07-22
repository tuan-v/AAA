<?php

namespace App\Repositories;

use Illuminate\Support\Carbon;

interface DashboardRepositoryInterface
{
    /**
     * Tổng doanh thu (sales_orders.total_amount) đã duyệt trong khoảng thời gian.
     */
    public function getSalesRevenue(
        int $companyId,
        Carbon $from,
        Carbon $to
    ): float;

    /**
     * Tổng chi phí mua hàng (purchase_order_items.amount) của các đơn đã duyệt/hoàn tất
     * trong khoảng thời gian.
     */
    public function getPurchaseCost(int $companyId, Carbon $from, Carbon $to): float;

    /**
     * Tổng công nợ phải thu hiện tại (KH) - tổng dồn tất cả customer_debts.amount (có dấu).
     */
    public function getTotalReceivableDebt(int $companyId): float;

    /**
     * Tổng công nợ phải trả hiện tại (NCC) - tổng dồn tất cả supplier_debts.amount (có dấu).
     */
    public function getTotalPayableDebt(int $companyId): float;

    public function getTotalAccountBalanceBase(int $companyId): float;

    /**
     * Số lượng bản ghi đang active của từng danh mục vận hành.
     * Trả về mảng ['users' => n, 'customers' => n, 'suppliers' => n, 'products' => n,
     *              'warehouses' => n, 'sales_orders_this_month' => n,
     *              'purchase_orders_this_month' => n, 'orders_this_month' => n]
     */
    public function getOperationCounts(int $companyId): array;

    /**
     * Doanh thu & chi phí mua hàng theo từng tháng trong N tháng gần nhất.
     * Trả về mảng [['month' => 'T2/2026', 'revenue' => .., 'purchase' => ..], ...]
     */
    public function getMonthlyFinance(int $companyId, int $months = 6): array;

    /**
     * Dòng tiền vào/ra (transactions loại receipt/payment, status posted) theo tháng.
     */
    public function getMonthlyCashFlow(int $companyId, int $months = 6): array;

    /**
     * Biến động công nợ (số dư cuối mỗi tháng) của KH và NCC.
     */
    public function getMonthlyDebtTrend(int $companyId, int $months = 6): array;

    /**
     * Số phiếu nhập/xuất kho theo tháng (warehouse_slips, status = approved).
     */
    public function getMonthlyWarehouseFlow(int $companyId, int $months = 6): array;

    /**
     * Số lượng đơn hàng (PO + SO gộp) theo nhóm trạng thái: pending / approved / cancelled.
     */
    public function getOrderStatusBreakdown(int $companyId): array;

    /**
     * Top N khách hàng theo tổng giá trị đơn bán đã duyệt (toàn thời gian).
     */
    public function getTopCustomers(int $companyId, int $limit = 5): array;

    /**
     * Top N nhà cung cấp theo tổng giá trị đơn mua đã duyệt/hoàn tất (toàn thời gian).
     */
    public function getTopSuppliers(int $companyId, int $limit = 5): array;

    /**
     * N đơn bán hàng gần nhất.
     */
    public function getRecentSalesOrders(int $companyId, int $limit = 5): array;

    /**
     * N đơn mua hàng gần nhất.
     */
    public function getRecentPurchaseOrders(int $companyId, int $limit = 5): array;

    /**
     * N giao dịch kế toán gần nhất.
     */
    public function getRecentTransactions(int $companyId, int $limit = 5): array;

    /**
     * Danh sách sản phẩm sắp hết hàng (tồn kho dưới ngưỡng).
     * NOTE: hiện dùng ngưỡng cố định do chưa có cột min_quantity trong schema.
     */
    public function getLowStockProducts(int $companyId, int $threshold = 10, int $limit = 10): array;
}
