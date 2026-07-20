<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserListVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_company_owner_is_visible_in_employee_management(): void
    {
        $user = User::factory()->create(['company_id' => null, 'status' => User::STATUS_ACTIVE]);
        $company = Company::create([
            'name' => 'Công ty kiểm thử nhân sự',
            'address' => 'TP.HCM',
            'phone' => '0900000000',
            'owner_id' => $user->id,
        ]);
        $user->update(['company_id' => $company->id]);
        $user->companies()->attach($company->id);

        $permission = Permission::create([
            'name' => 'nhan_su.xem',
            'guard_name' => 'web',
            'description' => 'Xem nhân sự',
        ]);
        $user->givePermissionTo($permission);

        $this->actingAs($user)
            ->getJson('/api/users/user')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.email', $user->email);
    }
}
