<?php

namespace App\Repositories;

use App\Models\TransactionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TransactionCategoryRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?TransactionCategory;

    public function create(array $data): TransactionCategory;

    public function update(TransactionCategory $category, array $data): TransactionCategory;

    public function delete(TransactionCategory $category): bool;

    public function getActive(int $companyId): Collection;

    public function findByCode(int $companyId, string $code): ?TransactionCategory;
}