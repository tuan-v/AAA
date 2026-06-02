<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::paginate(10);
    }

    public function store(Request $request)
    {
        Permission::create([
            'name' => $request->name,
            'group' => $request->group,
            'guard_name' => 'web'
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $permission->update([
            'name' => $request->name,
            'group' => $request->group
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function destroy($id)
    {
        Permission::findOrFail($id)->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
