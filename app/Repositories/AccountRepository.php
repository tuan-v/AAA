<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(
        protected Account $model
    ) {}

    /**
     * Danh sách tài khoản
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with([
                'company:id,name',
                'currency:id,name,code,symbol',
                'bank:id,name',
            ])

            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $query->where(function ($q) use ($filters) {
                        $q->where('code', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('name', 'like', '%' . $filters['search'] . '%');
                    });
                }
            )

            ->when(
                !empty($filters['company_id']),
                fn($query) => $query->where('company_id', $filters['company_id'])
            )

            ->when(
                !empty($filters['currency_id']),
                fn($query) => $query->where('currency_id', $filters['currency_id'])
            )

            ->when(
                !empty($filters['type']),
                fn($query) => $query->where('type', $filters['type'])
            )

            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', $filters['is_active'])
            )

            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Chi tiết tài khoản
     */
    public function find(int $id): ?Account
    {
        return $this->model
            ->with([
                'company',
                'currency',
                'bank',
                'ledgers' => fn($query) => $query->latest('ledger_date'),
            ])
            ->find($id);
    }

    /**
     * Tạo mới
     */
    public function create(array $data): Account
    {
        return $this->model->create($data);
    }

    /**
     * Cập nhật
     */
    public function update(Account $account, array $data): Account
    {
        $account->update($data);

        return $account->fresh();
    }

    /**
     * Xóa
     */
    public function delete(Account $account): bool
    {
        return (bool) $account->delete();
    }

    /**
     * Tìm theo mã trong công ty
     */
    public function findByCode(int $companyId, string $code): ?Account
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->first();
    }

    /**
     * Danh sách tài khoản đang hoạt động
     */
    public function getActive(int $companyId): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();
    }
}