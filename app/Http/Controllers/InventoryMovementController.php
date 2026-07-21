<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:import,export,transfer_in,transfer_out'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $companyId = auth()->user()->company_id ?? auth()->user()->companies()->value('companies.id');
        abort_unless($companyId, 403);

        return InventoryMovement::with(['warehouse:id,name,code', 'product:id,name,sku'])
            ->where('company_id', $companyId)
            ->when($validated['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($validated['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($validated['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')->paginate($validated['per_page'] ?? 20);
    }
}
