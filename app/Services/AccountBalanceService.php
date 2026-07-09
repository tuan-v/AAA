<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountLedger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountBalanceService
{
    /**
     * Tính lại toàn bộ số dư từ Opening Balance và Ledger.
     */
    public function rebuild(int $accountId): float
    {
        return DB::transaction(function () use ($accountId) {

            $account = Account::query()->findOrFail($accountId);

            $balance = (float) $account->opening_balance;

            $ledgers = AccountLedger::query()
                ->where('account_id', $accountId)
                ->orderBy('ledger_date')
                ->orderBy('id')
                ->get();

            foreach ($ledgers as $ledger) {

                $balance += (float) $ledger->debit;
                $balance -= (float) $ledger->credit;

                $ledger->balance_after = $balance;
                $ledger->save();
            }

            $account->current_balance = $balance;
            $account->save();

            return (float) $account->fresh()->current_balance;
        });
    }

    /**
     * Tăng số dư tài khoản.
     */
    public function increase(Account $account, float $amount): float
    {
        if ($amount <= 0) {
            throw new RuntimeException('Số tiền phải lớn hơn 0.');
        }

        $account->increaseBalance($amount);

        return (float) $account->fresh()->current_balance;
    }

    /**
     * Giảm số dư tài khoản.
     */
    public function decrease(Account $account, float $amount): float
    {
        if ($amount <= 0) {
            throw new RuntimeException('Số tiền phải lớn hơn 0.');
        }

        if ((float) $account->current_balance < $amount) {
            throw new RuntimeException('Số dư tài khoản không đủ.');
        }

        $account->decreaseBalance($amount);

        return (float) $account->fresh()->current_balance;
    }

    /**
     * Lấy số dư hiện tại.
     */
    public function getBalance(Account $account): float
    {
        return (float) $account->fresh()->current_balance;
    }
}