<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use App\Models\WarehouseTransfer;
use App\Services\CodeGeneratorService;
use App\Services\OrderQuantityValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarehouseTransferController extends Controller
{
    private function companyId(): int
    {
        $id = auth()->user()->company_id ?? auth()->user()->companies()->value('companies.id');
        abort_unless($id, 403, 'Tài khoản chưa thuộc công ty nào.');
        return (int) $id;
    }

    public function index(Request $request)
    {
        return WarehouseTransfer::with(['fromWarehouse:id,name', 'toWarehouse:id,name', 'items.product.unit'])
            ->where('company_id', $this->companyId())
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()->paginate(min((int) $request->input('per_page', 10), 100));
    }

    public function store(Request $request, OrderQuantityValidationService $quantityValidator, CodeGeneratorService $codes)
    {
        $companyId = $this->companyId();
        $validated = $request->validate([
            'from_warehouse_id' => ['required', 'different:to_warehouse_id', Rule::exists('warehouses', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('status', 'active'))],
            'to_warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('status', 'active'))],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('status', 'active'))],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ], [
            'from_warehouse_id.different' => 'Kho nguồn và kho đích phải khác nhau.',
            'items.min' => 'Phiếu chuyển kho phải có ít nhất một sản phẩm.',
            'items.*.quantity.gt' => 'Số lượng chuyển phải lớn hơn 0.',
            'items.*.product_id.distinct' => 'Sản phẩm bị trùng trong phiếu chuyển warehouse.',
        ]);
        $quantityValidator->validate($validated['items']);

        $transfer = DB::transaction(function () use ($validated, $companyId, $codes) {
            $transfer = WarehouseTransfer::create([
                'company_id' => $companyId,
                'code' => $codes->generate(WarehouseTransfer::class, 'TRF', $companyId),
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
            ]);
            $transfer->items()->createMany($validated['items']);
            return $transfer->load('items');
        });

        return response()->json(['message' => 'Tạo phiếu chuyển kho thành công.', 'data' => $transfer], 201);
    }

    public function approve($id)
    {
        $companyId = $this->companyId();
        $transfer = DB::transaction(function () use ($id, $companyId) {
            $transfer = WarehouseTransfer::with('items')->where('company_id', $companyId)->lockForUpdate()->findOrFail($id);
            abort_if($transfer->status !== 'pending', 422, 'Phiếu chuyển kho đã được xử lý.');

            foreach ($transfer->items as $item) {
                WarehouseProductStock::firstOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $transfer->from_warehouse_id, 'product_id' => $item->product_id],
                    ['quantity' => 0]
                );
                $source = WarehouseProductStock::where('company_id', $companyId)
                    ->where('warehouse_id', $transfer->from_warehouse_id)->where('product_id', $item->product_id)
                    ->lockForUpdate()->firstOrFail();
                abort_if((float) $source->quantity < (float) $item->quantity, 422, 'Tồn kho nguồn không đủ cho sản phẩm ID '.$item->product_id.'.');

                $source->decrement('quantity', $item->quantity);
                $target = WarehouseProductStock::firstOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $transfer->to_warehouse_id, 'product_id' => $item->product_id],
                    ['quantity' => 0]
                );
                $target->increment('quantity', $item->quantity);
            }

            $transfer->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
            return $transfer;
        });

        return response()->json(['message' => 'Duyệt và cập nhật tồn kho thành công.', 'data' => $transfer]);
    }

    public function cancel($id)
    {
        $transfer = WarehouseTransfer::where('company_id', $this->companyId())->findOrFail($id);
        abort_if($transfer->status !== 'pending', 422, 'Chỉ được hủy phiếu chuyển kho đang chờ duyệt.');
        $transfer->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Hủy phiếu chuyển kho thành công.']);
    }
}
