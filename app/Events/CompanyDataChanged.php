<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyDataChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $companyId,
        public readonly int $actorId,
        public readonly string $method,
        public readonly string $path,
        public readonly string $occurredAt,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return collect(config('notifications.subdomains', ['main']))
            ->map(fn (string $subdomain) => new PrivateChannel(sprintf(
                'company.%d.%s.data',
                $this->companyId,
                $subdomain
            )))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'company.data.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'company_id' => $this->companyId,
            'actor_id' => $this->actorId,
            'method' => $this->method,
            'path' => $this->path,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
