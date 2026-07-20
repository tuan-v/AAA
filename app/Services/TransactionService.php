<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\SupplierDebt;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\WarehouseSlip;
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
        $data = $this->normalizeAndValidateRelations($data);
        $this->validateInput($data);
        $this->validateRequestedOutstanding($data);

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

    public function update(int $transactionId, array $data): Transaction
    {
        $data = $this->normalizeAndValidateRelations($data);
        $this->validateInput($data);
        $this->validateRequestedOutstanding($data);

        return DB::transaction(function () use ($transactionId, $data) {
            $transaction = Transaction::whereKey($transactionId)
                ->where('company_id', $this->companyId())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $transaction->isPending()) {
                throw new \RuntimeException('Chỉ được chỉnh sửa giao dịch đang chờ duyệt.');
            }

            $transaction->update([
                'transaction_date' => $data['transaction_date'] ?? now(),
                'type' => $data['type'],
                'category_id' => $data['category_id'],
                'currency_id' => $data['currency_id'],
                'amount' => $data['amount'],
                'exchange_rate' => $data['exchange_rate'],
                'amount_base' => round($data['amount'] * $data['exchange_rate'], self::DEFAULT_DECIMALS),
                'from_account_id' => $data['from_account_id'] ?? null,
                'to_account_id' => $data['to_account_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            return $transaction->fresh(['fromAccount', 'toAccount', 'currency', 'category', 'customer', 'supplier', 'salesOrder', 'purchaseOrder']);
        });
    }

    public function delete(int $transactionId): void
    {
        DB::transaction(function () use ($transactionId) {
            $transaction = Transaction::whereKey($transactionId)
                ->where('company_id', $this->companyId())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $transaction->isPending()) {
                throw new \RuntimeException('Chỉ được xóa giao dịch đang chờ duyệt. Giao dịch đã xử lý phải được lưu lịch sử.');
            }

            $transaction->delete();
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

            $this->validateOutstandingDebt($transaction);

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
    public function reject(int $transactionId, ?string $reason = null): Transaction
    {
        return DB::transaction(function () use ($transactionId, $reason) {
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
                'status' => 'rejected',
                'rejected_by' => $this->user()?->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $transaction->fresh(['rejectedBy']);
        });
    }

    private function normalizeAndValidateRelations(array $data): array
    {
        $companyId = $this->companyId();
        $data['currency_id'] = $this->deriveCurrencyId($data, $companyId);
        $currency = Currency::whereKey($data['currency_id'])->first();
        $companyHasCurrency = $currency && $this->user()?->companies()
            ->where('companies.id', $companyId)
            ->whereHas('currencies', fn ($query) => $query->where('currencies.id', $currency->id))
            ->exists();

        if (! $companyHasCurrency) {
            throw new \InvalidArgumentException('Tiền tệ không thuộc công ty hiện tại.');
        }

        $data['exchange_rate'] = (float) ($currency->exchange_rate ?: 1);

        foreach (['from_account_id', 'to_account_id'] as $field) {
            if (! empty($data[$field]) && ! Account::whereKey($data[$field])->where('company_id', $companyId)->exists()) {
                throw new \InvalidArgumentException('Tài khoản giao dịch không thuộc công ty hiện tại.');
            }
        }
        if (! empty($data['customer_id']) && ! Customer::whereKey($data['customer_id'])->where('company_id', $companyId)->exists()) {
            throw new \InvalidArgumentException('Khách hàng không thuộc công ty hiện tại.');
        }
        if (! empty($data['supplier_id']) && ! Supplier::whereKey($data['supplier_id'])->where('company_id', $companyId)->exists()) {
            throw new \InvalidArgumentException('Nhà cung cấp không thuộc công ty hiện tại.');
        }

        if (! empty($data['sales_order_id'])) {
            $order = SalesOrder::whereKey($data['sales_order_id'])->where('company_id', $companyId)->first();
            if (! $order) throw new \InvalidArgumentException('Đơn bán không thuộc công ty hiện tại.');
            if ((int) $order->customer_id !== (int) ($data['customer_id'] ?? 0)) throw new \InvalidArgumentException('Đơn bán không thuộc khách hàng đã chọn.');
            if ((int) $order->currency_id !== (int) $data['currency_id']) throw new \InvalidArgumentException('Tiền tệ giao dịch phải trùng với tiền tệ đơn bán.');
        }

        if (! empty($data['purchase_order_id'])) {
            $order = PurchaseOrder::whereKey($data['purchase_order_id'])->where('company_id', $companyId)->first();
            if (! $order) throw new \InvalidArgumentException('Đơn mua không thuộc công ty hiện tại.');
            if ((int) $order->supplier_id !== (int) ($data['supplier_id'] ?? 0)) throw new \InvalidArgumentException('Đơn mua không thuộc nhà cung cấp đã chọn.');
            if ((int) $order->currency_id !== (int) $data['currency_id']) throw new \InvalidArgumentException('Tiền tệ giao dịch phải trùng với tiền tệ đơn mua.');
        }

        return $data;
    }

    private function deriveCurrencyId(array $data, int $companyId): int
    {
        if (! empty($data['sales_order_id'])) {
            $order = SalesOrder::whereKey($data['sales_order_id'])->where('company_id', $companyId)->first();
            if (! $order) throw new \InvalidArgumentException('Đơn bán không thuộc công ty hiện tại.');
            return (int) $order->currency_id;
        }
        if (! empty($data['purchase_order_id'])) {
            $order = PurchaseOrder::whereKey($data['purchase_order_id'])->where('company_id', $companyId)->first();
            if (! $order) throw new \InvalidArgumentException('Đơn mua không thuộc công ty hiện tại.');
            return (int) $order->currency_id;
        }

        $accountId = $data['type'] === 'receipt' ? ($data['to_account_id'] ?? null) : ($data['from_account_id'] ?? null);
        $account = $accountId ? Account::whereKey($accountId)->where('company_id', $companyId)->first() : null;
        if (! $account) throw new \InvalidArgumentException('Hãy chọn tài khoản để hệ thống xác định tiền tệ giao dịch.');
        return (int) $account->currency_id;
    }

    private function validateRequestedOutstanding(array $data): void
    {
        $this->validateOutstandingDebt(new Transaction([
            ...$data,
            'amount_base' => round((float) $data['amount'] * (float) $data['exchange_rate'], self::DEFAULT_DECIMALS),
        ]));
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

        if ($type === 'receipt' && !empty($data['customer_id']) && empty($data['sales_order_id'])) {
            throw new \InvalidArgumentException('Thu tiền khách hàng bắt buộc phải chọn đơn bán.');
        }

        if (!empty($data['purchase_order_id']) && empty($data['supplier_id'])) {
            throw new \InvalidArgumentException(
                'Giao dịch gắn đơn mua (purchase_order_id) phải có supplier_id tương ứng.'
            );
        }

        if ($type === 'payment' && !empty($data['supplier_id']) && empty($data['purchase_order_id'])) {
            throw new \InvalidArgumentException('Thanh toán nhà cung cấp bắt buộc phải chọn đơn mua.');
        }

        // MỚI: chặn category không khớp loại giao dịch (thu/chi/chuyển khoản)
        $this->validateCategoryType($type, $data['category_id'] ?? null);
    }

    /**
     * Đảm bảo loại thanh toán (category) được chọn phù hợp với loại giao dịch.
     * Lưu ý: transaction.type dùng receipt/payment/transfer,
     * còn transaction_categories.type dùng income/expense/transfer,
     * nên cần ánh xạ (map) qua lại thay vì so sánh trực tiếp.
     */
    private function validateCategoryType(string $transactionType, ?int $categoryId): void
    {
        if (!$categoryId) {
            return;
        }

        $category = \App\Models\TransactionCategory::where('id', $categoryId)
            ->where('company_id', $this->companyId())
            ->first();

        if (!$category) {
            throw new \InvalidArgumentException('Loại thanh toán không hợp lệ hoặc không thuộc công ty hiện tại.');
        }

        // Ánh xạ transaction.type -> category.type tương ứng
        $expectedCategoryType = match ($transactionType) {
            'receipt'  => 'income',
            'payment'  => 'expense',
            'transfer' => 'transfer',
            default    => null,
        };

        // Nếu category không gắn type (NULL) thì coi như dùng chung, không chặn
        if (empty($category->type) || $expectedCategoryType === null) {
            return;
        }

        if ($category->type !== $expectedCategoryType) {
            $transactionLabels = [
                'receipt'  => 'Thu tiền',
                'payment'  => 'Chi tiền',
                'transfer' => 'Chuyển khoản',
            ];

            $categoryLabels = [
                'income'   => 'Thu tiền',
                'expense'  => 'Chi tiền',
                'transfer' => 'Chuyển khoản',
            ];

            $categoryLabel = $categoryLabels[$category->type] ?? $category->type;
            $transactionLabel = $transactionLabels[$transactionType] ?? $transactionType;

            throw new \InvalidArgumentException(
                "Loại thanh toán \"{$category->name}\" ({$category->code}) chỉ dùng cho giao dịch {$categoryLabel}, không phù hợp với giao dịch {$transactionLabel} bạn đang tạo."
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

        if ($transaction->supplier_id) {

            $transaction->loadMissing('category');

            if ($transaction->isPayment()) {

                switch ($transaction->category->code) {

                    case 'CHI_NCC':
                        $this->supplierDebtService->paySupplier($transaction);
                        break;

                    case 'TAM_UNG_NCC':
                        $this->supplierDebtService->advanceSupplier($transaction);
                        break;
                }

                return;
            }

            if ($transaction->isReceipt()) {

                switch ($transaction->category->code) {

                    case 'HOAN_TAM_UNG_NCC':
                        $this->supplierDebtService->refundAdvance($transaction);
                        break;

                    default:
                        $this->supplierDebtService->receiveFromSupplier($transaction);
                        break;
                }

                return;
            }
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

    /**
     * Không cho phép một giao dịch thanh toán làm công nợ chuyển sang âm.
     * Kiểm tra khi duyệt (trong transaction) để không thể bỏ qua từ frontend.
     */
    private function validateOutstandingDebt(Transaction $transaction): void
    {
        $amount = round((float) $transaction->amount_base, self::DEFAULT_DECIMALS);

        if ($transaction->isReceipt() && $transaction->customer_id) {
            $balance = round(
                $this->customerDebtService->getBalance($transaction->customer_id),
                self::DEFAULT_DECIMALS
            );

            if ($amount > max(0, $balance)) {
                throw new \RuntimeException('Số tiền thu không được vượt quá công nợ khách hàng hiện tại.');
            }

            if ($transaction->sales_order_id) {
                $orderBalance = $this->salesOrderOutstanding($transaction->sales_order_id);
                if ($amount > max(0, round($orderBalance, self::DEFAULT_DECIMALS))) {
                    throw new \RuntimeException('Số tiền thu không được vượt quá công nợ còn lại của đơn bán.');
                }
            }
        }

        if ($transaction->isPayment() && $transaction->supplier_id
            && $transaction->category?->code === 'CHI_NCC') {
            $balance = round(
                $this->supplierDebtService->getOutstandingBalance($transaction->supplier_id),
                self::DEFAULT_DECIMALS
            );

            if ($amount > max(0, $balance)) {
                throw new \RuntimeException('Số tiền chi không được vượt quá công nợ nhà cung cấp hiện tại.');
            }

            if ($transaction->purchase_order_id) {
                $orderBalance = $this->purchaseOrderOutstanding($transaction->purchase_order_id);
                if ($amount > max(0, round($orderBalance, self::DEFAULT_DECIMALS))) {
                    throw new \RuntimeException('Số tiền chi không được vượt quá công nợ còn lại của đơn mua.');
                }
            }
        }
    }

    public function salesOrderOutstanding(int $orderId): float
    {
        $slipIds = WarehouseSlip::where('sales_order_id', $orderId)->where('status', 'approved')->pluck('id');
        $debt = (float) CustomerDebt::where('reference_type', WarehouseSlip::class)->whereIn('reference_id', $slipIds)->sum('amount');
        $transactionIds = Transaction::where('sales_order_id', $orderId)->where('status', 'approved')->pluck('id');
        $payments = (float) CustomerDebt::where('reference_type', Transaction::class)->whereIn('reference_id', $transactionIds)->sum('amount');
        return $debt + $payments;
    }

    public function purchaseOrderOutstanding(int $orderId): float
    {
        $slipIds = WarehouseSlip::where('purchase_order_id', $orderId)->where('status', 'approved')->pluck('id');
        $debt = (float) SupplierDebt::where('reference_type', WarehouseSlip::class)->whereIn('reference_id', $slipIds)->sum('amount');
        $transactionIds = Transaction::where('purchase_order_id', $orderId)->where('status', 'approved')->pluck('id');
        $payments = (float) SupplierDebt::where('reference_type', Transaction::class)->whereIn('reference_id', $transactionIds)->where('type', SupplierDebt::TYPE_PAYMENT)->sum('amount');
        return $debt + $payments;
    }
}
