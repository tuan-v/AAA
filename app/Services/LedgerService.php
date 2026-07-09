<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountLedger;
use App\Models\Transaction;
use InvalidArgumentException;

class LedgerService
{
    /**
     * Ghi sổ kế toán cho giao dịch.
     */
    public function record(Transaction $transaction): void
    {
        switch ($transaction->type) {
            case 'receipt':
                $this->receipt($transaction);
                break;

            case 'payment':
                $this->payment($transaction);
                break;

            case 'transfer':
                $this->transfer($transaction);
                break;

            default:
                throw new InvalidArgumentException(
                    "Unsupported transaction type: {$transaction->type}"
                );
        }
    }

    /**
     * Ghi sổ thu tiền.
     */
    private function receipt(Transaction $transaction): void
    {
        $account = Account::with('currency')
            ->findOrFail($transaction->to_account_id);

        $amount = $this->convertAmount($account, $transaction);

        AccountLedger::create([
            'company_id'     => $transaction->company_id,
            'account_id'     => $account->id,
            'transaction_id' => $transaction->id,
            'ledger_date'    => $transaction->transaction_date,
            'debit'          => $amount,
            'credit'         => 0,
            'balance_after'  => $account->current_balance,
            'description'    => $transaction->description,
        ]);
    }

    /**
     * Ghi sổ chi tiền.
     */
    private function payment(Transaction $transaction): void
    {
        $account = Account::with('currency')
            ->findOrFail($transaction->from_account_id);

        $amount = $this->convertAmount($account, $transaction);

        AccountLedger::create([
            'company_id'     => $transaction->company_id,
            'account_id'     => $account->id,
            'transaction_id' => $transaction->id,
            'ledger_date'    => $transaction->transaction_date,
            'debit'          => 0,
            'credit'         => $amount,
            'balance_after'  => $account->current_balance,
            'description'    => $transaction->description,
        ]);
    }

    /**
     * Ghi sổ chuyển khoản.
     */
    private function transfer(Transaction $transaction): void
    {
        $from = Account::with('currency')
            ->findOrFail($transaction->from_account_id);

        $to = Account::with('currency')
            ->findOrFail($transaction->to_account_id);

        $fromAmount = $this->convertAmount($from, $transaction);
        $toAmount   = $this->convertAmount($to, $transaction);

        AccountLedger::create([
            'company_id'     => $transaction->company_id,
            'account_id'     => $from->id,
            'transaction_id' => $transaction->id,
            'ledger_date'    => $transaction->transaction_date,
            'debit'          => 0,
            'credit'         => $fromAmount,
            'balance_after'  => $from->current_balance,
            'description'    => 'Chuyển tiền: ' . $transaction->description,
        ]);

        AccountLedger::create([
            'company_id'     => $transaction->company_id,
            'account_id'     => $to->id,
            'transaction_id' => $transaction->id,
            'ledger_date'    => $transaction->transaction_date,
            'debit'          => $toAmount,
            'credit'         => 0,
            'balance_after'  => $to->current_balance,
            'description'    => 'Nhận chuyển khoản: ' . $transaction->description,
        ]);
    }

    /**
     * Quy đổi số tiền giao dịch sang tiền tệ của tài khoản.
     */
    private function convertAmount(
        Account $account,
        Transaction $transaction
    ): float {
        if ($account->currency_id === $transaction->currency_id) {
            return (float) $transaction->amount;
        }

        return (float) (
            $transaction->amount_base /
            ($account->currency->exchange_rate ?: 1)
        );
    }
}