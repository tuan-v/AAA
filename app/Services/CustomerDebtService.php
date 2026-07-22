<?php

namespace App\Services;

use App\Models\CustomerDebt;
use App\Models\Transaction;
use App\Models\SalesOrder;
use App\Models\WarehouseSlip;
use App\Models\Customer;

class CustomerDebtService
{
    /**
     * Phát sinh công nợ phải thu khi đơn bán được duyệt.
     *
     * Gọi từ SalesOrderService khi SO chuyển trạng thái → approved.
     * $amount truyền vào là số tiền đã quy về tiền tệ cơ sở (amount_base)
     * để nhất quán với các bút toán receivePayment / refundToCustomer bên dưới.
     *
     * Cách dùng:
     *   $customerDebtService->createFromSalesOrder($salesOrder);
     */
    public function createFromSalesOrder(SalesOrder $salesOrder): CustomerDebt
    {
        // total_amount trên SO đã tính VAT, tỷ giá đã được lưu trong exchange_rate
        $amountBase = round(
            (float) $salesOrder->total_amount * (float) $salesOrder->exchange_rate,
            2
        );

        return CustomerDebt::create([
            'customer_id'    => $salesOrder->customer_id,
            'type'           => 'sale',
            'amount'         => $amountBase,
            ...$this->moneySnapshot(
                $amountBase,
                (int) $salesOrder->currency_id,
                (float) $salesOrder->exchange_rate,
                (float) $salesOrder->total_amount
            ),
            'reference_type' => SalesOrder::class,
            'reference_id'   => $salesOrder->id,
            'note'           => "Phát sinh công nợ từ đơn bán {$salesOrder->code}",
        ]);
    }
    public function createFromWarehouseSlip(WarehouseSlip $slip): CustomerDebt
    {
        $slip->load(['saleOrder.items', 'items']);

        if ($slip->type !== 'export' || ! $slip->saleOrder) {
            throw new \RuntimeException('Phiếu xuất không gắn với đơn bán hợp lệ.');
        }
        $exists = CustomerDebt::where(
            'reference_type',
            WarehouseSlip::class
        )
            ->where('reference_id', $slip->id)
            ->exists();

        if ($exists) {
            throw new \Exception('Phiếu xuất này đã phát sinh công nợ.');
        }
        $totalAmount = 0;

        foreach ($slip->items as $item) {
            $saleItem = $slip->saleOrder
                ->items
                ->firstWhere('product_id', $item->product_id);

            if (!$saleItem) {
                throw new \RuntimeException("Sản phẩm #{$item->product_id} không thuộc đơn bán.");
            }

            $unitPrice = (float) $saleItem->company_unit_price;
            $vatPercent = (float) ($item->vat_percent ?? $saleItem->vat_percent ?? 0);
            $totalAmount += (float) $item->quantity
                * $unitPrice
                * (1 + $vatPercent / 100);
        }

        $amountBase = round($totalAmount, 2);
        return $this->createDebt(

            customerId: $slip->saleOrder->customer_id,

            amount: $amountBase,

            currencyId: (int) $slip->saleOrder->currency_id,

            exchangeRate: (float) $slip->saleOrder->exchange_rate,

            originalAmount: $amountBase / ((float) $slip->saleOrder->exchange_rate ?: 1),

            referenceType: WarehouseSlip::class,

            referenceId: $slip->id,

            note: "Phát sinh công nợ từ phiếu xuất {$slip->code}"

        );
    }
    /**
     * Phát sinh công nợ thủ công (dùng khi tạo từ invoice/hóa đơn riêng).
     *
     * LƯU Ý: $amount truyền vào đây nên là số tiền đã quy về
     * tiền tệ cơ sở (base currency), để nhất quán với receivePayment()
     * và refundToCustomer() bên dưới — tất cả bút toán công nợ
     * phải cùng đơn vị tiền tệ mới cộng dồn (sum) đúng được.
     */
    public function createDebt(
        int $customerId,
        float $amount,
        string $referenceType,
        int $referenceId,
        ?string $note = null,
        ?int $currencyId = null,
        float $exchangeRate = 1,
        ?float $originalAmount = null
    ): CustomerDebt {
        return CustomerDebt::create([
            'customer_id'    => $customerId,
            'type'           => 'sale',
            'amount'         => $amount,
            ...$this->moneySnapshot($amount, $currencyId, $exchangeRate, $originalAmount),
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'note'           => $note,
        ]);
    }

    /**
     * Giảm công nợ khi khách hàng thanh toán (type=receipt + customer_id).
     *
     * Ghi bút toán âm (-amount_base) để SUM trên customer_debts
     * phản ánh số tiền còn phải thu đúng.
     *
     * Nếu transaction->sales_order_id != null → thanh toán gắn với SO cụ thể.
     * reference_id sẽ trỏ vào Transaction để trace lại giao dịch tiền.
     */
    public function receivePayment(Transaction $transaction): CustomerDebt
    {
        if (!$transaction->customer_id) {
            throw new \RuntimeException('Transaction không có customer_id.');
        }

        $note = $transaction->sales_order_id
            ? "KH thanh toán đơn #{$transaction->sales_order_id} — Giao dịch {$transaction->code}"
            : "KH thanh toán — Giao dịch {$transaction->code}";

        return CustomerDebt::create([
            'customer_id'    => $transaction->customer_id,
            'type'           => 'payment',
            'amount'         => -abs((float) $transaction->amount_base), // âm = giảm nợ
            ...$this->moneySnapshot(
                -abs((float) $transaction->amount_base),
                (int) $transaction->currency_id,
                (float) $transaction->exchange_rate,
                -abs((float) $transaction->amount)
            ),
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => $note,
        ]);
    }

    /**
     * Hoàn tiền cho khách hàng (type=payment + customer_id).
     *
     * Trường hợp: hủy đơn đã thanh toán, trả hàng, điều chỉnh...
     * → Tăng lại công nợ phải thu (KH nhận tiền về = mình "mất" khoản thu).
     *
     * Ghi bút toán dương (+amount_base) để SUM tăng lại đúng số đã hoàn.
     */
    public function refundToCustomer(Transaction $transaction): CustomerDebt
    {
        if (!$transaction->customer_id) {
            throw new \RuntimeException('Transaction không có customer_id.');
        }

        $note = $transaction->sales_order_id
            ? "Hoàn tiền KH — Đơn #{$transaction->sales_order_id} — Giao dịch {$transaction->code}"
            : "Hoàn tiền KH — Giao dịch {$transaction->code}";

        return CustomerDebt::create([
            'customer_id'    => $transaction->customer_id,
            'type'           => 'refund',
            'amount'         => abs((float) $transaction->amount_base), // dương = tăng nợ
            ...$this->moneySnapshot(
                abs((float) $transaction->amount_base),
                (int) $transaction->currency_id,
                (float) $transaction->exchange_rate,
                abs((float) $transaction->amount)
            ),
            'reference_type' => Transaction::class,
            'reference_id'   => $transaction->id,
            'note'           => $note,
        ]);
    }

    /**
     * Tính tổng công nợ hiện tại của khách hàng (theo tiền tệ cơ sở).
     *
     * Công thức: SUM(amount) trên customer_debts
     * - Dương: tổng phải thu
     * - Âm: đã thanh toán vượt (trả trước)
     */
    public function getBalance(int $customerId): float
    {
        $openingDebt = (float) (Customer::find($customerId)?->opening_debt_base ?? 0);

        return $openingDebt
            + (float) CustomerDebt::where('customer_id', $customerId)->sum('amount');
    }

    /**
     * Lịch sử công nợ của khách hàng.
     */
    public function getHistory(int $customerId)
    {
        return CustomerDebt::where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->get();
    }

    private function moneySnapshot(
        float $amountBase,
        ?int $currencyId,
        float $exchangeRate,
        ?float $originalAmount = null
    ): array {
        $rate = $exchangeRate > 0 ? $exchangeRate : 1;

        return [
            'currency_id' => $currencyId,
            'original_amount' => round($originalAmount ?? ($amountBase / $rate), 2),
            'exchange_rate' => $rate,
            'amount_base' => round($amountBase, 2),
        ];
    }
}
