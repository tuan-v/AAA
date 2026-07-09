<?php

namespace App\Repositories;

use App\Models\TransactionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TransactionCategoryRepository implements TransactionCategoryRepositoryInterface
{
    public function __construct(
        protected TransactionCategory $model
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->when(isset($filters['company_id']), function ($query) use ($filters) {
                $query->where('company_id', $filters['company_id']);
            })
            ->when(isset($filters['type']), function ($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(isset($filters['is_active']), function ($query) use ($filters) {
                $query->where('is_active', $filters['is_active']);
            })
            ->when(isset($filters['keyword']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('code', 'like', "%{$filters['keyword']}%")
                      ->orWhere('name', 'like', "%{$filters['keyword']}%");
                });
            })
            ->latest()
            ->paginate(request('per_page', 15));
    }

    public function find(int $id): ?TransactionCategory
    {
        return $this->model->find($id);
    }

    public function create(array $data): TransactionCategory
    {
        return $this->model->create($data);
    }

    public function update(TransactionCategory $category, array $data): TransactionCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(TransactionCategory $category): bool
    {
        return $category->delete();
    }

    public function getActive(int $companyId): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    public function findByCode(int $companyId, string $code): ?TransactionCategory
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->first();
    }
}