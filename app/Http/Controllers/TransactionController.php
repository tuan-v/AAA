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
        ]);

        // 🔥 FILTER theo yêu cầu nghiệp vụ
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->currency_id) {
            $query->where('currency_id', $request->currency_id);
        }

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('transaction_date', [
                $request->date_from,
                $request->date_to
            ]);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->keyword) {
            $query->where('description', 'like', "%{$request->keyword}%");
        }
        $perPage = min((int) $request->input('per_page', 10), 100);
        $transactions = $query->latest()->paginate($perPage);

        return response()->json($transactions);
    }

    // 📌 Tạo giao dịch
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:receipt,payment,transfer',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'required',
            'category_id' => 'required',

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
                ...$request->all(),
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
        $request->validate([
            'type' => 'required|in:receipt,payment,transfer',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'required',
            'category_id' => 'required',

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
            $transaction = $this->service->update($id, $request->all());

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

    public function reject(int $id, TransactionService $service)
    {
        try {
            $transaction = $service->reject($id);

            return response()->json([
                'message' => 'Từ chối giao dịch thành công',
                'data' => $transaction,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
