<?php

namespace App\Services;

use App\Models\Company;

class CurrencyService
{
    public function getCompanyCurrency(Company $company)
    {
        return $company->currencies()
            ->wherePivot('is_default', true)
            ->first();
    }

    public function convertByCurrency(
        float $amount,
        $currency
    ): float {

        if (!$currency || !$currency->exchange_rate) {
            return $amount;
        }

        return round(
            $amount / $currency->exchange_rate,
            2
        );
    }

    public function symbol(Company $company): string
    {
        return $this->getCompanyCurrency($company)?->symbol ?? "₫";
    }

    public function code(Company $company): string
    {
        return $this->getCompanyCurrency($company)?->code ?? "VND";
    }
}
