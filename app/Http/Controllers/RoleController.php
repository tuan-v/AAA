<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);
        return Role::with('permissions')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function permissions()
    {
        return Permission::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name'
            ],

        ], [
            'name.required' => 'Vui lòng nhập tên vai trò',
            'name.unique' => 'Tên vai trò đã tồn tại',
        ]);
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);


        $role->syncPermissions(
            $request->permissions ?? []
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $role->update([
            'name' => $request->name
        ]);

        $role->syncPermissions(
            $request->permissions ?? []
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
