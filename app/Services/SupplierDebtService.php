<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SupplierDebt;
use App\Models\Transaction;
use App\Repositories\SupplierDebtRepositoryInterface;

class SupplierDebtService
{
    public function __construct(
        protected SupplierDebtRepositoryInterface $repository
    ) {
    }

    /**
     * Phát sinh công nợ phải trả khi đơn mua được duyệt.
     *
     * Gọi từ PurchaseOrderService khi PO chuyển trạng thái → approved.
     * $amount là tổng giá trị đơn mua đã quy về tiền tệ cơ sở.
     *
     * Cách dùng:
     *   $supplierDebtService->createFromPurchaseOrder($purchaseOrder);
     */
    public function createFromPurchaseOrder(PurchaseOrder $purchaseOrder): SupplierDebt
    {
        // total_amount trên PO là tổng items->sum('amount')
        // exchange_rate lưu tỷ giá tại thời điểm tạo đơn
        $totalAmount = (float) $purchaseOrder->total_amount;
        $amountBase  = round($totalAmount * (float) $purchaseOrder->exchange_rate, 2);

        return $this->repository->create([
            'supplier_id'    => $purchaseOrder->supplier_id,
            'type'           => 'invoice',
            'amount'         => $amountBase,
            'reference_type' => PurchaseOrder::class,
            'reference_id'   => $purchaseOrder->id,
            'note'           => "Phát sinh công nợ từ đơn mua {$purchaseOrder->code}",
        ]);
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
        return $this->repository->create([
            'supplier_id'    => $supplierId,
            'type'           => 'invoice',
            'amount'         => $amount,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'note'           => $note,
        ]);
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

        return $this->repository->create([
            'supplier_id'    => $transaction->supplier_id,
            'type'           => 'payment',
            'amount'         => -abs((float) $transaction->amount_base), // âm = giảm nợ
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => $note,
        ]);
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

        return $this->repository->create([
            'supplier_id'    => $transaction->supplier_id,
            'type'           => 'refund',
            'amount'         => -abs((float) $transaction->amount_base), // âm = giảm nợ phải trả
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => $note,
        ]);
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
        return $this->repository->getBalance($supplierId);
    }

    /**
     * Lịch sử công nợ của NCC.
     */
    public function getHistory(int $supplierId)
    {
        return $this->repository->getBySupplier($supplierId);
    }
}