<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * A notification without a subdomain is shared across all modules. A
     * notification with a subdomain is broadcast only to that module.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $subdomains = $this->notification->subdomain
            ? [$this->notification->subdomain]
            : config('notifications.subdomains', ['main']);

        return collect($subdomains)
            ->map(fn (string $subdomain) => $this->channelFor($subdomain))
            ->filter()
            ->values()
            ->all();
    }

    private function channelFor(string $subdomain): ?PrivateChannel
    {
        if ($this->notification->user_id && $this->notification->company_id) {
            return new PrivateChannel(sprintf(
                'company.%d.%d.%s.notifications',
                $this->notification->company_id,
                $this->notification->user_id,
                $subdomain
            ));
        }

        if ($this->notification->company_id) {
            return new PrivateChannel(sprintf(
                'company.%d.%s.notifications',
                $this->notification->company_id,
                $subdomain
            ));
        }

        if ($this->notification->user_id) {
            return new PrivateChannel(sprintf(
                'user.%d.%s.notifications',
                $this->notification->user_id,
                $subdomain
            ));
        }

        return null;
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

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
