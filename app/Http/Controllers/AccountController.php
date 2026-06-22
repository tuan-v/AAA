<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\AccountBalanceService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query()
            ->with([
                'currency:id,code,name',
                'bank:id,code,name'
            ]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {

                $q->where(
                    'code',
                    'like',
                    '%' . $request->search . '%'
                )
                    ->orWhere(
                        'name',
                        'like',
                        '%' . $request->search . '%'
                    );
            });
        }

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        if ($request->filled('currency_id')) {
            $query->where(
                'currency_id',
                $request->currency_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status
            );
        }

        return $query
            ->latest()
            ->paginate(10)
            ->through(function ($account) {

                $account->is_used =
                    $account->isUsed();

                return $account;
            });
    }

    public function all()
    {
        return Account::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $company = auth()
            ->user()
            ->companies()
            ->first();

        $validated = $request->validate([
            'code' => 'required|unique:accounts,code',
            'name' => 'required',

            'type' => [
                'required',
                'in:cash,bank,ewallet,other'
            ],

            'currency_id' =>
            'required|exists:currencies,id',

            'opening_balance' =>
            'required|numeric',

            'bank_id' =>
            'nullable|exists:banks,id',

            'bank_account_no' =>
            'nullable|string|max:255',
        ]);

        if (
            $validated['type'] === 'bank'
        ) {

            $request->validate([
                'bank_id' =>
                'required|exists:banks,id',

                'bank_account_no' =>
                'required'
            ]);
        }

        $validated['company_id']
            = $company->id;

        $validated['current_balance']
            = $validated['opening_balance'];

        $account = Account::create(
            $validated
        );

        return response()->json([
            'message' =>
            'Thêm tài khoản thành công',

            'data' => $account
        ]);
    }

    public function show(Account $account)
    {
        return $account->load([
            'currency',
            'bank'
        ]);
    }

    public function update(
        Request $request,
        Account $account
    ) {

        if ($account->isUsed()) {

            return response()->json([
                'message' =>
                'Tài khoản đã phát sinh giao dịch, không thể chỉnh sửa.'
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required',

            'type' => [
                'required',
                'in:cash,bank,ewallet,other'
            ],

            'currency_id' =>
            'required|exists:currencies,id',

            'opening_balance' =>
            'required|numeric',

            'bank_id' =>
            'nullable|exists:banks,id',

            'bank_account_no' =>
            'nullable|string|max:255',
        ]);

        if (
            $validated['type'] === 'bank'
        ) {

            $request->validate([
                'bank_id' =>
                'required|exists:banks,id',

                'bank_account_no' =>
                'required'
            ]);
        }

        $account->update([
            ...$validated,

            // đồng bộ lại khi chưa có giao dịch
            'current_balance'
            => $validated['opening_balance']
        ]);

        return response()->json([
            'message' =>
            'Cập nhật thành công'
        ]);
    }

    public function destroy(
        Account $account
    ) {

        if ($account->isUsed()) {

            return response()->json([
                'message' =>
                'Tài khoản đã phát sinh giao dịch, không thể xóa.'
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' =>
            'Xóa thành công'
        ]);
    }

    public function toggleStatus(
        Account $account
    ) {

        $account->update([
            'is_active'
            => !$account->is_active
        ]);

        return response()->json([
            'message' =>
            'Cập nhật trạng thái thành công'
        ]);
    }
    public function rebuildBalance($id, AccountBalanceService $service)
    {
        $balance = $service->rebuild($id);

        return response()->json([
            'message' => 'Rebuild success',
            'balance' => $balance
        ]);
    }
}
