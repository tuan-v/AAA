<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    // 📌 Danh sách giao dịch (QUẢN LÝ GIAO DỊCH)
    public function index(Request $request)
    {
        $query = Transaction::with([
            'fromAccount',
            'toAccount',
            'category',
            'currency',
            'salesOrder',
            'purchaseOrder'
            ,'customer', 'supplier', 'createdBy', 'approvedBy', 'rejectedBy'
        ]);

        // 🔥 FILTER theo yêu cầu nghiệp vụ
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->currency_id) {
            $query->where('currency_id', $request->currency_id);
        }

        $dateFrom = $request->input('date_from', $request->input('from_date'));
        $dateTo = $request->input('date_to', $request->input('to_date'));
        if ($dateFrom && $dateTo) {
            $query->whereBetween('transaction_date', [
                $dateFrom,
                $dateTo
            ]);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $keyword = $request->input('search', $request->input('keyword'));
        if ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('description', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }
        $perPage = min((int) $request->input('per_page', 10), 100);
        $transactions = $query->latest()->paginate($perPage);

        return response()->json($transactions);
    }

    // 📌 Tạo giao dịch
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment,transfer',
            'amount' => 'required|numeric|gt:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'category_id' => 'required',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:2000',

            'from_account_id' => 'nullable|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'exchange_rate' => 'nullable|numeric',
        ], [
            'type.required' => 'Loại giao dịch không được để trống',
            'type.in' => 'Loại giao dịch không hợp lệ',
            'amount.required' => 'Số tiền không được để trống',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền phải lớn hơn hoặc bằng 0',
            'amount.gt' => 'Số tiền giao dịch phải lớn hơn 0',
            'transaction_date.required' => 'Ngày giao dịch không được để trống',
            'transaction_date.date' => 'Ngày giao dịch không hợp lệ',
            'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
            'category_id.required' => 'Danh mục không được để trống',
            'from_account_id.exists' => 'Tài khoản không tồn tại',
            'to_account_id.exists' => 'Tài khoản không tồn tại',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'sales_order_id.exists' => 'Đơn hàng không tồn tại',
            'purchase_order_id.exists' => 'Đơn hàng không tồn tại',
            'exchange_rate.numeric' => 'Tỷ giá phải là số',
        ]);

        // MỚI: bọc try/catch để bắt các lỗi nghiệp vụ ném từ Service
        // (vd category không khớp loại giao dịch, số dư không đủ, v.v.)
        // Nếu không bọc, các \InvalidArgumentException / \RuntimeException từ
        // Service sẽ rơi thành lỗi 500 chung chung thay vì message rõ ràng.
        try {
            $transaction = $this->service->create([
                ...$validated,
                'company_id' => auth()->user()->company_id,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Tạo giao dịch thành công',
                'data' => $transaction,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // 📌 Cập nhật giao dịch (MỚI)
    // Chỉ cho sửa khi giao dịch đang ở trạng thái "pending" — giữ đúng
    // nguyên tắc: đã duyệt thì khoá, không cho sửa lại (giống PO/SO).
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment,transfer',
            'amount' => 'required|numeric|gt:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'category_id' => 'required',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:2000',

            'from_account_id' => 'nullable|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'exchange_rate' => 'nullable|numeric',
        ], [
            'type.required' => 'Loại giao dịch không được để trống',
            'type.in' => 'Loại giao dịch không hợp lệ',
            'amount.required' => 'Số tiền không được để trống',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền phải lớn hơn hoặc bằng 0',
            'amount.gt' => 'Số tiền giao dịch phải lớn hơn 0',
            'transaction_date.required' => 'Ngày giao dịch không được để trống',
            'transaction_date.date' => 'Ngày giao dịch không hợp lệ',
            'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
            'category_id.required' => 'Danh mục không được để trống',
            'from_account_id.exists' => 'Tài khoản không tồn tại',
            'to_account_id.exists' => 'Tài khoản không tồn tại',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'sales_order_id.exists' => 'Đơn hàng không tồn tại',
            'purchase_order_id.exists' => 'Đơn hàng không tồn tại',
            'exchange_rate.numeric' => 'Tỷ giá phải là số',
        ]);

        try {
            $transaction = $this->service->update($id, $validated);

            return response()->json([
                'message' => 'Cập nhật giao dịch thành công',
                'data' => $transaction,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function ledger(Account $account)
    {
        return $account->ledgers()
            ->with('transaction')
            ->orderBy('ledger_date')
            ->paginate(50);
    }

    public function show(Transaction $transaction)
    {
        return $transaction->load([
            'fromAccount',
            'toAccount',
            'currency',
            'category',
            'salesOrder',
            'purchaseOrder',
            'createdBy',      // ← Thêm
            'approvedBy',
            'rejectedBy',
            'customer',
            'supplier',
        ]);
    }

    public function approve(int $id, TransactionService $service)
    {
        try {
            $transaction = $service->approve($id);

            return response()->json([
                'message' => 'Duyệt giao dịch thành công',
                'data' => $transaction,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, int $id, TransactionService $service)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Vui lòng nhập lý do từ chối giao dịch.',
            'rejection_reason.max' => 'Lý do từ chối không được vượt quá 500 ký tự.',
        ]);
        try {
            $transaction = $service->reject($id, $validated['rejection_reason']);

            return response()->json([
                'message' => 'Từ chối giao dịch thành công',
                'data' => $transaction,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function orderOutstanding(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sale,purchase',
            'order_id' => 'required|integer',
        ]);

        if ($validated['type'] === 'sale') {
            $order = \App\Models\SalesOrder::whereKey($validated['order_id'])->firstOrFail();
            $baseAmount = $this->service->salesOrderOutstanding($order->id);
        } else {
            $order = \App\Models\PurchaseOrder::whereKey($validated['order_id'])->firstOrFail();
            $baseAmount = $this->service->purchaseOrderOutstanding($order->id);
        }

        $rate = (float) ($order->exchange_rate ?: 1);
        return response()->json([
            'remaining_base' => round($baseAmount, 2),
            'remaining_amount' => round($baseAmount / $rate, 2),
            'currency_id' => $order->currency_id,
        ]);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);
            return response()->json(['message' => 'Xóa giao dịch chờ duyệt thành công']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
