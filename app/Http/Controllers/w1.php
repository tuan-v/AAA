<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::with([
            'province',
            'ward',
            'stocks.product'
        ])
            ->orderByDesc('id')
            ->paginate(5)
            ->through(function ($warehouse) {

                $totalValue = 0;

                foreach ($warehouse->stocks as $stock) {
                    $price = $stock->product?->purchase_price ?? 0;
                    $totalValue += $stock->quantity * $price;
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


                    'total_inventory_value' => $totalValue,
                    'status' => $warehouse->status,
                ];
            });

        return response()->json($warehouses);
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


        return Warehouse::create($validated);
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

        $warehouse->update([
            'name' => $validated['name'],
            'province_code' => $validated['province_code'],
            'ward_code' => $validated['ward_code'],
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
            $warehouse->status === 'active' ? 'inactive' : 'active';

        $warehouse->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'status' => $warehouse->status,
        ]);
    }
}
