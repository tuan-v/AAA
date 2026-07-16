<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        $isSuperAdmin = $currentUser->hasRole('Super Admin');

        $query = Role::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'type' => 'user',
            'is_protected' => false,
        ]);

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
        $role = Role::findOrFail($id);

        $this->guardAgainstProtectedRole($request, $role, 'chỉnh sửa');

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->update(['name' => $validated['name']]);

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
