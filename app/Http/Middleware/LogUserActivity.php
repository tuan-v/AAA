<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        // bỏ qua nếu chưa login
        if (!auth()->check()) {
            return $next($request);
        }

        // bỏ request rác
        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        // xác định action ERP
        $action = $this->resolveAction($request);

        // xác định module hệ thống
        $module = $this->detectModule($request->path());

        $response = $next($request);

        // chỉ log các request quan trọng
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,

            // nếu chưa gắn model cụ thể thì để null
            'model_type' => null,
            'model_id' => null,

            // mô tả dễ đọc cho admin
            'description' => strtoupper($action) . ' ' . $request->path(),

            // metadata ERP
            'new_values' => [
                'module' => $module,
                'url' => $request->path(),
                'method' => $request->method(),
                'full_url' => $request->fullUrl(),
            ],

            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $response;
    }

    /**
     * Xác định hành động ERP
     */
    private function resolveAction(Request $request): string
    {
        return match ($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };
    }

    /**
     * Xác định module hệ thống
     */
    private function detectModule(string $path): string
    {
        return match (true) {
            str_contains($path, 'warehouse') => 'warehouse',
            str_contains($path, 'slip') => 'warehouse',
            str_contains($path, 'sales') => 'sales',
            str_contains($path, 'purchase') => 'purchase',
            str_contains($path, 'product') => 'product',
            str_contains($path, 'auth') => 'auth',
            default => 'system',
        };
    }

    /**
     * Lọc request không cần log
     */
    private function shouldIgnore(Request $request): bool
    {
        $ignorePaths = [
            'favicon.ico',
            'sanctum',
            'api/ping',
            'storage',
            '_debugbar',
        ];

        foreach ($ignorePaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }

        return false;
    }
}
