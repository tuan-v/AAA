<?php

namespace App\Repositories;

use App\Models\SupplierDebt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SupplierDebtRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?SupplierDebt;

    public function create(array $data): SupplierDebt;

    public function update(SupplierDebt $supplierDebt, array $data): SupplierDebt;

    public function delete(SupplierDebt $supplierDebt): bool;

    public function getBySupplier(int $supplierId): Collection;

    public function getBalance(int $supplierId): float;
}