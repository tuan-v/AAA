<?php

namespace Tests\Unit;

use App\Events\CompanyDataChanged;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Tests\TestCase;

class CompanyDataChangedTest extends TestCase
{
    public function test_change_is_broadcast_to_every_module_of_the_company(): void
    {
        config()->set('notifications.subdomains', ['main', 'kho']);

        $event = new CompanyDataChanged(
            companyId: 10,
            actorId: 20,
            method: 'PATCH',
            path: 'api/warehouse/slips/1/approve',
            occurredAt: now()->toISOString(),
        );

        $channels = collect($event->broadcastOn())->pluck('name')->all();

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);
        $this->assertSame([
            'private-company.10.main.data',
            'private-company.10.kho.data',
        ], $channels);
    }
}
