<?php
namespace App\Services;

use App\Models\Company;
use App\Models\CompanyCurrencyRate;
use App\Models\Currency;
use Carbon\CarbonInterface;

class CompanyCurrencyService
{
    public function rate(int $companyId, int $currencyId, CarbonInterface|string|null $date = null): float
    {
        $company = Company::with('currencies')->findOrFail($companyId);
        $currency = $company->currencies->firstWhere('id', $currencyId);
        if (! $currency) throw new \InvalidArgumentException('Tiền tệ không thuộc công ty hiện tại.');

        $defaultId = $company->currencies->first(fn ($item) => (bool) $item->pivot->is_default)?->id;
        if ((int) $defaultId === $currencyId) return 1.0;

        $effectiveDate = $date ? date('Y-m-d', strtotime((string) $date)) : now()->toDateString();
        $rate = CompanyCurrencyRate::where('company_id', $companyId)
            ->where('currency_id', $currencyId)
            ->whereDate('effective_date', '<=', $effectiveDate)
            ->orderByDesc('effective_date')->value('rate_to_base');
        $rate ??= Currency::whereKey($currencyId)->value('exchange_rate');
        if (! $rate || (float) $rate <= 0) throw new \InvalidArgumentException('Chưa cấu hình tỷ giá hợp lệ tại ngày chứng từ.');
        return (float) $rate;
    }

    public function toBase(float $amount, int $companyId, int $currencyId, CarbonInterface|string|null $date = null): float
    { return round($amount * $this->rate($companyId, $currencyId, $date), 2); }
}
