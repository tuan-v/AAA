<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    private function companyId(): int
    {
        $companyId = auth()->user()->company_id
            ?? auth()->user()->companies()->value('companies.id');
        abort_unless($companyId, 403, 'Tài khoản chưa thuộc công ty nào.');
        return (int) $companyId;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $user->company; // belongsTo, 1 user - 1 company

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'User chưa thuộc công ty'
            ], 422);
        }

        $companyCurrency = $company->currencies()
            ->wherePivot('is_default', true)
            ->first();

        $rate = $companyCurrency?->exchange_rate ?? 1;

        $currency = [
            'code'          => $companyCurrency?->code ?? 'VND',
            'symbol'        => $companyCurrency?->symbol ?? '₫',
            'exchange_rate' => $rate,
        ];
        $perPage = min((int) $request->input('per_page', 10), 100);
        return Warehouse::with(['province', 'ward', 'stocks.product'])
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function ($warehouse) use ($rate, $currency) {
                $totalValue = 0;

                foreach ($warehouse->stocks as $stock) {
                    $price = $stock->product?->purchase_price ?? 0;
                    $converted = $rate > 0 ? $price / $rate : 0;
                    $totalValue += $stock->quantity * $converted;
                }

                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'address_detail' => $warehouse->address_detail,
                    'full_address' => trim(
                        ($warehouse->address_detail ?? '') . ', ' .
                            ($warehouse->ward->name ?? '') . ', ' .
                            ($warehouse->province->name ?? '')
                    ),
                    'province_code' => $warehouse->province_code,
                    'ward_code' => $warehouse->ward_code,
                    'total_inventory_value' => round($totalValue, 2),
                    'currency_symbol' => $currency['symbol'],
                    'status' => $warehouse->status,
                ];
            });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'province_code' => 'required|exists:provinces,id',
            'ward_code' => 'required|exists:wards,id',
            'address_detail' => 'required',
        ], [
            'name.required' => 'Tên không được để trống',
            'province_code.required' => 'Tỉnh không được để trống',
            'ward_code.required' => 'Xã/Phường không được bỏ trống',
            'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống'
        ]);

        $companyId = auth()->user()->company_id;

        $warehouse = DB::transaction(function () use ($validated, $companyId) {
            return Warehouse::create([
                'name' => $validated['name'],
                'province_code' => $validated['province_code'],
                'ward_code' => $validated['ward_code'],
                'address_detail' => $validated['address_detail'],
                'company_id' => $companyId,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Tạo kho thành công',
            'data' => $warehouse
        ]);
    }

    public function all()
    {
        return response()->json([
            'success' => true,
            'data' => Warehouse::where('status', 'active')
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => Warehouse::with(['province', 'ward'])
                ->where('company_id', $this->companyId())->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::where('company_id', $this->companyId())->findOrFail($id);

        if ($warehouse->slips()->exists() || $warehouse->stocks()->exists()) {
            return response()->json([
                'message' => 'Kho đã phát sinh tồn kho hoặc phiếu kho, không thể chỉnh sửa. Bạn chỉ có thể khóa hoặc mở khóa.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'province_code' => 'required|exists:provinces,id',
            'ward_code' => 'required|exists:wards,id',
            'address_detail' => 'required',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công',
            'data' => $warehouse->fresh()
        ]);
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::where('company_id', $this->companyId())->findOrFail($id);

        if ($warehouse->slips()->exists() || $warehouse->stocks()->exists()) {
            return response()->json([
                'message' => 'Kho đã được sử dụng, không thể xóa. Bạn có thể chuyển sang trạng thái khóa.',
            ], 422);
        }

        $warehouse->delete();

        return response()->json(['message' => 'Xóa kho thành công.']);
    }

    public function toggleStatus($id)
    {
        $warehouse = Warehouse::where('company_id', $this->companyId())->findOrFail($id);
        $warehouse->status = $warehouse->status === 'active' ? 'inactive' : 'active';
        $warehouse->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công',
            'data' => ['status' => $warehouse->status],
        ]);
    }

    public function products()
    {
        return Inertia::render('Warehouse/Product/Index');
    }

    public function import()
    {
        return Inertia::render('Warehouse/Import/Index');
    }

    public function export()
    {
        return Inertia::render('Warehouse/Export/Index');
    }

    /**
     * API lấy danh sách sản phẩm cùng số lượng tồn kho (dùng cho trang Detail Vue)
     */
    public function getStocks(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|integer'
        ]);

        // Dùng Eloquent để global scope company_id của Warehouse tự áp dụng
        // -> warehouse thuộc công ty khác sẽ tự động 404
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $stocks = WarehouseProductStock::with(['product.unit'])
            ->where('warehouse_id', $warehouse->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stocks
        ]);
    }

    public function detail(Warehouse $warehouse)
    {
        $warehouse->load([
            'province',
            'ward',
            'stocks.product.category',
            'stocks.product.unit',
            'slips.items.product',
            'slips.createdBy',
            'slips.approvedBy',
        ]);

        $company = auth()->user()->company;
        $companyCurrency = $company?->currencies()
            ->wherePivot('is_default', true)
            ->first();
        $rate = $companyCurrency?->exchange_rate ?? 1;

        $inventoryValue = $warehouse->stocks->sum(function ($stock) use ($rate) {
            $price = $stock->product?->purchase_price ?? 0;
            $converted = $rate > 0 ? $price / $rate : 0;
            return $stock->quantity * $converted;
        });

        $summary = [
            'total_products'  => $warehouse->stocks->count(),
            'total_quantity'  => $warehouse->stocks->sum('quantity'),
            'inventory_value' => round($inventoryValue, 2),
            'currency_symbol' => $companyCurrency?->symbol ?? '₫',
            'import_slips'    => $warehouse->slips->where('type', 'import')->count(),
            'export_slips'    => $warehouse->slips->where('type', 'export')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => [
                'warehouse' => $warehouse,
                'summary'   => $summary,
            ],
        ]);
    }
}
