<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $company = $user->companies()->first();

        if (!$company) {
            return response()->json([
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
        return Warehouse::with([
            'province',
            'ward',
            'stocks.product'
        ])
            ->orderByDesc('id')
            ->paginate(5)
            ->through(function ($warehouse) use ($rate, $currency) {
                $totalValue = 0;

                foreach ($warehouse->stocks as $stock) {
                    $price = $stock->product?->purchase_price ?? 0;
                    // quy đổi theo công ty 
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
                    'currency_symbol' => $currency['symbol'] ?? '₫',
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

        DB::transaction(function () use ($validated, &$warehouse) {
            $warehouse = Warehouse::create([
                'name' => $validated['name'],
                'province_code' => $validated['province_code'],
                'ward_code' => $validated['ward_code'],
                'address_detail' => $validated['address_detail'],
                'company_id' => auth()->user()->company_id,
            ]);

            $warehouse->update([
                'code' => 'WH' . str_pad($warehouse->id, 5, '0', STR_PAD_LEFT)
            ]);
        });

        return response()->json([
            'message' => 'Tạo kho thành công',
            'data' => $warehouse
        ]);
    }
    public function all()
    {
        return response()->json(
            Warehouse::where('status', 'active')
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
        );
    }
    public function show($id)
    {
        return response()->json(
            Warehouse::with(['province', 'ward'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'province_code' => 'required|exists:provinces,id',
            'ward_code' => 'required|exists:wards,id',
            'address_detail' => 'required',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data' => $warehouse->fresh()
        ]);
    }
    public function toggleStatus($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $warehouse->status =
            $warehouse->status === 'active' ? 'inactive' : 'active';

        $warehouse->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'status' => $warehouse->status,
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
}
