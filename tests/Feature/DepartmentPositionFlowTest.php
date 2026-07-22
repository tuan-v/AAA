<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DepartmentDemoSeeder;
use Database\Seeders\DepartmentEmployeeDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DepartmentPositionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_crud_is_scoped_to_current_company_and_department(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $department = Department::where('company_id', $owner->company_id)->firstOrFail();

        $created = $this->actingAs($owner)->postJson('/api/positions', [
            'department_id' => $department->id,
            'name' => 'Trưởng nhóm',
            'description' => 'Điều phối công việc của nhóm',
            'status' => 'active',
        ])->assertCreated()->assertJsonPath('data.name', 'Trưởng nhóm');

        $positionId = $created->json('data.id');
        $this->actingAs($owner)->getJson('/api/positions?department_id='.$department->id)
            ->assertOk()->assertJsonPath('data.0.id', $positionId);

        $this->actingAs($owner)->putJson('/api/positions/'.$positionId, [
            'department_id' => $department->id,
            'name' => 'Trưởng nhóm kinh doanh',
            'description' => null,
            'status' => 'inactive',
        ])->assertOk()->assertJsonPath('data.status', 'inactive');

        $this->actingAs($owner)->deleteJson('/api/positions/'.$positionId)->assertOk();
        $this->assertDatabaseMissing('positions', ['id' => $positionId]);
    }

    public function test_used_position_and_department_cannot_be_deleted(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $department = Department::where('company_id', $owner->company_id)->firstOrFail();
        $position = Position::create([
            'company_id' => $owner->company_id, 'department_id' => $department->id,
            'code' => 'CV-TEST', 'name' => 'Nhân viên', 'status' => 'active',
        ]);
        $owner->update(['department_id' => $department->id, 'position_id' => $position->id]);

        $this->actingAs($owner)->deleteJson('/api/positions/'.$position->id)->assertUnprocessable();
        $this->actingAs($owner)->deleteJson('/api/departments/'.$department->id)->assertUnprocessable();
    }

    public function test_position_name_cannot_be_duplicated_across_departments_in_the_same_company(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $firstDepartment = Department::where('company_id', $owner->company_id)->firstOrFail();
        $secondDepartment = Department::create([
            'company_id' => $owner->company_id,
            'code' => 'PB-UNIQUE-NAME',
            'name' => 'Second department',
            'status' => 'active',
        ]);

        Position::create([
            'company_id' => $owner->company_id,
            'department_id' => $firstDepartment->id,
            'code' => 'CV-UNIQUE-NAME',
            'name' => 'Company scoped position',
            'status' => 'active',
        ]);

        $this->actingAs($owner)->postJson('/api/positions', [
            'department_id' => $secondDepartment->id,
            'name' => 'Company scoped position',
            'description' => null,
            'status' => 'active',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('name')
            ->assertJsonPath('errors.name.0', 'Tên chức vụ đã tồn tại trong công ty.');
    }

    public function test_department_employee_can_only_access_assigned_module(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DepartmentDemoSeeder::class);
        $this->seed(DepartmentEmployeeDemoSeeder::class);

        $purchaseEmployee = User::where('email', 'purchase.staff@demo.vn')->firstOrFail();

        $this->actingAs($purchaseEmployee)
            ->getJson('/api/purchase/orders')
            ->assertOk();

        $this->actingAs($purchaseEmployee)
            ->getJson('/api/users/user')
            ->assertForbidden();

        $this->actingAs($purchaseEmployee)
            ->postJson('/api/purchase/orders/1/approve')
            ->assertForbidden();
    }

    public function test_user_can_be_created_and_updated_with_department_and_position(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DepartmentDemoSeeder::class);
        $this->seed(DepartmentEmployeeDemoSeeder::class);

        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $department = Department::where('company_id', $owner->company_id)->where('code', 'PB-005')->firstOrFail();
        $position = Position::where('company_id', $owner->company_id)->where('code', 'CV-105')->firstOrFail();

        $created = $this->actingAs($owner)->postJson('/api/users/user', [
            'name' => 'Nhân viên Form Test', 'username' => 'form_test', 'email' => 'form.test@demo.vn',
            'phone' => '0902999999', 'password' => 'Test@123456', 'password_confirmation' => 'Test@123456',
            'status' => 'active', 'role' => 'Nhân viên bán hàng',
            'department_id' => $department->id, 'position_id' => $position->id,
        ])->assertCreated();

        $user = User::findOrFail($created->json('user.id'));
        $this->assertSame($position->id, $user->position_id);

        $this->actingAs($owner)->putJson('/api/users/user/'.$user->id, [
            'name' => 'Nhân viên Form Test Updated', 'username' => 'form_test', 'email' => 'form.test@demo.vn',
            'phone' => '0902999999', 'password' => 'NewTest@123', 'password_confirmation' => 'NewTest@123',
            'status' => 'active', 'role' => 'Nhân viên bán hàng',
            'department_id' => $department->id, 'position_id' => $position->id,
        ])->assertOk();

        $this->assertTrue(Hash::check('NewTest@123', $user->fresh()->password));
    }
}
