<?php

namespace App\Http\Middleware;

use App\Events\CompanyDataChanged;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BroadcastCompanyDataChanges
{
    private const WRITE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    private const EXCLUDED_PATHS = [
        'api/notifications',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        if (
            ! $user?->company_id
            || ! in_array($request->method(), self::WRITE_METHODS, true)
            || $response->getStatusCode() >= 400
            || $this->isExcluded($request)
        ) {
            return $response;
        }

        broadcast(new CompanyDataChanged(
            (int) $user->company_id,
            (int) $user->id,
            $request->method(),
            $request->path(),
            now()->toISOString(),
        ))->toOthers();

        return $response;
    }

    private function isExcluded(Request $request): bool
    {
        return collect(self::EXCLUDED_PATHS)
            ->contains(fn (string $path) => str_starts_with($request->path(), $path));
    }
}
