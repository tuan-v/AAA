<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        // Chạy qua Request để thực hiện hành động trước
        $response = $next($request);

        // Chỉ log khi user đã đăng nhập và thực hiện các thao tác thay đổi dữ liệu
        if (Auth::check() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {

            // Xác định module dựa trên URL
            $segments = $request->segments();
            $module = isset($segments[1]) ? ucfirst($segments[1]) : 'Hệ thống';

            // Tạo log mô tả cơ bản
            ActivityLog::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'action' => $request->method(),
                'model_type' => 'Request:' . $module,
                'model_id' => 0,
                'description' => "Người dùng " . Auth::user()->name . " thực hiện thao tác tại đường dẫn: " . $request->getRequestUri(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'new_values' => $request->except(['password', 'password_confirmation']),
            ]);
        }

        return $response;
    }
}
