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
        return Warehouse::with([
            'address.province',
            'address.ward'
        ])
            ->orderByDesc('id')
            ->paginate(5)
            ->through(function ($warehouse) {
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

                    'total_inventory_value' => $warehouse->total_inventory_value,

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
            'name.required' => 'Vui long nhap ten kho',
            'province_id.required' => 'Vui long chon tinh',
            'ward_id.required' => 'Vui long chon xa',
            'address_detail.required' => 'Vui long nhap dia chi cu the'
        ]);

        $address = Address::create([
            'province_id' => $validated['province_id'],
            'ward_id' => $validated['ward_id'],
            'address_detail' => $validated['address_detail'],
        ]);

        $warehouse = Warehouse::create([
            'name' => $validated['name'],
            'address_id' => $address->id,
            'total_inventory_value' =>
            $validated['total_inventory_value'] ?? 0,
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
            'total_inventory_value' => 'nullable|numeric',
        ]);

        $warehouse->update([
            'name' => $validated['name'],
            'total_inventory_value' =>
            $validated['total_inventory_value'] ?? 0,
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
