<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\WarehouseSlip;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfitLossReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:from_date'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
        ], [
            'to_date.before_or_equal' => 'Đến ngày không được lớn hơn ngày hôm nay.',
            'to_date.after_or_equal' => 'Đến ngày phải lớn hơn hoặc bằng từ ngày.',
        ]);

        $to = Carbon::parse($validated['to_date'] ?? now()->toDateString())->endOfDay();
        $from = Carbon::parse($validated['from_date'] ?? $to->copy()->startOfMonth()->toDateString())->startOfDay();
        $warehouseId = $validated['warehouse_id'] ?? null;

        $slips = WarehouseSlip::query()
            ->with([
                'warehouse:id,name',
                'items.product:id,name,sku,unit_id',
                'items.product.unit:id,name',
                'saleOrder:id,code,customer_id',
                'saleOrder.customer:id,name',
                'saleOrder.items:id,sales_order_id,product_id,company_unit_price,unit_price',
                'purchaseOrder:id,code,supplier_id',
                'purchaseOrder.supplier:id,name',
            ])
            ->where('company_id', $this->companyId)
            ->where('status', 'approved')
            ->whereBetween('approved_at', [$from, $to])
            ->when($warehouseId, fn($query) => $query->where('warehouse_id', $warehouseId))
            ->orderBy('approved_at')
            ->get();

        $sales = [];
        $trend = [];
        $products = [];
        $revenue = 0.0;
        $costOfGoods = 0.0;
        $purchaseValue = 0.0;
        $soldQuantity = 0.0;
        $purchasedQuantity = 0.0;
        $importSlipCount = 0;
        $importedProductIds = [];
        $soldProductIds = [];

        foreach ($slips as $slip) {
            $dateKey = optional($slip->approved_at)->format('Y-m-d') ?? $slip->created_at->format('Y-m-d');
            $trend[$dateKey] ??= ['date' => $dateKey, 'revenue' => 0.0, 'cost' => 0.0, 'profit' => 0.0, 'purchase' => 0.0];
            $slipRevenue = 0.0;
            $slipCost = 0.0;
            $slipPurchase = 0.0;

            foreach ($slip->items as $item) {
                $quantity = (float) $item->quantity;
                $productKey = (string) $item->product_id;
                $products[$productKey] ??= [
                    'product' => $item->product?->name ?? 'Sản phẩm đã xóa',
                    'sku' => $item->product?->sku,
                    'unit' => $item->product?->unit?->name ?? '',
                    'quantity' => 0.0,
                    'revenue' => 0.0,
                    'cost' => 0.0,
                    'profit' => 0.0,
                ];

                if ($slip->type === 'export') {
                    $soldProductIds[$item->product_id] = true;
                    $saleItem = $slip->saleOrder?->items->firstWhere('product_id', $item->product_id);
                    $salePrice = (float) ($saleItem?->company_unit_price ?? $saleItem?->unit_price ?? 0);
                    $storedCostPrice = (float) ($item->cost_price ?? 0);
                    $costPrice = $storedCostPrice > 0
                        ? $storedCostPrice
                        : (float) ($item->company_price ?? 0);
                    $lineRevenue = $quantity * $salePrice;
                    $storedCostAmount = (float) ($item->cost_amount ?? 0);
                    $lineCost = $storedCostAmount > 0
                        ? $storedCostAmount
                        : $quantity * $costPrice;

                    $slipRevenue += $lineRevenue;
                    $slipCost += $lineCost;
                    $soldQuantity += $quantity;
                    $products[$productKey]['quantity'] += $quantity;
                    $products[$productKey]['revenue'] += $lineRevenue;
                    $products[$productKey]['cost'] += $lineCost;
                    $products[$productKey]['profit'] += $lineRevenue - $lineCost;
                } elseif ($slip->type === 'import') {
                    $importedProductIds[$item->product_id] = true;
                    $linePurchase = $quantity * (float) ($item->company_price ?? $item->price ?? 0);
                    $slipPurchase += $linePurchase;
                    $purchasedQuantity += $quantity;
                }
            }

            if ($slip->type === 'export') {
                $revenue += $slipRevenue;
                $costOfGoods += $slipCost;
                $trend[$dateKey]['revenue'] += $slipRevenue;
                $trend[$dateKey]['cost'] += $slipCost;
                $trend[$dateKey]['profit'] += $slipRevenue - $slipCost;
                $sales[] = [
                    'date' => $dateKey,
                    'slip_code' => $slip->code,
                    'order_code' => $slip->saleOrder?->code,
                    'partner' => $slip->saleOrder?->customer?->name ?? '-',
                    'warehouse' => $slip->warehouse?->name ?? '-',
                    'revenue' => round($slipRevenue, 2),
                    'cost' => round($slipCost, 2),
                    'profit' => round($slipRevenue - $slipCost, 2),
                ];
            } else {
                $importSlipCount++;
                $purchaseValue += $slipPurchase;
                $trend[$dateKey]['purchase'] += $slipPurchase;
            }
        }

        $grossProfit = $revenue - $costOfGoods;
        $currency = Company::find($this->companyId)?->default_currency;

        return response()->json([
            'period' => ['from_date' => $from->toDateString(), 'to_date' => $to->toDateString()],
            'currency' => ['code' => $currency?->code ?? 'VND', 'symbol' => $currency?->symbol ?? '₫'],
            'summary' => [
                'purchase_value' => round($purchaseValue, 2),
                'revenue' => round($revenue, 2),
                'cost_of_goods' => round($costOfGoods, 2),
                'gross_profit' => round($grossProfit, 2),
                'margin' => $revenue > 0 ? round($grossProfit / $revenue * 100, 2) : 0,
                'sales_count' => count($sales),
                'import_slips_count' => $importSlipCount,
                'imported_products_count' => count($importedProductIds),
                'sold_products_count' => count($soldProductIds),
                'sold_quantity' => round($soldQuantity, 2),
                'purchased_quantity' => round($purchasedQuantity, 2),
            ],
            'trend' => array_values($trend),
            'top_products' => collect($products)->sortByDesc('profit')->take(10)->values(),
            'sales' => array_reverse($sales),
            'warehouses' => Warehouse::query()->where('company_id', $this->companyId)->orderBy('name')->get(['id', 'name']),
            'note' => 'Doanh thu và giá vốn được tính theo lượng hàng thực tế trên các phiếu xuất đã duyệt; số liệu không bao gồm VAT.',
        ]);
    }
}
