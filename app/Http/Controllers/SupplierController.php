<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with('currency');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {

                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query
            ->latest()
            ->paginate(5)
            ->through(function ($supplier) {

                return [
                    'id' => $supplier->id,
                    'code' => $supplier->code,
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'email' => $supplier->email,

                    'currency_id' => $supplier->currency_id,
                    'currency' => $supplier->currency,

                    'province_code' => $supplier->province_code,
                    'province_name' => $supplier->province_name,

                    'ward_code' => $supplier->ward_code,
                    'ward_name' => $supplier->ward_name,

                    'address_detail' => $supplier->address_detail,

                    'full_address' => collect([
                        $supplier->address_detail,
                        $supplier->ward_name,
                        $supplier->province_name,
                    ])->filter()->implode(', '),

                    'total_debts' => $supplier->total_debts,
                    'total_advance' => $supplier->total_advance,

                    'status' => $supplier->status,
                    'created_at' => $supplier->created_at,
                ];
            });
    }
    public function all()
    {
        return response()->json(
            Supplier::select(
                'id',
                'name',
                'currency_id',
                'code',
                'status'
            )
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
        );
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'nullable|max:20',
            'email' => 'nullable|email',

            'currency_id' => 'required|exists:currencies,id',

            'province_code' => 'nullable|string|max:50',
            'province_name' => 'nullable|string|max:255',

            'ward_code' => 'nullable|string|max:50',
            'ward_name' => 'nullable|string|max:255',

            'address_detail' => 'nullable|string|max:500',

            'total_debts' => 'nullable|numeric|min:0',
            'total_advance' => 'nullable|numeric|min:0',

            'status' => 'required|in:active,inactive',
        ]);

        $last = Supplier::latest('id')->first();

        $validated['code'] =
            'NCC' .
            str_pad(
                ($last?->id ?? 0) + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

        return Supplier::create($validated);
    }

    public function show($id)
    {
        return Supplier::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'nullable|max:20',
            'email' => 'nullable|email',

            'currency_id' => 'required|exists:currencies,id',

            'province_code' => 'nullable|string|max:50',
            'province_name' => 'nullable|string|max:255',

            'ward_code' => 'nullable|string|max:50',
            'ward_name' => 'nullable|string|max:255',

            'address_detail' => 'nullable|string|max:500',

            'total_debts' => 'nullable|numeric|min:0',
            'total_advance' => 'nullable|numeric|min:0',

            'status' => 'required|in:active,inactive',
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function toggleStatus($id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->status =
            $supplier->status === 'active'
            ? 'inactive'
            : 'active';

        $supplier->save();

        return response()->json([
            'status' => $supplier->status
        ]);
    }
}
