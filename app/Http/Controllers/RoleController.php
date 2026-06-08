<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')
            ->orderBy('id', 'desc')
            ->paginate(5);
    }

    public function permissions()
    {
        return Permission::all();
    }

    public function store(Request $request)
    {
        $role = Role::create([
            'name' => $request->name,
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
