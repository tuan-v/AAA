<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Address;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'min_inventory_value' => 'nullable|numeric|min:0',
            'max_inventory_value' => 'nullable|numeric|min:0|gte:min_inventory_value',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

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

        $currency = [
            'code'          => $companyCurrency?->code ?? 'VND',
            'symbol'        => $companyCurrency?->symbol ?? '₫',
            // Tiền mặc định của công ty luôn là tiền cơ sở.
            'exchange_rate' => 1,
        ];
        $perPage = (int) ($validated['per_page'] ?? 10);
        $query = Warehouse::with(['province', 'ward', 'stocks.product']);

        $inventoryValueQuery = fn () => DB::table('warehouse_product_stocks as wps')
            ->select('wps.warehouse_id')
            ->groupBy('wps.warehouse_id');

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (isset($validated['min_inventory_value'])) {
            $minimumQuery = $inventoryValueQuery()->havingRaw(
                'SUM(wps.stock_value) >= (? + 0)',
                [(float) $validated['min_inventory_value']]
            );
            $query->whereIn('warehouses.id', $minimumQuery);
        }

        if (isset($validated['max_inventory_value'])) {
            $maximumQuery = $inventoryValueQuery()->havingRaw(
                'SUM(wps.stock_value) <= (? + 0)',
                [(float) $validated['max_inventory_value']]
            );
            $query->where(function ($query) use ($maximumQuery) {
                $query->whereDoesntHave('stocks')
                    ->orWhereIn('warehouses.id', $maximumQuery);
            });
        }

        return $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function ($warehouse) use ($currency) {
                $totalValue = (float) $warehouse->stocks->sum('stock_value');

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
                    'total_inventory_value' => round(
                        $totalValue,
                        $currency['code'] === 'VND' ? 0 : 2
                    ),
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
            'ward_code' => [
                'required',
                Rule::exists('wards', 'id')->where(
                    fn ($query) => $query->where('province_id', $request->input('province_code'))
                ),
            ],
            'address_detail' => 'required',
        ], [
            'name.required' => 'Tên không được để trống',
            'province_code.required' => 'Tỉnh không được để trống',
            'ward_code.required' => 'Xã/Phường không được bỏ trống',
            'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống'
        ]);

        $companyId = auth()->user()->company_id;

        $warehouse = DB::transaction(function () use ($validated, $companyId) {
            $address = Address::create([
                'province_id' => $validated['province_code'],
                'ward_id' => $validated['ward_code'],
                'address_detail' => $validated['address_detail'],
            ]);

            return Warehouse::create([
                'name' => $validated['name'],
                'address_id' => $address->id,
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
            'data' => Warehouse::where('company_id', $this->companyId())
                ->where('status', 'active')
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

        $validated = $request->validate([
            'name' => 'required|max:255',
            'province_code' => 'required|exists:provinces,id',
            'ward_code' => [
                'required',
                Rule::exists('wards', 'id')->where(
                    fn ($query) => $query->where('province_id', $request->input('province_code'))
                ),
            ],
            'address_detail' => 'required',
        ], [
            'ward_code.exists' => 'Xã/Phường không thuộc Tỉnh/Thành phố đã chọn.',
        ]);

        DB::transaction(function () use ($warehouse, $validated) {
            $address = $warehouse->address ?: new Address();
            $address->fill([
                'province_id' => $validated['province_code'],
                'ward_id' => $validated['ward_code'],
                'address_detail' => $validated['address_detail'],
            ])->save();

            $warehouse->update($validated + ['address_id' => $address->id]);
        });

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
            ->where('quantity', '>', 0)
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

        $warehouse->setRelation(
            'stocks',
            $warehouse->stocks->filter(fn ($stock) => (float) $stock->quantity > 0)->values()
        );

        $company = auth()->user()->company;
        $companyCurrency = $company?->currencies()
            ->wherePivot('is_default', true)
            ->first();
        // stock_value là giá trị kế toán thực tế đã được ghi nhận bằng tiền cơ sở
        // khi duyệt phiếu; không tính lại theo giá mua hiện tại của sản phẩm.
        $inventoryValue = (float) $warehouse->stocks->sum('stock_value');

        $summary = [
            'total_products'  => $warehouse->stocks->count(),
            'total_quantity'  => $warehouse->stocks->sum('quantity'),
            'inventory_value' => round(
                $inventoryValue,
                ($companyCurrency?->code ?? 'VND') === 'VND' ? 0 : 2
            ),
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
