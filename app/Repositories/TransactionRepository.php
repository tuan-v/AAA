<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(
        protected Transaction $model
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->with([
                'fromAccount',
                'toAccount',
                'currency',
                'category',
                'customer',
                'supplier',
            ])
            ->when(!empty($filters['type']), function ($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(!empty($filters['category_id']), function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(!empty($filters['account_id']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('from_account_id', $filters['account_id'])
                      ->orWhere('to_account_id', $filters['account_id']);
                });
            })
            ->when(!empty($filters['from_date']), function ($query) use ($filters) {
                $query->whereDate('transaction_date', '>=', $filters['from_date']);
            })
            ->when(!empty($filters['to_date']), function ($query) use ($filters) {
                $query->whereDate('transaction_date', '<=', $filters['to_date']);
            })
            ->when(!empty($filters['currency_id']), function ($query) use ($filters) {
    $query->where('currency_id', $filters['currency_id']);
})
            ->latest('transaction_date')
            ->paginate(request('per_page', 15));
    }

    public function find(int $id): ?Transaction
    {
        return $this->model
            ->with([
                'fromAccount',
                'toAccount',
                'currency',
                'category',
                'customer',
                'supplier',
                'salesOrder',
                'purchaseOrder',
            ])
            ->find($id);
    }

    public function create(array $data): Transaction
    {
        return $this->model->create($data);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->fresh();
    }

    public function delete(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    public function getAccounts(): Collection
    {
        return Account::query()
            ->active()
            ->orderBy('code')
            ->get();
    }

    public function getCategories(): Collection
    {
        return TransactionCategory::query()
            ->active()
            ->orderBy('name')
            ->get();
    }
}