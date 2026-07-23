<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId($request);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', Rule::in(array_keys(ActivityLog::ACTION_ALIASES))],
            'model_type' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date', 'before_or_equal:today'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from', 'before_or_equal:today'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = ActivityLog::query()->forCompany($companyId)->trackable()->with('user:id,name')
            ->select([
                'id',
                'user_id',
                'action',
                'description',
                'model_type',
                'model_id',
                'ip_address',
                'created_at'
            ]);

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($validated['action'])) {
            $query->whereIn('action', ActivityLog::aliasesFor($validated['action']));
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

        $perPage = (int) ($validated['per_page'] ?? 15);

        $logs = $query->latest()->paginate($perPage);

        // === FORMAT THỜI GIAN ===
        $logs->getCollection()->transform(fn (ActivityLog $log) => $this->present($log));

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
            ->forCompany($this->companyId($request))
            ->trackable()
            ->with('user:id,name')
            ->where('model_type', $fullModelType)
            ->where('model_id', $request->model_id)
            ->orderBy('created_at', 'asc') // theo trình tự thời gian, dễ theo dõi diễn biến
            ->get();

        $logs->transform(fn (ActivityLog $log) => $this->present($log));

        return response()->json(['data' => $logs]);
    }

    public function show(ActivityLog $auditLog)
    {
        abort_unless((int) $auditLog->company_id === $this->companyId(request()), 404);
        abort_unless(ActivityLog::canonicalAction($auditLog->action) && $auditLog->model_id > 0, 404);

        return $this->present($auditLog->load('user:id,name'));
    }

    private function companyId(Request $request): int
    {
        $companyId = $request->user()?->company_id;
        abort_unless($companyId, 403, 'Tài khoản chưa thuộc công ty nào.');

        return (int) $companyId;
    }

    private function present(ActivityLog $log): ActivityLog
    {
        $canonicalAction = ActivityLog::canonicalAction($log->action);

        $log->setAttribute('action_key', $canonicalAction);
        $log->setAttribute('action_label', ActivityLog::actionLabel($log->action));
        $log->setAttribute('model_label', ActivityLog::modelLabel($log->model_type));
        $log->setAttribute(
            'created_at_formatted',
            $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s')
        );

        return $log;
    }
}
