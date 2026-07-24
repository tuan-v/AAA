<?php

namespace Tests\Feature;

use App\Events\NotificationCreated;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DepartmentDemoSeeder;
use Database\Seeders\DepartmentEmployeeDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_approve_reject_lock_and_unlock_are_logged(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->seed(DatabaseSeeder::class);
        $this->seed(DepartmentDemoSeeder::class);
        $this->seed(DepartmentEmployeeDemoSeeder::class);

        $hr = User::where('email', 'hr@demo.vn')->firstOrFail();
        $director = User::where('email', 'admin@demo.vn')->firstOrFail();
        $department = Department::where('company_id', $hr->company_id)
            ->where('code', 'PB-005')->firstOrFail();
        $position = Position::where('company_id', $hr->company_id)
            ->where('code', 'CV-105')->firstOrFail();

        $createdResponse = $this->actingAs($hr)->postJson('/api/users/user', [
            'name' => 'Nhân sự kiểm thử audit',
            'username' => 'audit_employee',
            'email' => 'audit.employee@demo.vn',
            'phone' => '0902777777',
            'password' => 'Test@123456',
            'password_confirmation' => 'Test@123456',
            'status' => User::STATUS_ACTIVE,
            'role' => 'Nhân viên bán hàng',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ])->assertCreated();

        $userId = $createdResponse->json('user.id');
        $this->assertUserLog($hr, $userId, 'create');

        $resubmitter = User::factory()->create([
            'company_id' => $hr->company_id,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::whereKey($userId)->update([
            'last_resubmitted_by' => $resubmitter->id,
            'last_resubmitted_at' => now(),
        ]);

        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/approve")
            ->assertOk();
        $this->assertUserLog($director, $userId, 'approve');

        $approvalNotifications = Notification::whereIn('user_id', [$hr->id, $resubmitter->id])
            ->where('title', 'Yêu cầu thêm nhân sự đã được duyệt')
            ->get();
        $this->assertCount(2, $approvalNotifications);
        $this->assertEqualsCanonicalizing(
            [$hr->id, $resubmitter->id],
            $approvalNotifications->pluck('user_id')->all()
        );
        foreach ($approvalNotifications as $approvalNotification) {
            $this->assertSame('employee_approved', $approvalNotification->data['event_type']);
            $this->assertSame('success', $approvalNotification->data['toast_type']);
        }

        User::whereKey($userId)->update(['status' => User::STATUS_PENDING]);
        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/reject", [
                'reason' => 'Thông tin kiểm thử chưa đầy đủ.',
                'rejection_type' => 'reject_and_return',
            ])
            ->assertOk();
        $this->assertUserLog($director, $userId, 'reject');
        $rejectionNotification = Notification::where('user_id', $hr->id)
            ->where('title', 'Yêu cầu thêm nhân sự bị từ chối')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('employee_rejected', $rejectionNotification->data['event_type']);
        $this->assertSame('error', $rejectionNotification->data['toast_type']);

        User::whereKey($userId)->update(['status' => User::STATUS_ACTIVE]);
        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/status", ['status' => User::STATUS_BLOCKED])
            ->assertOk();
        $this->assertUserLog($director, $userId, 'lock');

        $this->actingAs($director)
            ->patchJson("/api/users/{$userId}/status", ['status' => User::STATUS_ACTIVE])
            ->assertOk();
        $this->assertUserLog($director, $userId, 'unlock');
    }

    public function test_manager_cannot_review_a_pending_employee(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->seed(DatabaseSeeder::class);

        $director = User::where('email', 'admin@demo.vn')->firstOrFail();
        $manager = User::factory()->create([
            'name' => 'Quản lý kiểm thử',
            'company_id' => $director->company_id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $manager->companies()->syncWithoutDetaching([$director->company_id]);
        $manager->assignRole('Manager');

        $employee = User::factory()->create([
            'name' => 'Nhân viên chờ quản lý duyệt',
            'company_id' => $director->company_id,
            'status' => User::STATUS_PENDING,
            'creater_id' => $director->id,
        ]);
        $employee->companies()->syncWithoutDetaching([$director->company_id]);
        $employee->assignRole('Nhân viên bán hàng');

        $this->actingAs($manager)
            ->patchJson("/api/users/{$employee->id}/approve")
            ->assertForbidden();

        $this->actingAs($manager)
            ->patchJson("/api/users/{$employee->id}/reject", [
                'reason' => 'Thông tin nhân sự cần được bổ sung.',
                'rejection_type' => 'reject_and_return',
            ])
            ->assertForbidden();

        $this->assertSame(User::STATUS_PENDING, $employee->fresh()->status);
    }

    private function assertUserLog(User $actor, int $userId, string $action): void
    {
        $this->assertDatabaseHas('activity_logs', [
            'company_id' => $actor->company_id,
            'user_id' => $actor->id,
            'action' => $action,
            'model_type' => User::class,
            'model_id' => $userId,
        ]);

        $this->assertNotNull(ActivityLog::query()
            ->where('user_id', $actor->id)
            ->where('model_type', User::class)
            ->where('model_id', $userId)
            ->where('action', $action)
            ->value('new_values'));
    }
}
