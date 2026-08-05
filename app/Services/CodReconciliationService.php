<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CodReconciliation;
use App\Models\SalesOrder;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CodReconciliationService
{
    public function __construct(private TransactionService $transactions) {}

    public function reconcile(array $data, int $companyId, int $userId): CodReconciliation
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            $orderIds = collect($data['order_ids'])->map(fn ($id) => (int) $id)->unique()->values();
            $orders = SalesOrder::query()
                ->where('company_id', $companyId)
                ->whereIn('id', $orderIds)
                ->lockForUpdate()
                ->get();

            if ($orders->count() !== $orderIds->count()) {
                throw ValidationException::withMessages(['order_ids' => 'Có đơn hàng không tồn tại hoặc không thuộc công ty.']);
            }
            foreach ($orders as $order) {
                if ($order->sales_channel !== 'storefront' || $order->payment_method !== 'cod'
                    || $order->status !== 'completed' || $order->cod_status !== 'collected') {
                    throw ValidationException::withMessages(['order_ids' => "Đơn {$order->code} chưa đủ điều kiện đối soát COD."]);
                }
                if ($order->codReconciliationItem()->exists()) {
                    throw ValidationException::withMessages(['order_ids' => "Đơn {$order->code} đã được đối soát."]);
                }
            }
            $assignedPartnerIds = $orders->pluck('shipping_partner_id')->filter()->unique();
            if ($assignedPartnerIds->count() > 1
                || ($assignedPartnerIds->count() === 1 && (int) $assignedPartnerIds->first() !== (int) $data['shipping_partner_id'])) {
                throw ValidationException::withMessages([
                    'shipping_partner_id' => 'Các đơn đã chọn không cùng đơn vị vận chuyển.',
                ]);
            }

            $currencyIds = $orders->pluck('currency_id')->unique();
            if ($currencyIds->count() !== 1) {
                throw ValidationException::withMessages(['order_ids' => 'Các đơn trong một phiếu phải cùng tiền tệ.']);
            }
            $account = Account::where('company_id', $companyId)->whereKey($data['account_id'])->lockForUpdate()->firstOrFail();
            if ((int) $account->currency_id !== (int) $currencyIds->first()) {
                throw ValidationException::withMessages(['account_id' => 'Tiền tệ tài khoản nhận phải trùng với tiền tệ của các đơn COD.']);
            }

            $codAmount = round((float) $orders->sum('cod_amount'), 2);
            $shippingFee = array_key_exists('shipping_fee', $data)
                ? round((float) $data['shipping_fee'], 2)
                : round((float) $orders->sum('carrier_shipping_fee'), 2);
            $serviceFee = array_key_exists('service_fee', $data)
                ? round((float) $data['service_fee'], 2)
                : round((float) $orders->sum('carrier_service_fee'), 2);
            $insuranceFee = array_key_exists('insurance_fee', $data)
                ? round((float) $data['insurance_fee'], 2)
                : round((float) $orders->sum('carrier_insurance_fee'), 2);
            $adjustment = round((float) ($data['adjustment_amount'] ?? 0), 2);
            $received = round($codAmount - $shippingFee - $serviceFee - $insuranceFee + $adjustment, 2);
            if ($received <= 0) {
                throw ValidationException::withMessages(['received_amount' => 'Số tiền thực nhận phải lớn hơn 0.']);
            }

            $reconciliation = CodReconciliation::create([
                'company_id' => $companyId,
                'shipping_partner_id' => $data['shipping_partner_id'],
                'code' => app(CodeGeneratorService::class)->generate(CodReconciliation::class, 'DSCOD', 5, $companyId),
                'reconciliation_date' => $data['reconciliation_date'],
                'cod_amount' => $codAmount,
                'shipping_fee' => $shippingFee,
                'service_fee' => $serviceFee,
                'insurance_fee' => $insuranceFee,
                'adjustment_amount' => $adjustment,
                'received_amount' => $received,
                'account_id' => $account->id,
                'payment_reference' => $data['payment_reference'] ?? null,
                'status' => 'approved',
                'note' => $data['note'] ?? null,
                'created_by' => $userId,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            foreach ($orders as $order) {
                $reconciliation->items()->create([
                    'sales_order_id' => $order->id,
                    'tracking_code' => $order->tracking_code,
                    'cod_amount' => $order->cod_amount,
                ]);
                $order->update([
                    'shipping_partner_id' => $data['shipping_partner_id'],
                    'cod_status' => 'reconciled',
                    'cod_reconciled_at' => now(),
                ]);
            }

            $category = TransactionCategory::firstOrCreate(
                ['company_id' => $companyId, 'code' => 'THU_COD'],
                ['name' => 'Thu tiền đối soát COD', 'type' => 'income', 'description' => 'Tiền hãng vận chuyển chuyển về sau đối soát COD', 'status' => 1]
            );
            $transaction = $this->transactions->create([
                'transaction_date' => $data['reconciliation_date'],
                'type' => 'receipt',
                'payment_method' => $account->bank_id ? 'bank_transfer' : 'cash',
                'category_id' => $category->id,
                'currency_id' => $account->currency_id,
                'amount' => $received,
                'exchange_rate' => 1,
                'to_account_id' => $account->id,
                'reference_type' => CodReconciliation::class,
                'reference_id' => $reconciliation->id,
                'description' => mb_strimwidth(
                    "Thu đối soát COD {$reconciliation->code} cho đơn: ".$orders->pluck('code')->join(', '),
                    0,
                    2000,
                    '...'
                ),
            ]);
            $transaction = $this->transactions->approve($transaction->id);
            $reconciliation->update(['transaction_id' => $transaction->id]);

            return $reconciliation->fresh(['partner', 'account', 'transaction', 'items.order.customer']);
        });
    }
}
