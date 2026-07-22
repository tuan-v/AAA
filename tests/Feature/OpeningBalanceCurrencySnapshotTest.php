<?php

namespace Tests\Feature;

use App\Models\CompanyCurrencyRate;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Province;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningBalanceCurrencySnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_foreign_currency_opening_balances_are_snapshotted_in_company_currency(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $usd = Currency::where('code', 'USD')->firstOrFail();
        $owner->company->currencies()->syncWithoutDetaching([$usd->id => ['is_default' => false]]);
        CompanyCurrencyRate::create([
            'company_id' => $owner->company_id,
            'currency_id' => $usd->id,
            'rate_to_base' => 26100,
            'effective_date' => now()->toDateString(),
            'created_by' => $owner->id,
        ]);
        $province = Province::firstOrFail();
        $ward = Ward::where('province_id', $province->id)->firstOrFail();

        $this->actingAs($owner)->postJson('/api/sale/customers', [
            'name' => 'Khách USD',
            'phone' => '0901111111',
            'email' => 'usd.customer@example.com',
            'currency_id' => $usd->id,
            'province_id' => $province->id,
            'ward_id' => $ward->id,
            'address_detail' => 'Địa chỉ khách USD',
            'opening_debt' => 100,
        ])->assertSuccessful();

        $customer = Customer::where('email', 'usd.customer@example.com')->firstOrFail();
        $this->assertEquals(100, $customer->opening_debt);
        $this->assertEquals(26100, $customer->opening_debt_exchange_rate);
        $this->assertEquals(2610000, $customer->opening_debt_base);

        $this->actingAs($owner)->postJson('/api/purchase/suppliers', [
            'name' => 'NCC USD',
            'phone' => '0902222222',
            'email' => 'usd.supplier@example.com',
            'currency_id' => $usd->id,
            'province_code' => (string) $province->id,
            'province_name' => $province->name,
            'ward_code' => (string) $ward->id,
            'ward_name' => $ward->name,
            'address_detail' => 'Địa chỉ NCC USD',
            'opening_debt' => 200,
            'opening_advance' => 50,
        ])->assertSuccessful();

        $supplier = Supplier::where('email', 'usd.supplier@example.com')->firstOrFail();
        $this->assertEquals(5220000, $supplier->opening_debt_base);
        $this->assertEquals(1305000, $supplier->opening_advance_base);
    }
}
