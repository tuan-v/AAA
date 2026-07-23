<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Company;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                $query->where(fn($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"));
            })
            ->when($validated['status'] ?? null, fn($query, $status) => $query->where('status', $status))
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

    public function managers()
    {
        $companyId = $this->companyId();
        $ownerId = Company::whereKey($companyId)->value('owner_id');

        return User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhereHas(
                        'companies',
                        fn($companyQuery) =>
                        $companyQuery->where('companies.id', $companyId)
                    );
            })
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$ownerId ?: 0])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id', 'position_id'])
            ->map(fn($user) => [
                ...$user->toArray(),
                'is_company_owner' => (int) $user->id === (int) $ownerId,
            ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();
        $validated = $this->validateDepartment($request, $companyId);
        $validated['company_id'] = $companyId;
        $validated['code'] = $this->nextCode($companyId);

        $department = DB::transaction(function () use ($validated) {
            $department = Department::create($validated);
            $this->syncManagerAssignment($department);
            return $department->fresh('manager:id,name');
        });

        return response()->json([
            'message' => 'Thêm phòng ban thành công.',
            'data' => $department,
        ], 201);
    }

    public function update(Request $request, Department $department)
    {
        abort_unless((int) $department->company_id === $this->companyId(), 404);
        DB::transaction(function () use ($request, $department) {
            $oldManagerId = $department->manager_id;
            $department->update($this->validateDepartment($request, $department->company_id, $department->id));
            $this->syncManagerAssignment($department, $oldManagerId);
        });
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
            'manager_id' => ['nullable', 'integer', function ($attribute, $value, $fail) use ($companyId) {
                if (! User::query()->whereKey($value)->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId)
                        ->orWhereHas(
                            'companies',
                            fn($companyQuery) =>
                            $companyQuery->where('companies.id', $companyId)
                        );
                })->exists()) {
                    $fail('Trưởng phòng không tồn tại hoặc không thuộc công ty.');
                }
            }],
        ], [
            'name.required' => 'Vui lòng nhập tên phòng ban.',
            'name.string' => 'Tên phòng ban không hợp lệ.',
            'name.max' => 'Tên phòng ban không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên phòng ban đã tồn tại trong công ty.',
            'description.string' => 'Mô tả phòng ban không hợp lệ.',
            'description.max' => 'Mô tả không được vượt quá 1.000 ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'manager_id.exists' => 'Trưởng phòng không tồn tại hoặc không thuộc công ty.',
        ]);
    }

    private function nextCode(int $companyId): string
    {
        $usedNumbers = Department::where('company_id', $companyId)->pluck('code')
            ->map(function ($code) {
                return preg_match('/^PB-(\d+)$/', (string) $code, $matches) ? (int) $matches[1] : null;
            })->filter()->flip();

        $number = 1;
        while ($usedNumbers->has($number)) $number++;

        return 'PB-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function syncManagerAssignment(Department $department, ?int $oldManagerId = null): void
    {
        $position = Position::query()
            ->where('company_id', $department->company_id)
            ->where('department_id', $department->id)
            ->where('description', 'Chức vụ trưởng phòng được tạo tự động.')
            ->first();

        if (! $position && ! $department->manager_id) return;

        if (! $position) {
            $position = Position::create([
                'company_id' => $department->company_id,
                'department_id' => $department->id,
                'code' => $this->nextPositionCode((int) $department->company_id),
                'name' => 'Trưởng phòng ' . $department->name,
                'description' => 'Chức vụ trưởng phòng được tạo tự động.',
                'status' => 'active',
            ]);
        }

        if ($oldManagerId && (int) $oldManagerId !== (int) $department->manager_id) {
            User::whereKey($oldManagerId)
                ->where('department_id', $department->id)
                ->where('position_id', $position->id)
                ->update(['department_id' => null, 'position_id' => null]);
        }

        if (! $department->manager_id) return;

        $ownerId = Company::whereKey($department->company_id)->value('owner_id');
        if ((int) $department->manager_id === (int) $ownerId) return;

        User::whereKey($department->manager_id)
            ->where('company_id', $department->company_id)
            ->update(['department_id' => $department->id, 'position_id' => $position->id]);
    }

    private function nextPositionCode(int $companyId): string
    {
        return app(\App\Services\CodeGeneratorService::class)
            ->generate(Position::class, 'CV-', 3, $companyId);
    }
}
