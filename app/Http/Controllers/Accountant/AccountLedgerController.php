<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\AccountLedger;
use Illuminate\Http\Request;

class AccountLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountLedger::query()
            ->with([
                'account:id,name,code',
                'transaction:id,code,type'
            ])
            ->where('company_id', auth()->user()->company_id);

        if ($request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->search) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json(
            $query
                ->orderByDesc('ledger_date')
                ->paginate(20)
        );
    }
}
