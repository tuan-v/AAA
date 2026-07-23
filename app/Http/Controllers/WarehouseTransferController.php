<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use App\Models\WarehouseTransfer;
use App\Services\CodeGeneratorService;
use App\Services\ActivityLogService;
use App\Services\OrderQuantityValidationService;
use App\Services\InventoryMovementService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarehouseTransferController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

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

        $sourceStocks = WarehouseProductStock::where('company_id', $companyId)
            ->where('warehouse_id', $validated['from_warehouse_id'])
            ->whereIn('product_id', collect($validated['items'])->pluck('product_id'))
            ->get()
            ->keyBy('product_id');

        foreach ($validated['items'] as $index => $item) {
            $available = (float) ($sourceStocks->get($item['product_id'])?->quantity ?? 0);
            if ((float) $item['quantity'] > $available) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "items.{$index}.quantity" => "Số lượng chuyển vượt quá tồn kho nguồn ({$available}).",
                ]);
            }
        }

        $transfer = DB::transaction(function () use ($validated, $companyId, $codes) {
            $transfer = WarehouseTransfer::create([
                'company_id' => $companyId,
                'code' => $codes->generate(WarehouseTransfer::class, 'TRF', 4, $companyId),
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
            ]);
            $transfer->items()->createMany($validated['items']);
            return $transfer->load('items');
        });

        $this->notificationService->createForPermission(
            'chuyen_kho.duyet',
            $companyId,
            'Phiếu chuyển kho mới chờ duyệt',
            "Phiếu chuyển kho {$transfer->code} vừa được tạo và đang chờ duyệt.",
            [
                'warehouse_transfer_id' => $transfer->id,
                'status' => 'pending',
            ],
            '/warehouse/transfers',
            auth()->id(),
            'warehouse',
        );

        return response()->json(['message' => 'Tạo phiếu chuyển kho thành công.', 'data' => $transfer], 201);
    }

    public function approve($id, InventoryMovementService $movements)
    {
        $companyId = $this->companyId();
        $transfer = DB::transaction(function () use ($id, $companyId, $movements) {
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

                $sourceQuantityBefore = (float) $source->quantity;
                $sourceValueBefore = (float) $source->stock_value;
                $unitCost = $sourceQuantityBefore > 0 ? $sourceValueBefore / $sourceQuantityBefore : 0;
                $movedValue = round((float) $item->quantity * $unitCost, 2);

                $source->quantity = round($sourceQuantityBefore - (float) $item->quantity, 3);
                $source->stock_value = $source->quantity <= 0
                    ? 0
                    : round(max(0, $sourceValueBefore - $movedValue), 2);
                $source->save();
                $target = WarehouseProductStock::firstOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $transfer->to_warehouse_id, 'product_id' => $item->product_id],
                    ['quantity' => 0, 'stock_value' => 0]
                );
                $target = WarehouseProductStock::whereKey($target->id)->lockForUpdate()->firstOrFail();
                $targetQuantityBefore = (float) $target->quantity;
                $targetValueBefore = (float) $target->stock_value;
                $target->quantity = round($targetQuantityBefore + (float) $item->quantity, 3);
                $target->stock_value = round($targetValueBefore + $movedValue, 2);
                $target->save();

                $movements->record($source, 'transfer_out', (float) $item->quantity, $unitCost, $sourceQuantityBefore, $sourceValueBefore, $transfer);
                $movements->record($target, 'transfer_in', (float) $item->quantity, $unitCost, $targetQuantityBefore, $targetValueBefore, $transfer);
            }

            $transfer->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
            ActivityLogService::log(
                $transfer,
                'approve',
                "Duyệt phiếu chuyển kho {$transfer->code}",
                ['status' => 'pending'],
                ['status' => 'approved']
            );
            return $transfer;
        });

        if ($transfer->created_by && (int) $transfer->created_by !== (int) auth()->id()) {
            $this->notificationService->create(
                (int) $transfer->created_by,
                (int) $transfer->company_id,
                'Phiếu chuyển kho đã được duyệt',
                "Phiếu chuyển kho {$transfer->code} của bạn đã được duyệt.",
                [
                    'warehouse_transfer_id' => $transfer->id,
                    'status' => 'approved',
                ],
                '/warehouse/transfers',
                category: 'warehouse',
            );
        }

        return response()->json(['message' => 'Duyệt và cập nhật tồn kho thành công.', 'data' => $transfer]);
    }

    public function cancel($id)
    {
        $transfer = WarehouseTransfer::where('company_id', $this->companyId())->findOrFail($id);
        abort_if($transfer->status !== 'pending', 422, 'Chỉ được hủy phiếu chuyển kho đang chờ duyệt.');
        $transfer->update(['status' => 'cancelled']);
        ActivityLogService::log(
            $transfer,
            'cancel',
            "Hủy phiếu chuyển kho {$transfer->code}",
            ['status' => 'pending'],
            ['status' => 'cancelled']
        );
        return response()->json(['message' => 'Hủy phiếu chuyển kho thành công.']);
    }
}
