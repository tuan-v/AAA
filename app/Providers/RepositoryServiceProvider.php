<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// ===== Module Kế toán =====
use App\Repositories\TransactionRepositoryInterface;
use App\Repositories\TransactionRepository;

use App\Repositories\TransactionCategoryRepositoryInterface;
use App\Repositories\TransactionCategoryRepository;

use App\Repositories\SupplierDebtRepositoryInterface;
use App\Repositories\SupplierDebtRepository;

// Nếu các repository dưới đây đã tồn tại (theo luồng module Kế toán
// đã trao đổi), bỏ comment và trỏ đúng namespace. Nếu chưa tạo,
// tạm comment lại để không lỗi "class not found".
// use App\Repositories\CustomerDebtRepositoryInterface;
// use App\Repositories\CustomerDebtRepository;
// use App\Repositories\AccountRepositoryInterface;
// use App\Repositories\AccountRepository;
// use App\Repositories\CurrencyRepositoryInterface;
// use App\Repositories\CurrencyRepository;
// use App\Repositories\BankRepositoryInterface;
// use App\Repositories\BankRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(TransactionCategoryRepositoryInterface::class, TransactionCategoryRepository::class);
        $this->app->bind(SupplierDebtRepositoryInterface::class, SupplierDebtRepository::class);

        // $this->app->bind(CustomerDebtRepositoryInterface::class, CustomerDebtRepository::class);
        // $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        // $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        // $this->app->bind(BankRepositoryInterface::class, BankRepository::class);
    }
}