<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Company;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:import,export,transfer_in,transfer_out'],
            'date_from' => ['nullable', 'date', 'before_or_equal:today'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from', 'before_or_equal:today'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'date_from.date' => 'Từ ngày không hợp lệ.',
            'date_to.date' => 'Đến ngày không hợp lệ.',
            'date_to.after_or_equal' => 'Đến ngày phải lớn hơn hoặc bằng Từ ngày.',
            'date_to.before_or_equal' => 'Đến ngày không được lớn hơn ngày hôm nay.',
        ]);
        $companyId = auth()->user()->company_id ?? auth()->user()->companies()->value('companies.id');
        abort_unless($companyId, 403);

        $movements = InventoryMovement::with([
            'warehouse:id,name,code',
            'product:id,name,sku,unit_id',
            'product.unit:id,name,symbol',
        ])
            ->where('company_id', $companyId)
            ->when($validated['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($validated['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($validated['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')->paginate($validated['per_page'] ?? 20);

        $currency = Company::find($companyId)?->default_currency;
        $movements->getCollection()->each(function (InventoryMovement $movement) use ($currency) {
            $movement->setAttribute('company_currency', $currency?->only(['id', 'code', 'name', 'symbol']));
        });

        return $movements;
    }
}
