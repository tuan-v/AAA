<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
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
        $this->seed(PermissionSeeder::class);
        $user->givePermissionTo([
            'don_mua.xem',
            'don_ban.xem',
            'kho.xem',
            'giao_dich.xem',
        ]);

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

    public function test_module_user_cannot_access_overall_or_another_module_dashboard(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $warehouseUser = User::where('email', 'warehouse@demo.vn')->firstOrFail();

        $this->actingAs($warehouseUser)
            ->getJson('/api/dashboard/overview')
            ->assertForbidden();

        $this->actingAs($warehouseUser)
            ->getJson('/api/dashboard/accountant')
            ->assertForbidden();

        $this->actingAs($warehouseUser)
            ->getJson('/api/dashboard/warehouse')
            ->assertOk();

        $this->actingAs($warehouseUser)
            ->get('/dashboard')
            ->assertRedirect('/warehouse');
    }
}
