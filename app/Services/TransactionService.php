<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            if ($data['type'] === 'receipt' && empty($data['to_account_id'])) {
                throw new \Exception("Receipt must have to_account_id");
            }

            if ($data['type'] === 'payment' && empty($data['from_account_id'])) {
                throw new \Exception("Payment must have from_account_id");
            }

            if ($data['type'] === 'transfer') {
                if (empty($data['from_account_id']) || empty($data['to_account_id'])) {
                    throw new \Exception("Transfer must have both accounts");
                }
            }
            $transaction = Transaction::create([
                'company_id' => $data['company_id'],
                'code' => $this->generateCode(),
                'transaction_date' => $data['transaction_date'] ?? now(),
                'type' => $data['type'],
                'category_id' => $data['category_id'],
                'currency_id' => $data['currency_id'],
                'amount' => $data['amount'],
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'amount_base' => $data['amount'] * ($data['exchange_rate'] ?? 1),

                'from_account_id' => $data['from_account_id'] ?? null,
                'to_account_id' => $data['to_account_id'] ?? null,

                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,

                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'],
            ]);
            $this->updateBalance($transaction);
            app(\App\Services\LedgerService::class)->record($transaction);

            return $transaction;
        });
    }
    private function generateCode()
    {
        return 'TXN-' . now()->format('YmdHis') . rand(100, 999);
    }
    private function updateBalance(Transaction $transaction)
    {
        if ($transaction->type === 'receipt') {
            $account = Account::with('currency')->where('id', $transaction->to_account_id)
                ->lockForUpdate()
                ->first();
            if (!$account) {
                throw new \Exception("Account not found");
            }
            // Quy đổi số tiền sang tiền tệ của tài khoản
            $amount = ($account->currency_id === $transaction->currency_id)
                ? $transaction->amount
                : ($transaction->amount_base / ($account->currency->exchange_rate ?: 1));

            $account->current_balance += $amount;
            $account->save();
        }

        if ($transaction->type === 'payment') {
            $account = Account::with('currency')->where('id', $transaction->from_account_id)
                ->lockForUpdate()
                ->first();
            if (!$account) {
                throw new \Exception("From account not found");
            }

            // Quy đổi số tiền sang tiền tệ của tài khoản
            $amount = ($account->currency_id === $transaction->currency_id)
                ? $transaction->amount
                : ($transaction->amount_base / ($account->currency->exchange_rate ?: 1));

            if ($account->current_balance < $amount) {
                throw new \Exception("Insufficient balance");
            }
            $account->current_balance -= $amount;
            $account->save();
        }

        if ($transaction->type === 'transfer') {
            $from = Account::with('currency')->where('id', $transaction->from_account_id)
                ->lockForUpdate()
                ->first();
            if (!$from) {
                throw new \Exception("From account not found");
            }

            $to = Account::with('currency')->where('id', $transaction->to_account_id)
                ->lockForUpdate()
                ->first();
            if (!$to) {
                throw new \Exception("To account not found");
            }

            // Quy đổi cho tài khoản gửi
            $fromAmount = ($from->currency_id === $transaction->currency_id)
                ? $transaction->amount
                : ($transaction->amount_base / ($from->currency->exchange_rate ?: 1));

            // Quy đổi cho tài khoản nhận
            $toAmount = ($to->currency_id === $transaction->currency_id)
                ? $transaction->amount
                : ($transaction->amount_base / ($to->currency->exchange_rate ?: 1));

            if ($from->current_balance < $fromAmount) {
                throw new \Exception("Insufficient balance");
            }

            $from->current_balance -= $fromAmount;
            $to->current_balance += $toAmount;

            $from->save();
            $to->save();
        }
    }
}
