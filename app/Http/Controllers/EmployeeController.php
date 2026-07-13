<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);
        return User::with('roles')
            ->latest()
            ->orderByDesc('id', 'desc')
            ->paginate($perPage)
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
        ], [
            'name.required' => 'Tên nhân viên không được để trống',
            'username.required' => 'Tên đăng nhập không được để trống',
            'username.unique' => 'Tên đăng nhập đã tồn tại',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.unique' => 'Số điện thoại đã tồn tại',
            'phone.regex' => 'Số điện thoại không hợp lệ',
            'phone.numeric' => 'Số điện thoại không hợp lệ',
            'password.required' => 'Mật khẩu không được để trống',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'status.required' => 'Trạng thái không được để trống',
            'status.in' => 'Trạng thái không hợp lệ',
            'role.required' => 'Vai trò không được để trống',
            'role.exists' => 'Vai trò không tồn tại',
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
        ], [
            'name.required' => 'Tên nhân viên không được để trống',
            'username.required' => 'Tên đăng nhập không được để trống',
            'username.unique' => 'Tên đăng nhập đã tồn tại',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.unique' => 'Số điện thoại đã tồn tại',
            'phone.regex' => 'Số điện thoại không hợp lệ',
            'phone.numeric' => 'Số điện thoại không hợp lệ',
            'status.required' => 'Trạng thái không được để trống',
            'status.in' => 'Trạng thái không hợp lệ',
            'role.required' => 'Vai trò không được để trống',
            'role.exists' => 'Vai trò không tồn tại',
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
