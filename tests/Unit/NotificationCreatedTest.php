<?php

namespace Tests\Unit;

use App\Events\NotificationCreated;
use App\Models\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Tests\TestCase;

class NotificationCreatedTest extends TestCase
{
    public function test_shared_notification_is_broadcast_to_the_user_on_every_module(): void
    {
        config()->set('notifications.subdomains', ['main', 'mua-hang', 'ban-hang']);

        $notification = new Notification([
            'user_id' => 12,
            'company_id' => 34,
            'title' => 'Thông báo dùng chung',
            'message' => 'Nội dung',
        ]);

        $event = new NotificationCreated($notification);
        $channelNames = collect($event->broadcastOn())->pluck('name')->all();

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);
        $this->assertSame([
            'private-company.34.12.main.notifications',
            'private-company.34.12.mua-hang.notifications',
            'private-company.34.12.ban-hang.notifications',
        ], $channelNames);
    }

    public function test_module_notification_is_only_broadcast_to_its_subdomain(): void
    {
        $notification = new Notification([
            'user_id' => 12,
            'company_id' => 34,
            'title' => 'Thông báo mua hàng',
            'message' => 'Nội dung',
            'subdomain' => 'mua-hang',
        ]);

        $channelNames = collect((new NotificationCreated($notification))->broadcastOn())
            ->pluck('name')
            ->all();

        $this->assertSame([
            'private-company.34.12.mua-hang.notifications',
        ], $channelNames);
    }
}
