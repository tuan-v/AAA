<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransactionService extends BaseService
{
    private const DEFAULT_DECIMALS = 2;

    public function __construct(
        protected TransactionRepositoryInterface $repository,
        protected AccountBalanceService $balanceService,
        protected LedgerService $ledgerService,
        protected CustomerDebtService $customerDebtService,
        protected SupplierDebtService $supplierDebtService
    ) {}

    // -------------------------------------------------------------------------
    // TẠO GIAO DỊCH (CHỈ LƯU, KHÔNG TÁC ĐỘNG SỐ LIỆU)
    // -------------------------------------------------------------------------

    /**
     * Tạo giao dịch ở trạng thái "pending".
     *
     * QUAN TRỌNG: ở bước này KHÔNG cập nhật số dư, KHÔNG ghi sổ kế toán,
     * KHÔNG đồng bộ công nợ. Toàn bộ tác động chỉ xảy ra khi giao dịch
     * được duyệt qua hàm approve() bên dưới — giống luồng PO/SO
     * (tạo → duyệt → mới ảnh hưởng kho/công nợ).
     */
    public function create(array $data): Transaction
    {
        $this->validateInput($data);

        return DB::transaction(function () use ($data) {
            $amountBase = round(
                $data['amount'] * ($data['exchange_rate'] ?? 1),
                self::DEFAULT_DECIMALS
            );

            $transaction = $this->repository->create([
                'company_id'        => $this->companyId(),
                'transaction_date'  => $data['transaction_date'] ?? now(),
                'type'              => $data['type'],
                'category_id'       => $data['category_id'],
                'currency_id'       => $data['currency_id'],
                'amount'            => $data['amount'],
                'exchange_rate'     => $data['exchange_rate'] ?? 1,
                'amount_base'       => $amountBase,
                'from_account_id'   => $data['from_account_id'] ?? null,
                'to_account_id'     => $data['to_account_id'] ?? null,
                'customer_id'       => $data['customer_id'] ?? null,
                'supplier_id'       => $data['supplier_id'] ?? null,
                'sales_order_id'    => $data['sales_order_id'] ?? null,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'reference_type'    => $data['reference_type'] ?? null,
                'reference_id'      => $data['reference_id'] ?? null,
                'description'       => $data['description'] ?? null,
                'created_by'        => $this->user()?->id,

                // MỚI: mặc định pending, chưa tác động gì tới số liệu
                'status'            => 'pending',
            ]);

            return $transaction->fresh([
                'fromAccount',
                'toAccount',
                'currency',
                'category',
                'customer',
                'supplier',
                'salesOrder',
                'purchaseOrder',
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // DUYỆT GIAO DỊCH (ĐÂY LÀ NƠI DUY NHẤT TÁC ĐỘNG SỐ LIỆU)
    // -------------------------------------------------------------------------

    /**
     * Duyệt 1 giao dịch đang pending:
     * 1. Lock bản ghi transaction (tránh duyệt trùng do double-click/2 tab)
     * 2. Cập nhật số dư tài khoản/quỹ
     * 3. Ghi sổ kế toán (AccountLedger)
     * 4. Đồng bộ công nợ KH/NCC
     * 5. Đánh dấu approved
     *
     * Toàn bộ bọc trong DB::transaction để đảm bảo toàn vẹn — nếu bất kỳ
     * bước nào lỗi (vd không đủ số dư), giao dịch vẫn giữ nguyên "pending".
     */
    public function approve(int $transactionId): Transaction
    {
        return DB::transaction(function () use ($transactionId) {
            $transaction = Transaction::where('id', $transactionId)
                ->where('company_id', $this->companyId())
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                throw new \RuntimeException('Không tìm thấy giao dịch.');
            }

            if ($transaction->status !== 'pending') {
                throw new \RuntimeException(
                    "Giao dịch này đã ở trạng thái '{$transaction->status}', không thể duyệt lại."
                );
            }

            // Toàn bộ tác động số liệu chỉ xảy ra ở đây, khi duyệt
            $this->updateBalance($transaction);
            $this->ledgerService->record($transaction);
            $this->syncDebt($transaction);

            $transaction->update([
                'status'      => 'approved',
                'approved_by' => $this->user()?->id,
                'approved_at' => now(),
            ]);

            return $transaction->fresh([
                'fromAccount',
                'toAccount',
                'currency',
                'category',
                'customer',
                'supplier',
                'salesOrder',
                'purchaseOrder',
            ]);
        });
    }

    /**
     * Từ chối 1 giao dịch đang pending — không tác động số liệu gì cả,
     * chỉ đổi trạng thái để không thể duyệt lại (khác "xóa" — vẫn giữ lịch sử).
     */
    public function reject(int $transactionId): Transaction
    {
        $transaction = Transaction::where('id', $transactionId)
            ->where('company_id', $this->companyId())
            ->lockForUpdate()
            ->first();

        if (! $transaction) {
            throw new \RuntimeException('Không tìm thấy giao dịch.');
        }

        if ($transaction->status !== 'pending') {
            throw new \RuntimeException(
                "Giao dịch này đã ở trạng thái '{$transaction->status}', không thể từ chối."
            );
        }

        $transaction->update([
            'status'      => 'rejected',
            'approved_by' => $this->user()?->id,
            'approved_at' => now(),
        ]);

        return $transaction;
    }

    // -------------------------------------------------------------------------
    // VALIDATION
    // -------------------------------------------------------------------------

    private function validateInput(array $data): void
    {
        if (empty($data['amount']) || $data['amount'] <= 0) {
            throw new \InvalidArgumentException('Số tiền giao dịch phải lớn hơn 0.');
        }

        if (($data['exchange_rate'] ?? 1) <= 0) {
            throw new \InvalidArgumentException('Tỷ giá không hợp lệ.');
        }

        $type = $data['type'];

        if ($type === 'receipt' && empty($data['to_account_id'])) {
            throw new \InvalidArgumentException(
                'Giao dịch thu (receipt) phải có tài khoản đích (to_account_id).'
            );
        }

        if ($type === 'payment' && empty($data['from_account_id'])) {
            throw new \InvalidArgumentException(
                'Giao dịch chi (payment) phải có tài khoản nguồn (from_account_id).'
            );
        }

        if ($type === 'transfer') {
            if (empty($data['from_account_id']) || empty($data['to_account_id'])) {
                throw new \InvalidArgumentException(
                    'Giao dịch chuyển khoản (transfer) phải có cả tài khoản nguồn và đích.'
                );
            }

            if ($data['from_account_id'] === $data['to_account_id']) {
                throw new \InvalidArgumentException(
                    'Tài khoản nguồn và đích không được trùng nhau.'
                );
            }
        }

        if ($type === 'transfer' && (!empty($data['customer_id']) || !empty($data['supplier_id']))) {
            throw new \InvalidArgumentException(
                'Giao dịch chuyển khoản nội bộ (transfer) không được gắn khách hàng hoặc nhà cung cấp.'
            );
        }

        if (!empty($data['customer_id']) && !empty($data['supplier_id'])) {
            throw new \InvalidArgumentException(
                'Giao dịch chỉ được gắn 1 đối tượng: khách hàng HOẶC nhà cung cấp, không được cả hai.'
            );
        }

        if (!empty($data['sales_order_id']) && empty($data['customer_id'])) {
            throw new \InvalidArgumentException(
                'Giao dịch gắn đơn bán (sales_order_id) phải có customer_id tương ứng.'
            );
        }

        if (!empty($data['purchase_order_id']) && empty($data['supplier_id'])) {
            throw new \InvalidArgumentException(
                'Giao dịch gắn đơn mua (purchase_order_id) phải có supplier_id tương ứng.'
            );
        }
    }

    // -------------------------------------------------------------------------
    // CẬP NHẬT SỐ DƯ TÀI KHOẢN (giữ nguyên logic cũ, giờ chỉ gọi từ approve())
    // -------------------------------------------------------------------------

    private function updateBalance(Transaction $transaction): void
    {
        if ($transaction->type === 'receipt') {
            $account = $this->lockAccount($transaction->to_account_id);
            $amount  = $this->convertToAccountCurrency($transaction, $account);
            $this->balanceService->increase($account, $amount);
            return;
        }

        if ($transaction->type === 'payment') {
            $account = $this->lockAccount($transaction->from_account_id);
            $amount  = $this->convertToAccountCurrency($transaction, $account);

            if ($account->current_balance < $amount) {
                throw new \RuntimeException(
                    "Số dư tài khoản '{$account->code}' không đủ để thực hiện giao dịch."
                );
            }

            $this->balanceService->decrease($account, $amount);
            return;
        }

        if ($transaction->type === 'transfer') {
            $ids = [$transaction->from_account_id, $transaction->to_account_id];
            sort($ids);

            $accounts = Account::with('currency')
                ->whereIn('id', $ids)
                ->where('company_id', $this->companyId())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $from = $accounts->get($transaction->from_account_id);
            $to   = $accounts->get($transaction->to_account_id);

            if (!$from) {
                throw new \RuntimeException('Không tìm thấy tài khoản nguồn.');
            }
            if (!$to) {
                throw new \RuntimeException('Không tìm thấy tài khoản đích.');
            }

            $fromAmount = $this->convertToAccountCurrency($transaction, $from);
            $toAmount   = $this->convertToAccountCurrency($transaction, $to);

            if ($from->current_balance < $fromAmount) {
                throw new \RuntimeException(
                    "Số dư tài khoản nguồn '{$from->code}' không đủ để thực hiện chuyển khoản."
                );
            }

            $this->balanceService->decrease($from, $fromAmount);
            $this->balanceService->increase($to, $toAmount);
        }
    }

    private function lockAccount(?int $accountId): Account
    {
        if (!$accountId) {
            throw new \RuntimeException('Thiếu account_id.');
        }

        $account = Account::with('currency')
            ->where('id', $accountId)
            ->where('company_id', $this->companyId())
            ->lockForUpdate()
            ->first();

        if (!$account) {
            throw new \RuntimeException(
                "Không tìm thấy tài khoản #{$accountId} thuộc công ty hiện tại."
            );
        }

        return $account;
    }

    private function convertToAccountCurrency(Transaction $transaction, Account $account): float
    {
        $decimals = self::DEFAULT_DECIMALS;

        if ($account->currency_id === $transaction->currency_id) {
            return round((float) $transaction->amount, $decimals);
        }

        $accountRate = (float) ($account->currency->exchange_rate ?? 1);

        if ($accountRate <= 0) {
            throw new \RuntimeException(
                "Tỷ giá tiền tệ của tài khoản #{$account->id} không hợp lệ."
            );
        }

        return round((float) $transaction->amount_base / $accountRate, $decimals);
    }

    // -------------------------------------------------------------------------
    // ĐỒNG BỘ CÔNG NỢ KH / NCC (giữ nguyên logic cũ, giờ chỉ gọi từ approve())
    // -------------------------------------------------------------------------

    private function syncDebt(Transaction $transaction): void
    {
        if ($transaction->isReceipt() && $transaction->customer_id) {
            $this->customerDebtService->receivePayment($transaction);
            return;
        }

        if ($transaction->isPayment() && $transaction->supplier_id) {
            $this->supplierDebtService->paySupplier($transaction);
            return;
        }

        if ($transaction->isPayment() && $transaction->customer_id) {
            $this->customerDebtService->refundToCustomer($transaction);
            return;
        }

        if ($transaction->isReceipt() && $transaction->supplier_id) {
            $this->supplierDebtService->receiveFromSupplier($transaction);
            return;
        }
    }
}
