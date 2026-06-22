<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountLedger;
use Illuminate\Support\Facades\DB;

class AccountBalanceService
{
    public function rebuild($accountId)
    {
        return DB::transaction(function () use ($accountId) {

            $account = Account::findOrFail($accountId);

            $balance = 0;

            $ledgers = AccountLedger::where('account_id', $accountId)
                ->orderBy('ledger_date')
                ->orderBy('id')
                ->get();

            foreach ($ledgers as $ledger) {

                $balance += $ledger->debit;
                $balance -= $ledger->credit;

                // cập nhật lại balance_after
                $ledger->balance_after = $balance;
                $ledger->save();
            }

            $account->current_balance = $balance;
            $account->save();

            return $balance;
        });
    }
}
