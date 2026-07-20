<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SupplierDebt;
use App\Models\Transaction;
use App\Repositories\SupplierDebtRepositoryInterface;
use App\Models\WarehouseSlip;
use App\Models\Supplier;

class SupplierDebtService
{
    public function __construct(
        protected SupplierDebtRepositoryInterface $repository
    ) {}

    /**
     * Phát sinh công nợ phải trả khi đơn mua được duyệt.
     *
     * Gọi từ PurchaseOrderService khi PO chuyển trạng thái → approved.
     * $amount là tổng giá trị đơn mua đã quy về tiền tệ cơ sở.
     *
     * Cách dùng:
     *   $supplierDebtService->createFromPurchaseOrder($purchaseOrder);
     */
    public function createFromWarehouseSlip(WarehouseSlip $slip): SupplierDebt
    {
        $slip->load([
            'purchaseOrder',
            'items'
        ]);
        $exists = SupplierDebt::where(
            'reference_type',
            WarehouseSlip::class
        )
            ->where('reference_id', $slip->id)
            ->exists();

        if ($exists) {
            throw new \Exception('Phiếu nhập này đã phát sinh công nợ.');
        }
        $supplierId = $slip->purchaseOrder->supplier_id;

        $amount = $this->calculateSlipAmount($slip);

        $remainDebt = $this->offsetAdvance(
            $supplierId,
            $amount,
            $slip
        );

        if ($remainDebt <= 0) {
            $this->updateSupplierSummary($supplierId);

            return new SupplierDebt();
        }

        return $this->createDebt(
            supplierId: $supplierId,
            amount: $remainDebt,
            referenceType: WarehouseSlip::class,
            referenceId: $slip->id,
            note: "Phát sinh công nợ từ phiếu nhập {$slip->code}"
        );
    }

    private function calculateSlipAmount(WarehouseSlip $slip): float
    {
        return round(
            $slip->items->sum(function ($item) {
                return $item->quantity
                    * $item->company_price
                    * (1 + ((float) ($item->vat_percent ?? 0) / 100));
            }),
            2
        );
    }
    private function offsetAdvance(
        int $supplierId,
        float $amount,
        WarehouseSlip $slip
    ): float {

        $advance = max(
            0,
            $this->getAdvanceBalance($supplierId)
        );

        if ($advance <= 0) {
            return $amount;
        }

        $offset = min($advance, $amount);

        $this->repository->create([

            'supplier_id'    => $supplierId,
            'type'           => SupplierDebt::TYPE_ADVANCE_OFFSET,
            'amount'         => -$offset,
            'reference_type' => WarehouseSlip::class,
            'reference_id'   => $slip->id,
            'note'           => "Cấn trừ tạm ứng phiếu {$slip->code}",
        ]);
        $this->updateSupplierSummary($supplierId);

        return $amount - $offset;
    }
    /**
     * Phát sinh công nợ phải trả thủ công (dùng khi tạo từ invoice riêng).
     *
     * $amount là số tiền đã quy về tiền tệ cơ sở (base currency).
     */
    public function createDebt(
        int $supplierId,
        float $amount,
        string $referenceType,
        int $referenceId,
        ?string $note = null
    ): SupplierDebt {
        $debt = $this->repository->create([
            'supplier_id'    => $supplierId,
            'type'           => SupplierDebt::TYPE_INVOICE,
            'amount'         => $amount,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'note'           => $note,
        ]);
        $this->updateSupplierSummary($supplierId);

        return $debt;
    }

    public function getDebtBalance(int $supplierId): float
    {
        return (float) SupplierDebt::where('supplier_id', $supplierId)
            ->whereIn('type', [
                SupplierDebt::TYPE_INVOICE,
                SupplierDebt::TYPE_PAYMENT,
                SupplierDebt::TYPE_REFUND,
            ])
            ->sum('amount');
    }

    public function getOutstandingBalance(int $supplierId): float
    {
        $openingDebt = (float) (Supplier::find($supplierId)?->opening_debt ?? 0);

        return $openingDebt + $this->getDebtBalance($supplierId);
    }
    public function getAdvanceBalance(int $supplierId): float
    {
        $supplier = Supplier::find($supplierId);

        return ($supplier?->opening_advance ?? 0)
            + (float) SupplierDebt::where('supplier_id', $supplierId)
                ->whereIn('type', [
                    SupplierDebt::TYPE_ADVANCE,
                    SupplierDebt::TYPE_ADVANCE_REFUND,
                    SupplierDebt::TYPE_ADVANCE_OFFSET,
                ])
                ->sum('amount');
    }
    private function updateSupplierSummary(int $supplierId): void
    {
        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            return;
        }

        $supplier->total_debts =
            $supplier->opening_debt +
            $this->getDebtBalance($supplierId);

        $supplier->total_advance =
            $this->getAdvanceBalance($supplierId);

        $supplier->save();
    }
    /**
     * Giảm công nợ phải trả khi công ty thanh toán cho NCC (type=payment + supplier_id).
     *
     * Ghi bút toán âm (-amount_base) để SUM phản ánh số còn phải trả đúng.
     *
     * Nếu transaction->purchase_order_id != null → thanh toán gắn với PO cụ thể.
     * reference_id trỏ vào Transaction để truy vết giao dịch tiền.
     */
    public function paySupplier(Transaction $transaction): SupplierDebt
    {
        if (!$transaction->supplier_id) {
            throw new \RuntimeException('Transaction không có supplier_id.');
        }

        $note = $transaction->purchase_order_id
            ? "Thanh toán NCC — Đơn mua #{$transaction->purchase_order_id} — Giao dịch {$transaction->code}"
            : "Thanh toán NCC — Giao dịch {$transaction->code}";

        $debt = $this->repository->create([
            'supplier_id'    => $transaction->supplier_id,
            'type'           => SupplierDebt::TYPE_PAYMENT,
            'amount'         => -abs((float) $transaction->amount_base),
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => $note,
        ]);

        $this->updateSupplierSummary($transaction->supplier_id);

        return $debt;
    }

    /**
     * NCC hoàn tiền cho công ty (type=receipt + supplier_id).
     *
     * Trường hợp: trả hàng, điều chỉnh hóa đơn, NCC bồi thường...
     * → Giảm công nợ phải trả NCC (mình nhận tiền về = bớt nợ NCC).
     *
     * Ghi bút toán âm (-amount_base) cùng chiều với paySupplier.
     */
    public function receiveFromSupplier(Transaction $transaction): SupplierDebt
    {
        if (!$transaction->supplier_id) {
            throw new \RuntimeException('Transaction không có supplier_id.');
        }

        $note = $transaction->purchase_order_id
            ? "NCC hoàn tiền — Đơn mua #{$transaction->purchase_order_id} — Giao dịch {$transaction->code}"
            : "NCC hoàn tiền — Giao dịch {$transaction->code}";

        $debt = $this->repository->create([
            'supplier_id'    => $transaction->supplier_id,
            'type' => SupplierDebt::TYPE_REFUND,
            'amount'         => -abs((float) $transaction->amount_base),
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => $note,
        ]);

        $this->updateSupplierSummary($transaction->supplier_id);

        return $debt;
    }
    public function advanceSupplier(Transaction $transaction): SupplierDebt
    {
        if (!$transaction->supplier_id) {
            throw new \RuntimeException('Transaction không có supplier_id.');
        }

        $debt = $this->repository->create([
            'supplier_id'    => $transaction->supplier_id,
            'type'           => SupplierDebt::TYPE_ADVANCE,
            'amount'         => abs((float)$transaction->amount_base),
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => "Tạm ứng NCC {$transaction->code}",
        ]);

        $this->updateSupplierSummary($transaction->supplier_id);

        return $debt;
    }
    public function refundAdvance(Transaction $transaction): SupplierDebt
    {
        if (!$transaction->supplier_id) {
            throw new \RuntimeException('Transaction không có supplier_id.');
        }

        $debt = $this->repository->create([
            'supplier_id'    => $transaction->supplier_id,
            'type'           => SupplierDebt::TYPE_ADVANCE_REFUND,
            'amount'         => -abs((float)$transaction->amount_base),
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => "NCC hoàn tạm ứng {$transaction->code}",
        ]);

        $this->updateSupplierSummary($transaction->supplier_id);

        return $debt;
    }
    /**
     * Tính tổng công nợ phải trả hiện tại của NCC (theo tiền tệ cơ sở).
     *
     * Công thức: SUM(amount) trên supplier_debts
     * - Dương: tổng còn phải trả
     * - Âm: đã thanh toán nhiều hơn phát sinh (hiếm)
     */
    public function getBalance(int $supplierId): float
    {
        return $this->getDebtBalance($supplierId);
    }

    /**
     * Lịch sử công nợ của NCC.
     */
    public function getHistory(int $supplierId)
    {
        return $this->repository->getBySupplier($supplierId);
    }
}
