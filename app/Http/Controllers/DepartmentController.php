<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    private function companyId(): int
    {
        $id = auth()->user()->company_id;
        abort_unless($id, 403, 'Tài khoản chưa thuộc công ty nào.');
        return (int) $id;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return Department::query()
            ->with('manager:id,name')
            ->withCount('users')
            ->where('company_id', $this->companyId())
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"));
            })
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate($validated['per_page'] ?? 10);
    }

    public function all()
    {
        return Department::where('company_id', $this->companyId())
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();
        $validated = $this->validateDepartment($request, $companyId);
        $validated['company_id'] = $companyId;
        $validated['code'] = $this->nextCode($companyId);

        return response()->json([
            'message' => 'Thêm phòng ban thành công.',
            'data' => Department::create($validated),
        ], 201);
    }

    public function update(Request $request, Department $department)
    {
        abort_unless((int) $department->company_id === $this->companyId(), 404);
        $department->update($this->validateDepartment($request, $department->company_id, $department->id));
        return response()->json(['message' => 'Cập nhật phòng ban thành công.', 'data' => $department->fresh('manager:id,name')]);
    }

    public function destroy(Department $department)
    {
        abort_unless((int) $department->company_id === $this->companyId(), 404);
        if ($department->users()->exists() || $department->positions()->exists()) {
            return response()->json(['message' => 'Phòng ban đã có nhân sự, chỉ có thể ngừng hoạt động.'], 422);
        }
        $department->delete();
        return response()->json(['message' => 'Xóa phòng ban thành công.']);
    }

    private function validateDepartment(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->where('company_id', $companyId)->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'manager_id' => ['nullable', Rule::exists('users', 'id')->where('company_id', $companyId)],
        ]);
    }

    private function nextCode(int $companyId): string
    {
        $number = ((int) Department::where('company_id', $companyId)->max('id')) + 1;
        do {
            $code = 'PB-'.str_pad((string) $number++, 3, '0', STR_PAD_LEFT);
        } while (Department::where('company_id', $companyId)->where('code', $code)->exists());
        return $code;
    }
}
