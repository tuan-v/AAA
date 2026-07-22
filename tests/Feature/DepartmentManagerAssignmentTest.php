<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DepartmentManagerAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_manager_is_assigned_to_department_and_generated_head_position(): void
    {
        [$owner, $company] = $this->companyOwner();
        $manager = User::factory()->create(['company_id' => $company->id, 'status' => User::STATUS_ACTIVE]);
        $manager->companies()->attach($company->id);

        $response = $this->actingAs($owner)->postJson('/api/departments', [
            'name' => 'Kinh doanh',
            'description' => null,
            'status' => 'active',
            'manager_id' => $manager->id,
        ])->assertCreated();

        $departmentId = $response->json('data.id');
        $position = Position::where('department_id', $departmentId)->firstOrFail();

        $this->assertSame('Trưởng phòng Kinh doanh', $position->name);
        $this->assertSame($departmentId, $manager->fresh()->department_id);
        $this->assertSame($position->id, $manager->fresh()->position_id);
    }

    public function test_company_owner_can_manage_department_without_being_assigned_to_it(): void
    {
        [$owner, $company] = $this->companyOwner();

        $this->actingAs($owner)->getJson('/api/departments/managers')
            ->assertOk()
            ->assertJsonPath('0.id', $owner->id)
            ->assertJsonPath('0.is_company_owner', true);

        $this->actingAs($owner)->postJson('/api/departments', [
            'name' => 'Điều hành',
            'description' => null,
            'status' => 'active',
            'manager_id' => $owner->id,
        ])->assertCreated();

        $this->assertNull($owner->fresh()->department_id);
        $this->assertNull($owner->fresh()->position_id);
    }

    private function companyOwner(): array
    {
        $owner = User::factory()->create(['company_id' => null, 'status' => User::STATUS_ACTIVE]);
        $company = Company::create([
            'name' => 'Công ty kiểm thử phòng ban',
            'address' => 'TP.HCM',
            'phone' => '0900000000',
            'owner_id' => $owner->id,
        ]);
        $owner->update(['company_id' => $company->id]);
        $owner->companies()->attach($company->id);

        $permission = Permission::create([
            'name' => 'nhan_su.xem',
            'guard_name' => 'web',
            'description' => 'Xem nhân sự',
        ]);
        $owner->givePermissionTo($permission);
        $owner->givePermissionTo(Permission::create([
            'name' => 'nhan_su.them',
            'guard_name' => 'web',
            'description' => 'Thêm nhân sự',
        ]));

        return [$owner->fresh(), $company];
    }
}
