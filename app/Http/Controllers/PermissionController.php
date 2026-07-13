<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        return response()->json(
            Permission::orderBy('id', 'desc')
                ->paginate($perPage)
        );
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2',
        ]);
        $permission = Permission::create([
            'name' => $request->name,
            'group' => $request->group,
            'guard_name' => 'web'
        ]);

        return response()->json([
            'message' => "Thêm thành công",
            'data' => $permission
        ]);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $permission->update([
            'name' => $request->name,
            'group' => $request->group,
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
