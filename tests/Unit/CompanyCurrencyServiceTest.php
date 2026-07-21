<?php

namespace Tests\Unit;

use App\Models\CompanyCurrencyRate;
use App\Models\User;
use App\Services\CompanyCurrencyService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyCurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_is_company_specific_effective_dated_and_base_currency_is_one(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'admin@demo.vn')->firstOrFail();
        $company = $user->company;
        $currencies = $company->currencies;
        $base = $currencies->first(fn ($item) => (bool) $item->pivot->is_default);
        $foreign = $currencies->firstWhere('id', '!=', $base->id);
        $this->assertNotNull($foreign);

        CompanyCurrencyRate::create(['company_id' => $company->id, 'currency_id' => $foreign->id, 'rate_to_base' => 24000, 'effective_date' => '2026-01-01']);
        CompanyCurrencyRate::create(['company_id' => $company->id, 'currency_id' => $foreign->id, 'rate_to_base' => 25000, 'effective_date' => '2026-07-01']);
        $service = app(CompanyCurrencyService::class);

        $this->assertSame(1.0, $service->rate($company->id, $base->id, '2026-07-20'));
        $this->assertSame(24000.0, $service->rate($company->id, $foreign->id, '2026-06-30'));
        $this->assertSame(25000.0, $service->rate($company->id, $foreign->id, '2026-07-20'));
        $this->assertSame(250000.0, $service->toBase(10, $company->id, $foreign->id, '2026-07-20'));
    }
}
