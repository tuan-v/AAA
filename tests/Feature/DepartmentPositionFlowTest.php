<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Position;
use App\Models\User;
use App\Events\NotificationCreated;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DepartmentDemoSeeder;
use Database\Seeders\DepartmentEmployeeDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DepartmentPositionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_account_can_load_role_options_without_role_management_permission(): void
    {
        $this->seed(DatabaseSeeder::class);
        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();

        $this->actingAs($hr)
            ->getJson('/api/users/roles')
            ->assertOk()
            ->assertJsonStructure(['data' => ['system', 'user']]);
    }

    public function test_department_and_position_audit_logs_use_the_correct_models_and_descriptions(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->seed(DatabaseSeeder::class);
        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();
        $hr->givePermissionTo(['phong_ban.them', 'chuc_vu.them']);

        $department = $this->actingAs($hr)->postJson('/api/departments', [
            'name' => 'Phòng audit',
            'description' => null,
            'status' => 'active',
            'manager_id' => null,
        ])->assertCreated()->json('data');

        $position = $this->actingAs($hr)->postJson('/api/positions', [
            'department_id' => $department['id'],
            'name' => 'Chức vụ audit',
            'description' => null,
            'status' => 'active',
        ])->assertCreated()->json('data');

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => Department::class,
            'model_id' => $department['id'],
            'description' => "Thêm mới phòng ban #{$department['id']}",
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'model_type' => Position::class,
            'model_id' => $position['id'],
            'description' => "Thêm mới chức vụ #{$position['id']}",
        ]);
        $this->assertFalse(ActivityLog::where('model_type', User::class)
            ->whereIn('model_id', [$department['id'], $position['id']])
            ->where('user_id', $hr->id)
            ->exists());
    }

    public function test_creating_department_and_position_notifies_higher_role_users(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->seed(DatabaseSeeder::class);

        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();
        $hr->givePermissionTo(['phong_ban.them', 'chuc_vu.them']);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();

        $departmentResponse = $this->actingAs($hr)->postJson('/api/departments', [
            'name' => 'Phòng kiểm thử thông báo',
            'description' => null,
            'status' => 'active',
            'manager_id' => null,
        ])->assertCreated();

        $departmentId = $departmentResponse->json('data.id');
        $this->actingAs($hr)->postJson('/api/positions', [
            'department_id' => $departmentId,
            'name' => 'Chức vụ kiểm thử thông báo',
            'description' => null,
            'status' => 'active',
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'company_id' => $hr->company_id,
            'title' => 'Phòng ban mới được tạo',
            'category' => 'management',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'company_id' => $hr->company_id,
            'title' => 'Chức vụ mới được tạo',
            'category' => 'management',
        ]);
        $this->assertFalse(Notification::where('user_id', $hr->id)->exists());
    }

    public function test_empty_department_deletes_its_unused_positions_and_notifies_higher_roles(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->seed(DatabaseSeeder::class);

        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $department = Department::create([
            'company_id' => $hr->company_id,
            'code' => 'PB-DELETE',
            'name' => 'Phòng ban không có nhân sự',
            'status' => 'active',
        ]);
        $position = Position::create([
            'company_id' => $hr->company_id,
            'department_id' => $department->id,
            'code' => 'CV-DELETE',
            'name' => 'Chức vụ chưa sử dụng',
            'status' => 'active',
        ]);

        $this->actingAs($hr)
            ->deleteJson("/api/departments/{$department->id}")
            ->assertOk();

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'title' => 'Phòng ban đã bị xóa',
            'category' => 'management',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $hr->id,
            'action' => 'delete',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'description' => "Xóa phòng ban #{$department->id}",
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $hr->id,
            'action' => 'delete',
            'model_type' => Position::class,
            'model_id' => $position->id,
            'description' => "Xóa chức vụ #{$position->id}",
        ]);
    }

    public function test_hr_account_without_specific_create_permissions_cannot_create_departments_or_positions(): void
    {
        $this->seed(DatabaseSeeder::class);
        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();
        $department = Department::where('company_id', $hr->company_id)->firstOrFail();

        $this->actingAs($hr)->postJson('/api/departments', [
            'name' => 'Phòng không được phép tạo',
            'status' => 'active',
        ])->assertForbidden();

        $this->actingAs($hr)->postJson('/api/positions', [
            'department_id' => $department->id,
            'name' => 'Chức vụ không được phép tạo',
            'status' => 'active',
        ])->assertForbidden();
    }

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
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $owner->id,
            'action' => 'delete',
            'model_type' => Position::class,
            'model_id' => $positionId,
            'description' => "Xóa chức vụ #{$positionId}",
        ]);
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

    public function test_position_code_sequence_recovers_when_it_is_behind_existing_codes(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::where('email', 'admin@demo.vn')->firstOrFail();
        $department = Department::where('company_id', $owner->company_id)->firstOrFail();

        Position::create([
            'company_id' => $owner->company_id,
            'department_id' => $department->id,
            'code' => 'CV-005',
            'name' => 'Chức vụ đã có mã 005',
            'status' => 'active',
        ]);

        DB::table('company_code_sequences')->updateOrInsert(
            [
                'company_id' => $owner->company_id,
                'model_type' => Position::class,
                'prefix' => 'CV-',
            ],
            [
                'current_number' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $maxBefore = Position::where('company_id', $owner->company_id)
            ->pluck('code')
            ->reduce(function (int $max, string $code) {
                return preg_match('/^CV-(\d+)$/', $code, $matches)
                    ? max($max, (int) $matches[1])
                    : $max;
            }, 0);

        $response = $this->actingAs($owner)->postJson('/api/positions', [
            'department_id' => $department->id,
            'name' => 'Chức vụ không trùng mã',
            'description' => null,
            'status' => 'active',
        ])->assertCreated();

        $this->assertSame(
            'CV-'.str_pad((string) ($maxBefore + 1), 3, '0', STR_PAD_LEFT),
            $response->json('data.code'),
        );
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
        ])->assertCreated()
            ->assertJsonPath('user.status', User::STATUS_ACTIVE)
            ->assertJsonPath('requires_approval', false);

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

    public function test_employee_created_by_hr_stays_pending_until_a_higher_account_approves_it(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->seed(DatabaseSeeder::class);
        $this->seed(DepartmentDemoSeeder::class);
        $this->seed(DepartmentEmployeeDemoSeeder::class);

        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();
        $director = User::where('email', 'admin@demo.vn')->firstOrFail();
        $resubmittingManager = User::where('email', 'purchase@demo.vn')->firstOrFail();
        $department = Department::where('company_id', $hr->company_id)->where('code', 'PB-005')->firstOrFail();
        $position = Position::where('company_id', $hr->company_id)->where('code', 'CV-105')->firstOrFail();

        $created = $this->actingAs($hr)->postJson('/api/users/user', [
            'name' => 'Nhân viên chờ duyệt',
            'username' => 'pending_employee',
            'email' => 'pending.employee@demo.vn',
            'phone' => '0902888888',
            'password' => 'Test@123456',
            'password_confirmation' => 'Test@123456',
            'status' => User::STATUS_ACTIVE,
            'role' => 'Nhân viên bán hàng',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ])->assertCreated()->assertJsonPath('user.status', User::STATUS_PENDING);

        $userId = $created->json('user.id');
        $this->assertDatabaseHas('notifications', [
            'user_id' => $director->id,
            'title' => 'Nhân sự mới chờ duyệt',
        ]);

        $this->actingAs($director)->putJson("/api/users/user/{$userId}", [
            'name' => 'Nhân viên chờ duyệt đã sửa',
            'username' => 'pending_employee',
            'email' => 'pending.employee@demo.vn',
            'phone' => '0902888888',
            'password' => null,
            'password_confirmation' => null,
            'status' => User::STATUS_ACTIVE,
            'role' => 'Nhân viên bán hàng',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ])->assertOk();
        $this->assertSame(User::STATUS_PENDING, User::findOrFail($userId)->status);

        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_ACTIVE);

        $this->assertSame(User::STATUS_ACTIVE, User::findOrFail($userId)->status);

        User::whereKey($userId)->update(['status' => User::STATUS_PENDING]);
        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/reject")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reason');

        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/reject", [
                'reason' => 'Thiếu số điện thoại và thông tin chức vụ.',
                'rejection_type' => 'reject_and_return',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_PENDING_EDIT);

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'status' => User::STATUS_PENDING_EDIT,
            'rejection_reason' => 'Thiếu số điện thoại và thông tin chức vụ.',
            'rejected_by' => $director->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $hr->id,
            'title' => 'Yêu cầu thêm nhân sự bị từ chối',
        ]);

        $this->actingAs($director)
            ->getJson("/api/users/user/{$userId}")
            ->assertOk()
            ->assertJsonPath('can_edit_pending_edit', false)
            ->assertJsonPath('can_resubmit', false);
        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/resubmit")
            ->assertForbidden();

        $this->actingAs($hr)
            ->putJson("/api/users/user/{$userId}", [
                'name' => 'Pending Employee Updated',
                'username' => 'pending_employee',
                'email' => 'pending.employee@demo.vn',
                'phone' => '0902999999',
                'password' => null,
                'password_confirmation' => null,
                'status' => User::STATUS_ACTIVE,
                'role' => 'Nhân viên bán hàng',
                'department_id' => $department->id,
                'position_id' => $position->id,
            ])
            ->assertOk();

        $this->assertSame(User::STATUS_PENDING_EDIT, User::findOrFail($userId)->status);

        $this->actingAs($hr)
            ->patchJson("/api/users/{$userId}/resubmit")
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_PENDING);

        $this->actingAs($hr)
            ->putJson("/api/users/user/{$userId}", [
                'name' => 'Không được sửa sau khi gửi lại',
                'username' => 'pending_employee',
                'email' => 'pending.employee@demo.vn',
                'phone' => '0902999999',
                'password' => null,
                'password_confirmation' => null,
                'status' => User::STATUS_PENDING,
                'role' => 'Nhân viên bán hàng',
                'department_id' => $department->id,
                'position_id' => $position->id,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Yêu cầu đã được gửi duyệt lại và không thể chỉnh sửa trong lúc chờ phê duyệt.');

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'status' => User::STATUS_PENDING,
            'rejection_reason' => null,
            'rejected_by' => null,
            'rejected_at' => null,
        ]);

        User::whereKey($userId)->update([
            'status' => User::STATUS_PENDING_EDIT,
            'rejection_type' => 'reject_and_return',
            'resubmit_expires_at' => now()->subMinute(),
        ]);
        $this->actingAs($hr)
            ->patchJson("/api/users/{$userId}/resubmit")
            ->assertUnprocessable();
        $this->assertSame(User::STATUS_EXPIRED, User::findOrFail($userId)->status);

        User::whereKey($userId)->update([
            'status' => User::STATUS_PENDING,
            'rejection_count' => 2,
            'rejection_type' => null,
            'resubmit_expires_at' => null,
            'last_resubmitted_by' => $resubmittingManager->id,
            'last_resubmitted_at' => now(),
        ]);
        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/reject", [
                'reason' => 'Hồ sơ vẫn chưa đáp ứng yêu cầu sau ba lần.',
                'rejection_type' => 'reject_and_return',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_REJECTED_FINAL);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $resubmittingManager->id,
            'title' => 'Yêu cầu thêm nhân sự bị từ chối',
        ]);

        User::whereKey($userId)->update([
            'status' => User::STATUS_PENDING,
            'rejection_count' => 0,
        ]);
        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/reject", [
                'reason' => 'Không phê duyệt tuyển dụng cho vị trí này.',
                'rejection_type' => 'reject_final',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', User::STATUS_REJECTED_FINAL);
    }
}
