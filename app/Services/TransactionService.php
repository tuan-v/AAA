<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransactionService extends BaseService
{
    /** Số thập phân làm tròn mặc định nếu currency không khai báo decimal_places */
    private const DEFAULT_DECIMALS = 2;

    public function __construct(
        protected TransactionRepositoryInterface $repository,
        protected AccountBalanceService $balanceService,
        protected LedgerService $ledgerService,
        protected CustomerDebtService $customerDebtService,
        protected SupplierDebtService $supplierDebtService
    ) {
    }

    // -------------------------------------------------------------------------
    // PUBLIC API
    // -------------------------------------------------------------------------

    /**
     * Tạo giao dịch mới, bao gồm:
     * 1. Validate input
     * 2. Tạo bản ghi Transaction
     * 3. Cập nhật số dư tài khoản/quỹ
     * 4. Ghi sổ kế toán (AccountLedger)
     * 5. Đồng bộ công nợ KH/NCC
     *
     * Toàn bộ được bọc trong DB::transaction để đảm bảo tính toàn vẹn dữ liệu.
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
                'code'              => $this->generateCode(),
                'transaction_date'  => $data['transaction_date'] ?? now(),
                'type'              => $data['type'],
                'category_id'       => $data['category_id'],
                'currency_id'       => $data['currency_id'],
                'amount'            => $data['amount'],
                'exchange_rate'     => $data['exchange_rate'] ?? 1,
                'amount_base'       => $amountBase,
                'from_account_id'   => $data['from_account_id'] ?? null,
                'to_account_id'     => $data['to_account_id'] ?? null,

                // Đối tượng giao dịch (KH hoặc NCC)
                'customer_id'       => $data['customer_id'] ?? null,
                'supplier_id'       => $data['supplier_id'] ?? null,

                // Đơn hàng liên quan (nếu thanh toán gắn với đơn cụ thể)
                'sales_order_id'    => $data['sales_order_id'] ?? null,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,

                // Chứng từ tham chiếu linh hoạt (polymorphic — dùng khi không
                // phải PO/SO, ví dụ: hợp đồng, phiếu nhập kho đặc biệt...)
                'reference_type'    => $data['reference_type'] ?? null,
                'reference_id'      => $data['reference_id'] ?? null,

                'description'       => $data['description'] ?? null,
                'created_by'        => $this->user()?->id,
            ]);

            // STEP 2 — Cập nhật số dư tài khoản/quỹ (có lock for update)
            $this->updateBalance($transaction);

            // STEP 3 — Ghi sổ kế toán (AccountLedger)
            $this->ledgerService->record($transaction);

            // STEP 4 — Đồng bộ công nợ KH / NCC
            $this->syncDebt($transaction);

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
    // VALIDATION
    // -------------------------------------------------------------------------

    /**
     * Validate input trước khi tạo giao dịch.
     *
     * FormRequest nên validate lại các rule này ở tầng HTTP;
     * đây là lớp bảo vệ thứ 2 ở tầng Service — giúp an toàn khi
     * TransactionService được gọi trực tiếp từ code (không qua HTTP).
     */
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

        // Transfer không gắn đối tượng KH/NCC
        if ($type === 'transfer' && (!empty($data['customer_id']) || !empty($data['supplier_id']))) {
            throw new \InvalidArgumentException(
                'Giao dịch chuyển khoản nội bộ (transfer) không được gắn khách hàng hoặc nhà cung cấp.'
            );
        }

        // Không được vừa có customer_id vừa có supplier_id
        if (!empty($data['customer_id']) && !empty($data['supplier_id'])) {
            throw new \InvalidArgumentException(
                'Giao dịch chỉ được gắn 1 đối tượng: khách hàng HOẶC nhà cung cấp, không được cả hai.'
            );
        }

        // Kiểm tra đơn hàng phù hợp với đối tượng
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
    // CẬP NHẬT SỐ DƯ TÀI KHOẢN
    // -------------------------------------------------------------------------

    /**
     * Cập nhật số dư tài khoản theo loại giao dịch.
     *
     * - receipt  : tăng to_account
     * - payment  : giảm from_account (kiểm tra đủ số dư trước)
     * - transfer : giảm from_account + tăng to_account (lock theo ID tăng dần)
     */
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
            // Lock theo thứ tự ID tăng dần để tránh deadlock khi có
            // 2 transfer ngược chiều (A→B và B→A) chạy đồng thời.
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

    /**
     * Lock 1 tài khoản, có scope theo company_id để tránh thao tác nhầm
     * sang tài khoản của công ty khác (multi-tenant safety).
     */
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

    /**
     * Quy đổi số tiền giao dịch sang đơn vị tiền tệ của tài khoản.
     *
     * QUAN TRỌNG: dùng transaction->exchange_rate (tỷ giá lưu cứng lúc tạo),
     * KHÔNG dùng tỷ giá "sống" từ bảng currencies, để tránh số dư trôi lệch
     * theo thời gian khi tỷ giá thay đổi sau này.
     */
    private function convertToAccountCurrency(Transaction $transaction, Account $account): float
    {
        $decimals = self::DEFAULT_DECIMALS;

        // Cùng tiền tệ → dùng thẳng amount gốc
        if ($account->currency_id === $transaction->currency_id) {
            return round((float) $transaction->amount, $decimals);
        }

        // Khác tiền tệ → quy qua amount_base (đã nhân exchange_rate lúc tạo)
        // rồi chia cho exchange_rate của tài khoản
        $accountRate = (float) ($account->currency->exchange_rate ?? 1);

        if ($accountRate <= 0) {
            throw new \RuntimeException(
                "Tỷ giá tiền tệ của tài khoản #{$account->id} không hợp lệ."
            );
        }

        return round((float) $transaction->amount_base / $accountRate, $decimals);
    }

    // -------------------------------------------------------------------------
    // ĐỒNG BỘ CÔNG NỢ KH / NCC
    // -------------------------------------------------------------------------

    /**
     * Xác định loại đối tượng (KH / NCC) và loại giao dịch (receipt / payment)
     * để ghi bút toán công nợ đúng chiều.
     *
     * ┌──────────────────┬──────────────┬────────────────────────────────────────────┐
     * │ type             │ đối tượng    │ Ý nghĩa công nợ                            │
     * ├──────────────────┼──────────────┼────────────────────────────────────────────┤
     * │ receipt          │ customer_id  │ KH trả tiền → GIẢM công nợ phải thu KH    │
     * │ payment          │ supplier_id  │ Trả NCC     → GIẢM công nợ phải trả NCC   │
     * │ payment          │ customer_id  │ Hoàn tiền KH → TĂNG lại công nợ phải thu  │
     * │ receipt          │ supplier_id  │ NCC hoàn    → GIẢM công nợ phải trả NCC   │
     * └──────────────────┴──────────────┴────────────────────────────────────────────┘
     *
     * Ghi chú đơn hàng:
     * - Nếu transaction->sales_order_id  != null → biết rõ thanh toán thuộc SO nào
     * - Nếu transaction->purchase_order_id != null → biết rõ thanh toán thuộc PO nào
     * - Nếu null → thanh toán chung cho đối tượng (không gắn đơn cụ thể)
     *
     * Dù có hay không có order_id, logic công nợ vẫn chạy như nhau:
     * chỉ reference_id trong CustomerDebt/SupplierDebt khác nhau.
     */
    private function syncDebt(Transaction $transaction): void
    {
        // CASE 1 — KH trả tiền cho công ty (receipt + customer)
        // → Giảm công nợ phải thu KH
        if ($transaction->isReceipt() && $transaction->customer_id) {
            $this->customerDebtService->receivePayment($transaction);
            return;
        }

        // CASE 2 — Công ty trả tiền cho NCC (payment + supplier)
        // → Giảm công nợ phải trả NCC
        if ($transaction->isPayment() && $transaction->supplier_id) {
            $this->supplierDebtService->paySupplier($transaction);
            return;
        }

        // CASE 3 — Công ty hoàn tiền cho KH (payment + customer)
        // → Tăng lại công nợ phải thu (KH không còn nợ khoản đã hoàn)
        if ($transaction->isPayment() && $transaction->customer_id) {
            $this->customerDebtService->refundToCustomer($transaction);
            return;
        }

        // CASE 4 — NCC hoàn tiền cho công ty (receipt + supplier)
        // → Giảm công nợ phải trả NCC (mình đã nhận lại tiền từ NCC)
        if ($transaction->isReceipt() && $transaction->supplier_id) {
            $this->supplierDebtService->receiveFromSupplier($transaction);
            return;
        }

        // CASE 5 — Chuyển khoản nội bộ (transfer): không ảnh hưởng công nợ
        // → Không làm gì cả
    }

    // -------------------------------------------------------------------------
    // HELPER
    // -------------------------------------------------------------------------

    /**
     * Sinh mã giao dịch duy nhất theo format: TXN-YYYYMMDDHHIISS-XXXX
     * Retry tối đa 5 lần nếu trùng (race condition khi tạo đồng thời).
     * Yêu cầu: cột `code` trên bảng transactions có UNIQUE constraint.
     */
    private function generateCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = 'TXN-' . now()->format('YmdHis') . '-' . str_pad(
                (string) random_int(0, 9999),
                4,
                '0',
                STR_PAD_LEFT
            );

            if (!Transaction::where('code', $code)->exists()) {
                return $code;
            }
        }

        // Fallback cực hiếm: thêm microtime để gần như chắc chắn unique
        return 'TXN-' . now()->format('YmdHis') . '-' . substr((string) microtime(true), -6);
    }
}