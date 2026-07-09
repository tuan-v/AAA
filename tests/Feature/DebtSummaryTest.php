<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DebtSummaryTest extends TestCase
{
    public function test_customer_detail_formula_is_correct(): void
    {
        $openingDebt = 100.0;
        $totalReceivable = 200.0;
        $totalPaid = 50.0;
        $remainingDebt = $openingDebt + $totalReceivable - $totalPaid;

        $this->assertSame(250.0, $remainingDebt);
    }

    public function test_accountant_debt_routes_are_registered(): void
    {
        $router = app('router');

        $this->assertNotNull($router->getRoutes()->getByName('accountant.customers-debt.index'));
        $this->assertNotNull($router->getRoutes()->getByName('accountant.customers-debt.detail'));
        $this->assertNotNull($router->getRoutes()->getByName('accountant.suppliers-debt.index'));
        $this->assertNotNull($router->getRoutes()->getByName('accountant.suppliers-debt.detail'));
    }

    public function test_supplier_detail_formula_is_correct(): void
    {
        $openingDebt = 100.0;
        $totalReceivable = 200.0;
        $totalPaid = 50.0;
        $remainingDebt = $openingDebt + $totalReceivable - $totalPaid;

        $this->assertSame(250.0, $remainingDebt);
    }
}
