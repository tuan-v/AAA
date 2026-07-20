<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        $isSuperAdmin = $currentUser->hasRole('Super Admin');

        $query = Role::query()
            ->where(function ($q) use ($currentUser) {

                // Role hệ thống
                $q->where('type', 'system')

                    // Role của công ty hiện tại
                    ->orWhere(function ($q) use ($currentUser) {
                        $q->where('type', 'user')
                            ->where('company_id', $currentUser->company_id);
                    });
            });

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! $isSuperAdmin) {
            $query->where('is_protected', false);
        }

        $roles = $query->orderByRaw("FIELD(type, 'system', 'user')")
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                // Dùng scope role() có sẵn từ Spatie (phía User), không dùng $role->users()
                $role->users_count = User::role($role->name)->count();
                $role->permissions_list = $role->permissions->pluck('name');
                return $role;
            });

        return response()->json([
            'data' => [
                'system' => $roles->where('type', 'system')->values(),
                'user'   => $roles->where('type', 'user')->values(),
            ],
        ]);
    }

    public function permissions()
    {
        return Permission::orderBy('name')->get();
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => [
                'required',
                Rule::unique('roles')->where(function ($q) {
                    return $q->where(
                        'company_id',
                        auth()->user()->company_id
                    );
                }),
            ],
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::create(
            [
                'company_id' => auth()->user()->company_id,
                'name' => $validated['name'],
                'guard_name' => 'web',
                'type' => 'user',
                'is_protected' => false,
                'description' => $validated['description'] ?? null,
            ]

        );

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Tạo vai trò thành công',
            'data' => $role,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $role = Role::where(function ($q) {

            $q->where('type', 'system')

                ->orWhere(function ($q) {

                    $q->where('company_id', auth()->user()->company_id)
                        ->where('type', 'user');
                });
        })->findOrFail($id);

        $this->guardAgainstProtectedRole($request, $role, 'chỉnh sửa');

        $validated = $request->validate([
            'name' => [
                'required',
                Rule::unique('roles')
                    ->ignore($role->id)
                    ->where(function ($q) {
                        return $q->where(
                            'company_id',
                            auth()->user()->company_id
                        );
                    }),
            ],
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json(['message' => 'Cập nhật vai trò thành công']);
    }

    public function destroy(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $this->guardAgainstProtectedRole($request, $role, 'xóa');

        if ($role->isSystem()) {
            return response()->json([
                'message' => 'Không thể xóa vai trò hệ thống.',
            ], 422);
        }

        // Dùng scope role() có sẵn từ Spatie thay vì $role->users()
        if (User::role($role->name)->exists()) {
            return response()->json([
                'message' => 'Vai trò đang được gán cho người dùng, không thể xóa.',
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Xóa vai trò thành công']);
    }

    private function guardAgainstProtectedRole(Request $request, Role $role, string $action): void
    {
        if ($role->is_protected && ! $request->user()->hasRole('Super Admin')) {
            abort(403, "Bạn không có quyền {$action} vai trò này.");
        }
    }
}
