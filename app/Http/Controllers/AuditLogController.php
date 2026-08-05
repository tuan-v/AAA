<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                'old_values',
                'new_values',
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

        $log = $this->present($auditLog->load('user:id,name'));
        $log->setAttribute('relation_labels', $this->relationLabels($log));

        return $log;
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
        $log->setAttribute('record_reference', $this->recordReference($log));
        $log->setAttribute('summary', $this->activitySummary($log, $canonicalAction));
        $log->setAttribute(
            'created_at_formatted',
            $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s')
        );

        return $log;
    }

    private function recordReference(ActivityLog $log): string
    {
        $values = array_merge($log->old_values ?? [], $log->new_values ?? []);

        foreach (['code', 'name', 'username', 'email'] as $field) {
            if (! empty($values[$field]) && is_scalar($values[$field])) {
                return (string) $values[$field];
            }
        }

        return '#'.$log->model_id;
    }

    private function activitySummary(ActivityLog $log, ?string $action): string
    {
        $verbs = [
            'create' => 'đã tạo',
            'update' => 'đã cập nhật',
            'approve' => 'đã duyệt',
            'reject' => 'đã từ chối',
            'cancel' => 'đã hủy',
            'delete' => 'đã xóa',
            'lock' => 'đã khóa',
            'unlock' => 'đã mở khóa',
        ];
        $actor = $log->user?->name ?? 'Hệ thống';
        $model = mb_strtolower(ActivityLog::modelLabel($log->model_type));

        return trim($actor.' '.($verbs[$action] ?? 'đã thao tác').' '.$model.' '.$this->recordReference($log));
    }

    private function relationLabels(ActivityLog $log): array
    {
        $definitions = [
            'user_id' => ['users', ['name', 'email']],
            'created_by' => ['users', ['name', 'email']],
            'approved_by' => ['users', ['name', 'email']],
            'rejected_by' => ['users', ['name', 'email']],
            'submitted_to_accountant_by' => ['users', ['name', 'email']],
            'manager_id' => ['users', ['name', 'email']],
            'creater_id' => ['users', ['name', 'email']],
            'last_resubmitted_by' => ['users', ['name', 'email']],
            'return_requested_by' => ['users', ['name', 'email']],
            'return_received_by' => ['users', ['name', 'email']],
            'return_approved_by' => ['users', ['name', 'email']],
            'warehouse_id' => ['warehouses', ['name', 'code']],
            'pos_warehouse_id' => ['warehouses', ['name', 'code']],
            'from_warehouse_id' => ['warehouses', ['name', 'code']],
            'to_warehouse_id' => ['warehouses', ['name', 'code']],
            'customer_id' => ['customers', ['name', 'code']],
            'customer_account_id' => ['customer_accounts', ['name', 'email']],
            'supplier_id' => ['suppliers', ['name', 'code']],
            'currency_id' => ['currencies', ['name', 'code']],
            'payment_currency_id' => ['currencies', ['name', 'code']],
            'department_id' => ['departments', ['name', 'code']],
            'position_id' => ['positions', ['name', 'code']],
            'product_id' => ['products', ['name', 'code']],
            'unit_id' => ['units', ['name', 'code']],
            'bank_id' => ['banks', ['name', 'code']],
            'account_id' => ['accounts', ['name', 'code']],
            'from_account_id' => ['accounts', ['name', 'code']],
            'to_account_id' => ['accounts', ['name', 'code']],
            'purchase_order_id' => ['purchase_orders', ['code']],
            'sales_order_id' => ['sales_orders', ['code']],
            'return_of_slip_id' => ['warehouse_slips', ['code']],
            'shipping_partner_id' => ['shipping_partners', ['name', 'code']],
            'province_id' => ['provinces', ['name', 'code']],
            'ward_id' => ['wards', ['name', 'code']],
            'pos_coupon_id' => ['pos_coupons', ['name', 'code']],
            'address_id' => ['addresses', ['address_detail']],
        ];
        $definitions['category_id'] = class_basename($log->model_type) === 'Transaction'
            ? ['transaction_categories', ['name', 'code']]
            : ['categories', ['name', 'code']];

        $labels = [];

        foreach ($definitions as $field => [$table, $columns]) {
            $ids = collect([$log->old_values[$field] ?? null, $log->new_values[$field] ?? null])
                ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
                ->map(fn ($id) => (int) $id)
                ->unique();
            if ($ids->isEmpty() || ! Schema::hasTable($table)) {
                continue;
            }

            $availableColumns = collect($columns)->filter(fn ($column) => Schema::hasColumn($table, $column))->values();
            $query = DB::table($table)->whereIn('id', $ids);
            if (Schema::hasColumn($table, 'company_id')) {
                $query->where('company_id', $log->company_id);
            }

            foreach ($query->get(['id', ...$availableColumns])->all() as $record) {
                $parts = $availableColumns->map(fn ($column) => $record->{$column} ?? null)->filter()->values();
                if ($parts->isEmpty()) {
                    continue;
                }
                $labels[$field][(string) $record->id] = $parts->count() > 1
                    ? $parts[0].' ('.$parts[1].')'
                    : (string) $parts[0];
            }
        }

        return $labels;
    }
}
