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
            if ($transaction->type === 'receipt') {
                return $this->receipt($transaction);
            }
            if ($transaction->type === 'payment') {
                return $this->payment($transaction);
            }
            if ($transaction->type === 'transfer') {
                return $this->transfer($transaction);
            }
        });
    }

    private function receipt($transaction)
    {
        $account = Account::with('currency')->find($transaction->to_account_id);
        $amount = ($account->currency_id === $transaction->currency_id)
            ? $transaction->amount
            : ($transaction->amount_base / ($account->currency->exchange_rate ?: 1));

        AccountLedger::create([
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

    private function payment($transaction)
    {
        $account = Account::with('currency')->find($transaction->from_account_id);
        $amount = ($account->currency_id === $transaction->currency_id)
            ? $transaction->amount
            : ($transaction->amount_base / ($account->currency->exchange_rate ?: 1));

        AccountLedger::create([
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

    private function transfer($transaction)
    {
        $from = Account::with('currency')->find($transaction->from_account_id);
        $to   = Account::with('currency')->find($transaction->to_account_id);

        $fromAmount = ($from->currency_id === $transaction->currency_id)
            ? $transaction->amount
            : ($transaction->amount_base / ($from->currency->exchange_rate ?: 1));

        $toAmount = ($to->currency_id === $transaction->currency_id)
            ? $transaction->amount
            : ($transaction->amount_base / ($to->currency->exchange_rate ?: 1));

        AccountLedger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $from->id,
            'transaction_id' => $transaction->id,
            'ledger_date' => $transaction->transaction_date,
            'debit' => 0,
            'credit' => $fromAmount,
            'balance_after' => $from->current_balance,
            'description' => $transaction->description,
        ]);

        AccountLedger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $to->id,
            'transaction_id' => $transaction->id,
            'ledger_date' => $transaction->transaction_date,
            'debit' => $toAmount,
            'credit' => 0,
            'balance_after' => $to->current_balance,
            'description' => $transaction->description,
        ]);
    }
}
