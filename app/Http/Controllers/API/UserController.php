<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('roles:id,name')                    // load vai trò
            ->visibleFor(auth()->user());              // giữ scope quyền xem

        // Lọc theo công ty
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Tìm kiếm chung
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo vai trò
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $perPage = min((int) $request->get('per_page', 10), 100);

        $users = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($users);
    }
    public function role()
    {
        return Role::all();
    }

    public function show($id)
    {
        $user = User::with([
            'roles',
            'logs.user'
        ])->findOrFail($id);

        return response()->json($user);
    }

    // Thêm user
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:50|unique:users,username',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|regex:/^(0)[0-9]{9,10}$/',
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
            ],
            [
                'name.required' => 'Vui lòng nhập tên',
                'username.required' => 'Vui lòng nhập tên đăng nhập',
                'username.unique' => 'Username đã tồn tại',
                'email.required' => 'Vui lòng nhập email',
                'email.unique' => 'Email đã tồn tại',
                'phone.required' => 'Vui lòng nhập số điện thoại',
                'phone.regex' => 'Số điện thoại không hợp lệ',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp',
                'password.required' => 'Vui lòng nhập mật khẩu',
                'role.required' => 'Vui lòng chọn vai trò',
                'role.exists' => 'Vai trò không tồn tại'
            ]
        );

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
