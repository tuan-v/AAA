<?php

namespace Tests\Feature;

use App\Events\NotificationCreated;
use App\Models\Company;
use App\Models\User;
use App\Services\NotificationService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationRecipientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_order_approval_notification_reaches_active_approvers_and_company_owner(): void
    {
        Event::fake([NotificationCreated::class]);
        $this->seed(PermissionSeeder::class);

        $owner = User::factory()->create(['status' => User::STATUS_ACTIVE]);
        $company = Company::create([
            'name' => 'Công ty nhận thông báo',
            'address' => 'TP.HCM',
            'phone' => '0901000001',
            'owner_id' => $owner->id,
        ]);
        $owner->update(['company_id' => $company->id]);

        $creator = User::factory()->create([
            'company_id' => $company->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $approver = User::factory()->create([
            'company_id' => $company->id,
            'status' => User::STATUS_ACTIVE,
        ]);
        $approver->givePermissionTo('don_ban.duyet');

        $inactiveApprover = User::factory()->create([
            'company_id' => $company->id,
            'status' => User::STATUS_BLOCKED,
        ]);
        $inactiveApprover->givePermissionTo('don_ban.duyet');

        $unrelatedUser = User::factory()->create([
            'company_id' => $company->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $notifications = app(NotificationService::class)->createForPermission(
            'don_ban.duyet',
            $company->id,
            'Đơn bán mới chờ duyệt',
            'Đơn bán SO-001 đang chờ duyệt.',
            ['sales_order_id' => 1],
            '/sale/orders',
            $creator->id,
            'sale',
        );

        $this->assertEqualsCanonicalizing(
            [$owner->id, $approver->id],
            $notifications->pluck('user_id')->all(),
        );
        $this->assertFalse($notifications->contains('user_id', $creator->id));
        $this->assertFalse($notifications->contains('user_id', $inactiveApprover->id));
        $this->assertFalse($notifications->contains('user_id', $unrelatedUser->id));
    }
}
