<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::with('roles')
            ->orderBy('id', 'desc')
            ->paginate(5);
    }

    public function role()
    {
        return Role::all();
    }

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return response()->json($user);
    }

    // Thêm user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'username' => [
                'required',
                'string',
                'max:50',
                'unique:users,username'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email'
            ],

            'phone' => [
                'nullable',
                'number',
                'max:10',
                'unique:users,phone'
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed'
            ],
            'status' => [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_BLOCKED,
                    User::STATUS_PENDING,
                ])
            ],

            'role' => [
                'required',
                'exists:roles,name'
            ]
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'status' => User::STATUS_ACTIVE
        ]);

        $user->syncRoles($validated['role']);

        return response()->json([
            'message' => 'Thêm tài khoản thành công',
            'user' => $user
        ], 201);
    }

    // Cập nhật user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id)
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id)
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id)
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed'
            ],

            'status' => [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_BLOCKED,
                    User::STATUS_PENDING,
                ])
            ],

            'role' => [
                'required',
                'exists:roles,name'
            ]
        ]);

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status']
        ];

        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $user->update($data);

        $user->syncRoles($validated['role']);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function changeStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_BLOCKED,
                    User::STATUS_PENDING
                ])
            ]
        ]);

        $user->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công'
        ]);
    }
}
