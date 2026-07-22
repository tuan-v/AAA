<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_read_and_delete_only_their_notifications(): void
    {
        $user = User::create([
            'name' => 'Người nhận', 'username' => 'receiver',
            'email' => 'receiver@example.com', 'password' => Hash::make('password'),
            'type' => 'user', 'status' => 'active',
        ]);
        $other = User::create([
            'name' => 'Người khác', 'username' => 'other',
            'email' => 'other@example.com', 'password' => Hash::make('password'),
            'type' => 'user', 'status' => 'active',
        ]);
        $company = Company::create([
            'name' => 'Công ty thông báo', 'address' => 'Hà Nội',
            'phone' => '0900000000', 'owner_id' => $user->id,
        ]);
        $user->update(['company_id' => $company->id]);
        $other->update(['company_id' => $company->id]);

        $mine = Notification::create([
            'user_id' => $user->id, 'company_id' => $company->id,
            'title' => 'Giao dịch mới', 'message' => 'Có giao dịch chờ duyệt.',
            'category' => 'order',
        ]);
        $notMine = Notification::create([
            'user_id' => $other->id, 'company_id' => $company->id,
            'title' => 'Thông báo riêng', 'message' => 'Không thuộc người dùng hiện tại.',
        ]);

        $this->actingAs($user)->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.data.0.id', $mine->id)
            ->assertJsonMissing(['id' => $notMine->id]);

        $this->getJson('/api/notifications/unread-count')
            ->assertOk()->assertJsonPath('count', 1);
        $this->postJson("/api/notifications/{$mine->id}/mark-as-read")->assertOk();
        $this->postJson("/api/notifications/{$notMine->id}/mark-as-read")->assertNotFound();
        $this->deleteJson("/api/notifications/{$mine->id}")->assertOk();
    }
}
