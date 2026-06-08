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
        $users = User::with('roles')
            ->visibleFor(auth()->user())
            ->orderBy('id', 'desc')
            ->paginate(5);

        return response()->json($users);
    }
    public function role()
    {
        return Role::all();
    }

    public function show($id)
    {
        $user = User::with('roles')
            ->visibleFor(auth()->user())
            ->findOrFail($id);

        return response()->json($user);
    }

    // Thêm user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|regex:/^(0)[0-9]{9,10}$/',
            'password' => 'required|string',
            'status' => [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_BLOCKED,
                    User::STATUS_PENDING,
                ])
            ],
            'role' => 'required|exists:roles,name'
        ]);

        // ❗ CHECK TRÙNG TRONG COMPANY (PHẢI ĐẶT TRƯỚC KHI CREATE)
        $exists = User::whereHas('companies', function ($q) {
            $q->where('companies.id', auth()->user()->company_id);
        })->where('email', $validated['email'])->exists();

        if ($exists) {
            return response()->json([
                'message' => 'User đã tồn tại trong công ty'
            ], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'status' => User::STATUS_ACTIVE,
            'mode' => 'company',
            'company_id' => auth()->user()->company_id,
        ]);

        $user->companies()->syncWithoutDetaching([
            auth()->user()->company_id
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
        $user = User::visibleFor(auth()->user())
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                Rule::unique('users', 'username')->ignore($user->id)
            ],
            'email' => [
                'required',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'phone' => [
                'nullable',
                'regex:/^(0)[0-9]{9,10}$/',
                Rule::unique('users', 'phone')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'status' => [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_BLOCKED,
                    User::STATUS_PENDING,
                ])
            ],
            'role' => 'required|exists:roles,name'
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

        // 🔥 đảm bảo vẫn thuộc company hiện tại
        $user->companies()->sync([auth()->user()->company_id]);

        $user->syncRoles($validated['role']);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }
    public function toggleStatus(Request $request, User $user)
    {
        // ❗ chặn user khác công ty
        abort_unless(
            auth()->user()->type === User::TYPE_SYSTEM
                || $user->companies->contains(auth()->user()->company_id),
            403
        );

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
    public function makeSystem($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'type' => User::TYPE_SYSTEM
        ]);

        return response()->json([
            'message' => 'Đã chuyển sang system'
        ]);
    }
};
