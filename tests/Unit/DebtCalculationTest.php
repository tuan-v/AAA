<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DebtCalculationTest extends TestCase
{
    public function test_remaining_debt_formula_is_opening_plus_receivable_minus_paid(): void
    {
        $openingDebt = 100.0;
        $totalReceivable = 200.0;
        $totalPaid = 50.0;

        $remainingDebt = $openingDebt + $totalReceivable - $totalPaid;

        $this->assertSame(250.0, $remainingDebt);
    }
}
