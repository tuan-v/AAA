<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountLedger;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function record($transaction)
    {
        return DB::transaction(function () use ($transaction) {

            $amount = $transaction->amount_base;

            // RECEIPT
            if ($transaction->type === 'receipt') {
                return $this->receipt($transaction, $amount);
            }

            // PAYMENT
            if ($transaction->type === 'payment') {
                return $this->payment($transaction, $amount);
            }

            // TRANSFER
            if ($transaction->type === 'transfer') {
                return $this->transfer($transaction, $amount);
            }
        });
    }
    private function receipt($transaction, $amount)
    {
        $account = Account::lockForUpdate()->find($transaction->to_account_id);

        if (!$account) {
            throw new \Exception("Account not found");
        }

        $account->current_balance += $amount;
        $account->save();

        return AccountLedger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $account->id,
            'transaction_id' => $transaction->id,
            'ledger_date' => $transaction->transaction_date,
            'debit' => $amount,
            'credit' => 0,
            'balance_after' => $account->current_balance,
            'description' => $transaction->description,
        ]);
    }
    private function payment($transaction, $amount)
    {
        $account = Account::lockForUpdate()->find($transaction->from_account_id);

        if (!$account) {
            throw new \Exception("Account not found");
        }

        if ($account->current_balance < $amount) {
            throw new \Exception("Insufficient balance");
        }

        $account->current_balance -= $amount;
        $account->save();

        return AccountLedger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $account->id,
            'transaction_id' => $transaction->id,
            'ledger_date' => $transaction->transaction_date,
            'debit' => 0,
            'credit' => $amount,
            'balance_after' => $account->current_balance,
            'description' => $transaction->description,
        ]);
    }
    private function transfer($transaction, $amount)
    {
        $from = Account::lockForUpdate()->find($transaction->from_account_id);
        $to = Account::lockForUpdate()->find($transaction->to_account_id);

        if (!$from || !$to) {
            throw new \Exception("Account not found");
        }

        if ($from->current_balance < $amount) {
            throw new \Exception("Insufficient balance");
        }

        // trừ tiền
        $from->current_balance -= $amount;
        $from->save();

        AccountLedger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $from->id,
            'transaction_id' => $transaction->id,
            'ledger_date' => $transaction->transaction_date,
            'debit' => 0,
            'credit' => $amount,
            'balance_after' => $from->current_balance,
            'description' => $transaction->description,
        ]);

        // cộng tiền
        $to->current_balance += $amount;
        $to->save();

        return AccountLedger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $to->id,
            'transaction_id' => $transaction->id,
            'ledger_date' => $transaction->transaction_date,
            'debit' => $amount,
            'credit' => 0,
            'balance_after' => $to->current_balance,
            'description' => $transaction->description,
        ]);
    }
}
