<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    private function companyId(): int
    {
        $id = auth()->user()->company_id;
        abort_unless($id, 403, 'Tài khoản chưa thuộc công ty nào.');
        return (int) $id;
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'department_id' => ['nullable', 'integer'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        return Position::with('department:id,code,name')->withCount('users')->where('company_id', $this->companyId())
            ->when($data['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")))
            ->when($data['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($data['department_id'] ?? null, fn ($q, $v) => $q->where('department_id', $v))
            ->latest('id')->paginate($data['per_page'] ?? 10);
    }

    public function all(Request $request)
    {
        $request->validate(['department_id' => ['nullable', 'integer']]);
        return Position::where('company_id', $this->companyId())->where('status', 'active')
            ->when($request->department_id, fn ($q, $id) => $q->where('department_id', $id))
            ->orderBy('name')->get(['id', 'department_id', 'code', 'name']);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();
        $data = $this->validated($request, $companyId);
        $data += ['company_id' => $companyId, 'code' => $this->nextCode($companyId)];
        $position = Position::create($data);

        $this->notificationService->createForHigherRoleUsers(
            $request->user(),
            $companyId,
            'Chức vụ mới được tạo',
            "{$request->user()->name} đã tạo chức vụ {$position->name}.",
            ['position_id' => $position->id, 'department_id' => $position->department_id],
            '/positions',
            'management'
        );

        return response()->json(['message' => 'Thêm chức vụ thành công.', 'data' => $position], 201);
    }

    public function update(Request $request, Position $position)
    {
        abort_unless((int) $position->company_id === $this->companyId(), 404);
        $position->update($this->validated($request, $position->company_id, $position->id));
        return response()->json(['message' => 'Cập nhật chức vụ thành công.', 'data' => $position->fresh('department:id,code,name')]);
    }

    public function destroy(Request $request, Position $position)
    {
        abort_unless((int) $position->company_id === $this->companyId(), 404);
        if ($position->users()->exists()) return response()->json(['message' => 'Chức vụ đã được gán cho nhân sự, chỉ có thể ngừng hoạt động.'], 422);

        $positionData = [
            'id' => $position->id,
            'name' => $position->name,
            'company_id' => (int) $position->company_id,
        ];

        ActivityLog::create([
            'company_id' => $request->user()->company_id,
            'user_id' => $request->user()->id,
            'action' => 'delete',
            'model_type' => Position::class,
            'model_id' => $position->id,
            'old_values' => $position->toArray(),
            'new_values' => null,
            'description' => "Xóa chức vụ #{$position->id}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        $position->delete();

        $this->notificationService->createForHigherRoleUsers(
            $request->user(),
            $positionData['company_id'],
            'Chức vụ đã bị xóa',
            "{$request->user()->name} đã xóa chức vụ {$positionData['name']}.",
            ['position_id' => $positionData['id'], 'position_name' => $positionData['name']],
            '/positions',
            'management'
        );

        return response()->json(['message' => 'Xóa chức vụ thành công.']);
    }

    private function validated(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'department_id' => ['required', Rule::exists('departments', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('status', 'active'))],
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->where('company_id', $companyId)->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:1000'], 'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'department_id.exists' => 'Phòng ban không hợp lệ hoặc đã ngừng hoạt động.',
            'name.required' => 'Vui lòng nhập tên chức vụ.',
            'name.string' => 'Tên chức vụ phải là chuỗi ký tự.',
            'name.max' => 'Tên chức vụ không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên chức vụ đã tồn tại trong công ty.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'description.max' => 'Mô tả không được vượt quá 1.000 ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);
    }

    private function nextCode(int $companyId): string
    {
        return app(\App\Services\CodeGeneratorService::class)
            ->generate(Position::class, 'CV-', 3, $companyId);
    }
}
