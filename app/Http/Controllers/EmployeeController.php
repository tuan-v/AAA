<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        return User::with('roles')
            ->latest()
            ->get()
            ->map(function ($item) {

                return [

                    'id' => $item->id,

                    'name' => $item->name,

                    'username' => $item->username,

                    'email' => $item->email,

                    'phone' => $item->phone,

                    'status' => $item->status,

                    'role_name' =>
                        $item->roles
                            ->pluck('name')
                            ->join(', ')
                ];
            });
    }

    public function roles()
    {
        return Role::all();
    }

    public function store(Request $request)
    {
        $user = User::create([

            'name' => $request->name,

            'username' => $request->username,

            'email' => $request->email,

            'phone' => $request->phone,

            'password' =>
                Hash::make(
                    $request->password
                ),

            'status' =>
                $request->status
        ]);

        $user->assignRole(
            $request->role
        );

        return response()->json();
    }

    public function update(
        Request $request,
        User $employee
    ) {

        $employee->update([

            'name' => $request->name,

            'username' => $request->username,

            'email' => $request->email,

            'phone' => $request->phone,

            'status' => $request->status
        ]);

        $employee->syncRoles([
            $request->role
        ]);

        return response()->json();
    }

    public function destroy(
        User $employee
    ) {
        $employee->delete();

        return response()->json();
    }
}