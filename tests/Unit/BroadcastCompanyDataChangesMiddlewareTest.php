<?php

namespace Tests\Unit;

use App\Events\CompanyDataChanged;
use App\Http\Middleware\BroadcastCompanyDataChanges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BroadcastCompanyDataChangesMiddlewareTest extends TestCase
{
    public function test_successful_write_dispatches_company_change_event(): void
    {
        Event::fake([CompanyDataChanged::class]);
        $request = $this->request('POST', 'api/sale/orders');

        app(BroadcastCompanyDataChanges::class)->handle(
            $request,
            fn () => new Response('', 201)
        );

        Event::assertDispatched(
            CompanyDataChanged::class,
            fn (CompanyDataChanged $event) => $event->companyId === 7
                && $event->actorId === 9
                && $event->path === 'api/sale/orders'
        );
    }

    public function test_reads_failed_writes_and_notification_actions_do_not_dispatch(): void
    {
        Event::fake([CompanyDataChanged::class]);
        $middleware = app(BroadcastCompanyDataChanges::class);

        $middleware->handle($this->request('GET', 'api/sale/orders'), fn () => new Response);
        $middleware->handle($this->request('POST', 'api/sale/orders'), fn () => new Response('', 422));
        $middleware->handle(
            $this->request('POST', 'api/notifications/1/mark-as-read'),
            fn () => new Response
        );

        Event::assertNotDispatched(CompanyDataChanged::class);
    }

    private function request(string $method, string $path): Request
    {
        $request = Request::create('/'.$path, $method);
        $request->setUserResolver(fn () => (object) ['id' => 9, 'company_id' => 7]);

        return $request;
    }
}
