<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
            'currency'
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

        $transactions = $query->latest()->paginate(20);

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
            'transaction_date' => 'required|date',

            'from_account_id' => 'nullable|exists:accounts,id',
            'to_account_id' => 'nullable|exists:accounts,id',
            'exchange_rate' => 'nullable|numeric',
        ]);

        $transaction = $this->service->create([
            ...$request->all(),
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return response()->json($transaction);
    }
}
