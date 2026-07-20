<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_business_module_has_a_real_dashboard_api(): void
    {
        $user = User::factory()->create(['company_id' => null]);
        $company = Company::create([
            'name' => 'Công ty kiểm thử dashboard',
            'address' => 'TP.HCM',
            'phone' => '0900000000',
            'owner_id' => $user->id,
        ]);
        $user->update(['company_id' => $company->id]);

        foreach (['purchase', 'sale', 'warehouse', 'accountant'] as $module) {
            $this->actingAs($user)
                ->getJson("/api/dashboard/{$module}")
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonCount(4, 'data.metrics')
                ->assertJsonStructure([
                    'data' => ['metrics', 'trend', 'recent', 'ranking', 'currency'],
                ]);
        }
    }
}
