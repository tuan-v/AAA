<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user:id,name')
            ->select([
                'id',
                'user_id',
                'action',
                'description',
                'model_type',
                'model_id',
                'old_values',
                'new_values',
                'ip_address',
                'created_at'
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            // Frontend gửi tên ngắn gọn, vd "WarehouseSlip" thay vì full namespace
            $query->where('model_type', 'like', '%' . $request->model_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);

        $logs = $query->latest()->paginate($perPage);

        // === FORMAT THỜI GIAN ===
        $logs->getCollection()->transform(function ($log) {
            $createdAt = $log->created_at;

            // Định dạng đúng như bạn muốn
            $log->created_at_formatted = $createdAt
                ? $createdAt->format('d/m/Y H:i:s')
                : null;

            return $log;
        });

        return $logs;
    }
    /**
     * Truy vết lịch sử của MỘT tài liệu cụ thể.
     * Dùng để nhúng vào modal chi tiết của Phiếu kho, Đơn mua, Đơn bán...
     *
     * Ví dụ gọi: GET /api/audit-logs/trace?model_type=WarehouseSlip&model_id=105
     */
    public function trace(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $fullModelType = 'App\\Models\\' . $request->model_type;

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->where('model_type', $fullModelType)
            ->where('model_id', $request->model_id)
            ->orderBy('created_at', 'asc') // theo trình tự thời gian, dễ theo dõi diễn biến
            ->get();

        return response()->json(['data' => $logs]);
    }

    public function show(ActivityLog $auditLog)
    {
        return $auditLog->load('user:id,name');
    }
}
