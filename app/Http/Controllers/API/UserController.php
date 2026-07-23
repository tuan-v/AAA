<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = User::query()
            ->with(['company:id,name', 'roles:id,name', 'departmentRecord:id,code,name', 'positionRecord:id,department_id,code,name'])
            ->visibleFor(auth()->user());              // giữ scope quyền xem

        // Lọc theo công ty
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
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
        $ownerId = Company::whereKey(auth()->user()->company_id)->value('owner_id');
        $users->getCollection()->transform(function ($user) use ($ownerId) {
            $user->is_company_owner = (int) $user->id === (int) $ownerId;

            return $user;
        });

        return response()->json($users);
    }

    public function role()
    {
        return Role::query()->visibleTo(auth()->user())->orderByDesc('hierarchy_level')->get();
    }

    public function show($id)
    {
        $user = User::query()
            ->visibleFor(auth()->user())
            ->with([
                'roles:id,name',
                'company:id,name,email,phone,address',
                'departmentRecord:id,code,name',
                'positionRecord:id,code,name',
                'creator:id,name',
            ])
            ->findOrFail($id);

        $activities = ActivityLog::query()
            ->forCompany((int) auth()->user()->company_id)
            ->where('user_id', $user->id)
            ->select([
                'id',
                'action',
                'description',
                'model_type',
                'model_id',
                'old_values',
                'new_values',
                'ip_address',
                'user_agent',
                'created_at',
            ])
            ->latest()
            ->limit(50)
            ->get();

        $recentSessions = $user->sessions()
            ->select(['id', 'ip_address', 'device_name', 'session_type', 'last_activity', 'login_at', 'logout_at'])
            ->orderByDesc('last_activity')
            ->limit(5)
            ->get();

        $ownerId = Company::whereKey(auth()->user()->company_id)->value('owner_id');

        return response()->json([
            ...$user->toArray(),
            'is_company_owner' => (int) $user->id === (int) $ownerId,
            'activities' => $activities,
            'recent_sessions' => $recentSessions,
            'activity_count' => ActivityLog::query()
                ->forCompany((int) auth()->user()->company_id)
                ->where('user_id', $user->id)
                ->count(),
        ]);
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
                'password' => 'required|string|min:6|confirmed',
                'status' => [
                    'required',
                    Rule::in([
                        User::STATUS_ACTIVE,
                        User::STATUS_INACTIVE,
                        User::STATUS_BLOCKED,
                        User::STATUS_PENDING,
                    ]),
                ],
                'role' => 'required|exists:roles,name',
                'department_id' => [
                    'required',
                    Rule::exists('departments', 'id')->where(
                        fn($query) => $query->where('company_id', auth()->user()->company_id)->where('status', 'active')
                    ),
                ],
                'position_id' => [
                    'required',
                    Rule::exists('positions', 'id')->where(fn($query) => $query
                        ->where('company_id', auth()->user()->company_id)
                        ->where('department_id', $request->department_id)
                        ->where('status', 'active')),
                ],
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
                'role.exists' => 'Vai trò không tồn tại',
                'department_id.required' => 'Vui lòng chọn phòng ban',
                'position_id.required' => 'Vui lòng chọn chức vụ',
            ]
        );

        // ❗ CHECK TRÙNG TRONG COMPANY (PHẢI ĐẶT TRƯỚC KHI CREATE)
        $exists = User::whereHas('companies', function ($q) {
            $q->where('companies.id', auth()->user()->company_id);
        })->where('email', $validated['email'])->exists();

        if ($exists) {
            return response()->json([
                'message' => 'User đã tồn tại trong công ty',
            ], 422);
        }

        $assignableRole = Role::query()
            ->visibleTo(auth()->user())
            ->where('name', $validated['role'])
            ->first();
        abort_unless($assignableRole, 403, 'Bạn không thể gán vai trò cao hơn vai trò của mình.');

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'status' => User::STATUS_ACTIVE,
            'mode' => 'company',
            'company_id' => auth()->user()->company_id,
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
        ]);

        $user->companies()->syncWithoutDetaching([
            auth()->user()->company_id,
        ]);

        $user->syncRoles([$assignableRole]);

        $this->notificationService->createForPermission(
            'nhan_su.xem',
            (int) auth()->user()->company_id,
            'Nhân sự mới được thêm',
            "Tài khoản {$user->name} vừa được thêm vào công ty.",
            ['user_id' => $user->id],
            '/manage/user',
            auth()->id(),
            'management'
        );

        return response()->json([
            'message' => 'Thêm tài khoản thành công',
            'user' => $user,
        ], 201);
    }

    // Cập nhật user
    public function update(Request $request, $id)
    {
        $user = User::visibleFor(auth()->user())
            ->findOrFail($id);
        $isCompanyOwner = (int) Company::whereKey(auth()->user()->company_id)->value('owner_id') === (int) $user->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'nullable',
                'regex:/^(0)[0-9]{9,10}$/',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'status' => [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_BLOCKED,
                    User::STATUS_PENDING,
                ]),
            ],
            'role' => 'required|exists:roles,name',
            'department_id' => [
                Rule::requiredIf(! $isCompanyOwner),
                'nullable',
                Rule::exists('departments', 'id')->where(
                    fn($query) => $query->where('company_id', auth()->user()->company_id)->where('status', 'active')
                ),
            ],
            'position_id' => [
                Rule::requiredIf(! $isCompanyOwner),
                'nullable',
                Rule::exists('positions', 'id')->where(fn($query) => $query
                    ->where('company_id', auth()->user()->company_id)
                    ->where('department_id', $request->department_id)
                    ->where('status', 'active')),
            ],
        ]);

        $assignableRole = Role::query()
            ->visibleTo(auth()->user())
            ->where('name', $validated['role'])
            ->first();
        abort_unless($assignableRole, 403, 'Bạn không thể gán vai trò cao hơn vai trò của mình.');

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'department_id' => $isCompanyOwner ? null : $validated['department_id'],
            'position_id' => $isCompanyOwner ? null : $validated['position_id'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }
        $user->update($data);

        // 🔥 đảm bảo vẫn thuộc company hiện tại
        $user->companies()->sync([auth()->user()->company_id]);

        $user->syncRoles([$assignableRole]);

        return response()->json([
            'message' => 'Cập nhật thành công',
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
                    User::STATUS_PENDING,
                ]),
            ],
        ]);

        $user->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
        ]);
    }

    public function makeSystem($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'type' => User::TYPE_SYSTEM,
        ]);

        return response()->json([
            'message' => 'Đã chuyển sang system',
        ]);
    }
}
