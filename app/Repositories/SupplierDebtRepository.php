<?php

namespace App\Repositories;

use App\Models\SupplierDebt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SupplierDebtRepository implements SupplierDebtRepositoryInterface
{
    public function __construct(
        protected SupplierDebt $model
    ) {
    }

   public function paginate(array $filters = []): LengthAwarePaginator
{
    return $this->model
        ->with('supplier')
        ->when(!empty($filters['supplier_id']), function ($query) use ($filters) {
            $query->where('supplier_id', $filters['supplier_id']);
        })
        ->when(!empty($filters['type']), function ($query) use ($filters) {
            $query->where('type', $filters['type']);
        })
        ->when(!empty($filters['from_date']), function ($query) use ($filters) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        })
        ->when(!empty($filters['to_date']), function ($query) use ($filters) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        })
        ->latest()
        ->paginate(request('per_page', 15));
}

    public function find(int $id): ?SupplierDebt
    {
        return $this->model
            ->with('supplier')
            ->find($id);
    }

    public function create(array $data): SupplierDebt
    {
        return $this->model->create($data);
    }

    public function update(SupplierDebt $supplierDebt, array $data): SupplierDebt
    {
        $supplierDebt->update($data);

        return $supplierDebt->fresh();
    }

    public function delete(SupplierDebt $supplierDebt): bool
    {
        return $supplierDebt->delete();
    }

    public function getBySupplier(int $supplierId): Collection
    {
        return $this->model
            ->where('supplier_id', $supplierId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getBalance(int $supplierId): float
    {
        return (float) $this->model
            ->where('supplier_id', $supplierId)
            ->sum('amount');
    }
}