<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    { {
            $users =
                User::with('roles')
                ->paginate(10);

            $users
                ->getCollection()
                ->transform(
                    function ($user) {

                        $user->role_name =
                            $user->roles
                            ->pluck('name')
                            ->join(', ');

                        return $user;
                    }
                );

            return response()->json(
                $users
            );
        }
    }
    //lấy role
    public function roleList()
    {
        return Role::all();
    }
    //thêm user
    public function store(Request $request)
    {
        $user = User::create([

            'name' => $request->name,

            'username' => $request->username,

            'email' => $request->email,

            'phone' => $request->phone,

            'password' => bcrypt(
                $request->password
            ),
        ]);

        $user->syncRoles(
            $request->roles
        );

        return response()->json([
            'message' => 'Thành công'
        ]);
    }
    //sửa user
    public function update(
        Request $request,
        User $user
    ) {
        $user->update([

            'name' =>
            $request->name,

            'username' =>
            $request->username,

            'email' =>
            $request->email,

            'phone' =>
            $request->phone,

            'status' =>
            $request->status
        ]);
        $user->syncRoles([
            $request->role
        ]);
        return response()->json();
    }
    public function destroy(
        User $user
    ) {
        $user->delete();

        return response()->json();
    }
}
