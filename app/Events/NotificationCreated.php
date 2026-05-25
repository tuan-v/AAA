<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Kiểm tra có đủ thông tin cho kênh user trong company không
        if ($this->notification->user_id && $this->notification->company_id && $this->notification->subdomain) {
            // Kênh: company.{companyId}.{id}.{sub}.notifications
            $channelName = 'company.' .
                $this->notification->company_id . '.' .
                $this->notification->user_id . '.' .
                $this->notification->subdomain . '.notifications';

            $channels[] = new PrivateChannel($channelName);
        }
        // Kiểm tra có đủ thông tin cho kênh company không
        elseif ($this->notification->company_id && $this->notification->subdomain) {
            // Kênh: company.{companyId}.{sub}.notifications
            $channelName = 'company.' .
                $this->notification->company_id . '.' .
                $this->notification->subdomain . '.notifications';

            $channels[] = new PrivateChannel($channelName);
            Log::info('chanel domain');
        }
        // Kiểm tra có đủ thông tin cho kênh user cá nhân không
        elseif ($this->notification->user_id && $this->notification->subdomain) {
            // Kênh: user.{id}.{sub}.notifications
            $channelName = 'user.' .
                $this->notification->user_id . '.' .
                $this->notification->subdomain . '.notifications';

            $channels[] = new PrivateChannel($channelName);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'user_id' => $this->notification->user_id,
            'company_id' => $this->notification->company_id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'url_link' => $this->notification->url_link,
            'category' => $this->notification->category,
            'read_at' => $this->notification->read_at,
            'subdomain' => $this->notification->subdomain,
            'created_at' => $this->notification->created_at->toISOString(),
        ];
    }
}
