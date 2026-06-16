<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Warehouse;
use Illuminate\Http\Request;
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
            'address.province',
            'address.ward',
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
                    'province_id' => $warehouse->address->province_id,
                    'ward_id' => $warehouse->address->ward_id,
                    'address_detail' => $warehouse->address->address_detail,

                    'address' =>
                    $warehouse->address->address_detail . ', ' .
                        $warehouse->address->ward->name . ', ' .
                        $warehouse->address->province->name,

                    'total_inventory_value' => round($totalValue, 2),
                    'currency_symbol' => $currency?->symbol ?? '₫',
                    'status' => $warehouse->status,
                ];
            });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'province_id' => 'required|exists:provinces,id',
            'ward_id' => 'required|exists:wards,id',
            'address_detail' => 'required',
            'total_inventory_value' => 'nullable|numeric',
        ], [
            'name.required' => 'Vui lòng nhập tên kho',
            'province_id.required' => 'Vui lòng chọn tỉnh',
            'ward_id.required' => 'Vui lòng chọn xã',
            'address_detail.required' => 'Vui lòng nhập địa chỉ cụ thể'
        ]);

        $address = Address::create([
            'province_id' => $validated['province_id'],
            'ward_id' => $validated['ward_id'],
            'address_detail' => $validated['address_detail'],
        ]);

        $warehouse = Warehouse::create([
            'name' => $validated['name'],
            'address_id' => $address->id,

        ]);

        return $warehouse->load([
            'address.province',
            'address.ward'
        ]);
    }

    public function show($id)
    {
        return Warehouse::with([
            'address.province',
            'address.ward'
        ])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::with('address')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'province_id' => 'required|exists:provinces,id',
            'ward_id' => 'required|exists:wards,id',
            'address_detail' => 'required',
        ]);

        $warehouse->update([
            'name' => $validated['name'],
        ]);

        $warehouse->address->update([
            'province_id' => $validated['province_id'],
            'ward_id' => $validated['ward_id'],
            'address_detail' => $validated['address_detail'],
        ]);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function toggleStatus($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $warehouse->status =
            $warehouse->status === 'active'
            ? 'inactive'
            : 'active';

        $warehouse->save();

        return response()->json([
            'message' => 'Cập nhật thành công',
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
