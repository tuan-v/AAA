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
            ->with(['company:id,name', 'roles:id,name,hierarchy_level', 'departmentRecord:id,code,name', 'positionRecord:id,department_id,code,name'])
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
        $actor = $request->user();
        $users->getCollection()->transform(function ($user) use ($ownerId, $actor) {
            $user->is_company_owner = (int) $user->id === (int) $ownerId;
            $user->can_be_managed = $actor->canManageUser($user);
            $user->can_resubmit = $user->canBeResubmitted()
                && $actor->canHandleEmployeeCorrection()
                && $actor->canManageUser($user);
            $user->can_edit_pending_edit = $user->canBeResubmitted()
                && $actor->canHandleEmployeeCorrection()
                && $actor->canManageUser($user);

            return $user;
        });

        return response()->json($users);
    }

    public function role(Request $request)
    {
        $currentUser = $request->user();
        $roles = Role::query()
            ->visibleTo($currentUser)
            ->where(function ($query) use ($currentUser) {
                $query->where('type', 'system')
                    ->orWhere(fn($query) => $query
                        ->where('type', 'user')
                        ->where('company_id', $currentUser->company_id));
            })
            ->when(! $currentUser->hasRole('Supper Admin'), fn($query) => $query->where('is_protected', false))
            ->orderByDesc('hierarchy_level')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'system' => $roles->where('type', 'system')->values(),
                'user' => $roles->where('type', 'user')->values(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = User::query()
            ->visibleFor(auth()->user())
            ->with([
                'roles:id,name,hierarchy_level',
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
            'can_be_managed' => $request->user()->canManageUser($user),
            'can_resubmit' => $user->canBeResubmitted()
                && $request->user()->canHandleEmployeeCorrection()
                && $request->user()->canManageUser($user),
            'can_edit_pending_edit' => $user->canBeResubmitted()
                && $request->user()->canHandleEmployeeCorrection()
                && $request->user()->canManageUser($user),
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

        $actor = $request->user();
        $companyOwnerId = Company::whereKey($actor->company_id)->value('owner_id');
        $requiresApproval = ! $actor->isSystem()
            && ! $actor->hasRole('Supper Admin')
            && ! $actor->hasRole('Giám đốc')
            && (int) $actor->id !== (int) $companyOwnerId;

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
            'status' => $requiresApproval ? User::STATUS_PENDING : User::STATUS_ACTIVE,
            'mode' => 'company',
            'company_id' => auth()->user()->company_id,
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
            'creater_id' => $actor->id,
        ]);

        $user->companies()->syncWithoutDetaching([
            auth()->user()->company_id,
        ]);

        $user->syncRoles([$assignableRole]);

        if ($requiresApproval) {
            $this->notificationService->createForHigherRoleUsers(
                $actor,
                (int) $actor->company_id,
                'Nhân sự mới chờ duyệt',
                "{$actor->name} đã thêm tài khoản {$user->name} và đang chờ duyệt.",
                ['user_id' => $user->id, 'status' => User::STATUS_PENDING],
                '/user',
                'management'
            );
        }

        return response()->json([
            'message' => $requiresApproval
                ? 'Đã tạo tài khoản và gửi yêu cầu chờ duyệt.'
                : 'Đã thêm và kích hoạt tài khoản thành công.',
            'requires_approval' => $requiresApproval,
            'user' => $user,
        ], 201);
    }

    // Cập nhật user
    public function update(Request $request, $id)
    {
        $user = User::visibleFor(auth()->user())
            ->findOrFail($id);
        abort_unless(
            ! in_array($user->status, [User::STATUS_REJECTED_FINAL, User::STATUS_EXPIRED], true),
            422,
            'Yêu cầu đã kết thúc và không thể chỉnh sửa.'
        );
        abort_unless(
            ! ($user->status === User::STATUS_PENDING && $user->last_resubmitted_at),
            422,
            'Yêu cầu đã được gửi duyệt lại và không thể chỉnh sửa trong lúc chờ phê duyệt.'
        );
        $this->authorizeUserManagement($request, $user);
        if ($user->status === User::STATUS_PENDING_EDIT) {
            abort_unless(
                $request->user()->canHandleEmployeeCorrection()
                    && $request->user()->canManageUser($user),
                403,
                'Chỉ Quản lý nhân sự được sửa yêu cầu này.'
            );
        }
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
                    User::STATUS_PENDING_EDIT,
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
            'status' => in_array($user->status, [User::STATUS_PENDING, User::STATUS_PENDING_EDIT], true)
                ? $user->status
                : $validated['status'],
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

        $this->authorizeUserManagement($request, $user);
        abort_unless(
            in_array($user->status, [User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_BLOCKED], true),
            422,
            'Tài khoản chưa được kích hoạt nên không thể khóa hoặc mở khóa.'
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

    public function approve(Request $request, User $user)
    {
        abort_unless($this->canReviewEmployee($request), 403, 'Chỉ Giám đốc mới có thể duyệt nhân sự.');
        abort_unless(
            $request->user()->isSystem()
                || $user->companies->contains($request->user()->company_id),
            403
        );
        $this->authorizeUserManagement($request, $user);
        abort_unless($user->status === User::STATUS_PENDING, 422, 'Tài khoản này không ở trạng thái chờ duyệt.');

        $user->update(['status' => User::STATUS_ACTIVE]);

        $notificationRecipients = collect([
            $user->creater_id,
            $user->last_resubmitted_by,
        ])->filter()
            ->map(fn($id) => (int) $id)
            ->reject(fn($id) => $id === (int) $request->user()->id)
            ->unique();

        foreach ($notificationRecipients as $recipientId) {
            $this->notificationService->create(
                $recipientId,
                (int) $request->user()->company_id,
                'Yêu cầu thêm nhân sự đã được duyệt',
                "Tài khoản {$user->name} đã được {$request->user()->name} duyệt và kích hoạt.",
                [
                    'user_id' => $user->id,
                    'status' => $user->status,
                    'event_type' => 'employee_approved',
                    'toast_type' => 'success',
                ],
                '/user',
                category: 'management'
            );
        }

        return response()->json([
            'message' => 'Duyệt tài khoản thành công.',
            'data' => ['id' => $user->id, 'status' => $user->status],
        ]);
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($this->canReviewEmployee($request), 403, 'Chỉ Giám đốc mới có thể từ chối nhân sự.');
        abort_unless(
            $request->user()->isSystem()
                || $user->companies->contains($request->user()->company_id),
            403
        );
        $this->authorizeUserManagement($request, $user);
        abort_unless($user->status === User::STATUS_PENDING, 422, 'Tài khoản này không ở trạng thái chờ duyệt.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'rejection_type' => ['required', Rule::in(['reject_and_return', 'reject_final'])],
        ], [
            'reason.required' => 'Vui lòng nhập lý do từ chối.',
            'reason.min' => 'Lý do từ chối phải có ít nhất 5 ký tự.',
            'reason.max' => 'Lý do từ chối không được vượt quá 1.000 ký tự.',
        ]);

        $rejectionCount = $user->rejection_count + 1;
        $isFinal = $validated['rejection_type'] === 'reject_final'
            || $rejectionCount >= User::MAX_REJECTION_COUNT;
        $nextStatus = $isFinal ? User::STATUS_REJECTED_FINAL : User::STATUS_PENDING_EDIT;

        $user->update([
            'status' => $nextStatus,
            'rejection_reason' => $validated['reason'],
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'rejection_count' => $rejectionCount,
            'rejection_type' => $isFinal ? 'reject_final' : 'reject_and_return',
            'resubmit_expires_at' => $isFinal ? null : now()->addDays(User::RESUBMIT_EXPIRY_DAYS),
        ]);

        $notificationRecipients = collect([
            $user->creater_id,
            $user->last_resubmitted_by,
        ])->filter()
            ->map(fn($id) => (int) $id)
            ->reject(fn($id) => $id === (int) $request->user()->id)
            ->unique();

        foreach ($notificationRecipients as $recipientId) {
            $this->notificationService->create(
                $recipientId,
                (int) $request->user()->company_id,
                'Yêu cầu thêm nhân sự bị từ chối',
                $isFinal
                    ? "Tài khoản {$user->name} đã bị từ chối dứt điểm: {$validated['reason']}"
                    : "Tài khoản {$user->name} cần chỉnh sửa (lần {$rejectionCount}/3): {$validated['reason']}",
                [
                    'user_id' => $user->id,
                    'status' => $nextStatus,
                    'reason' => $validated['reason'],
                    'event_type' => 'employee_rejected',
                    'toast_type' => 'error',
                ],
                '/user',
                category: 'management'
            );
        }

        return response()->json([
            'message' => $isFinal
                ? 'Đã từ chối dứt điểm yêu cầu.'
                : 'Đã trả yêu cầu về để chỉnh sửa.',
            'data' => ['id' => $user->id, 'status' => $user->status],
        ]);
    }

    public function resubmit(Request $request, User $user)
    {
        abort_unless(
            $user->companies->contains($request->user()->company_id)
                && $request->user()->canHandleEmployeeCorrection()
                && $request->user()->canManageUser($user),
            403,
            'Chỉ Quản lý nhân sự mới có thể gửi duyệt lại.'
        );
        abort_unless($user->status === User::STATUS_PENDING_EDIT, 422, 'Tài khoản này không ở trạng thái chờ chỉnh sửa.');
        if ($user->resubmit_expires_at?->isPast()) {
            $user->update(['status' => User::STATUS_EXPIRED]);
            abort(422, 'Yêu cầu đã hết hạn gửi lại.');
        }
        abort_unless($user->canBeResubmitted(), 422, 'Yêu cầu không còn được phép gửi duyệt lại.');

        $user->update([
            'status' => User::STATUS_PENDING,
            'rejection_reason' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_type' => null,
            'resubmit_expires_at' => null,
            'last_resubmitted_by' => $request->user()->id,
            'last_resubmitted_at' => now(),
        ]);

        $this->notificationService->createForHigherRoleUsers(
            $request->user(),
            (int) $request->user()->company_id,
            'Nhân sự đã được gửi duyệt lại',
            "{$request->user()->name} đã chỉnh sửa và gửi lại tài khoản {$user->name}.",
            ['user_id' => $user->id, 'status' => User::STATUS_PENDING],
            '/user',
            'management'
        );

        return response()->json([
            'message' => 'Đã gửi tài khoản duyệt lại.',
            'data' => ['id' => $user->id, 'status' => $user->status],
        ]);
    }

    private function authorizeUserManagement(Request $request, User $target): void
    {
        abort_unless(
            $request->user()->canManageUser($target),
            403,
            'Bạn không thể chỉnh sửa tài khoản có vai trò cao hơn mình.'
        );
    }

    private function canReviewEmployee(Request $request): bool
    {
        $actor = $request->user();
        $companyOwnerId = Company::whereKey($actor->company_id)->value('owner_id');

        return $actor->isSystem()
            || $actor->hasAnyRole(['Supper Admin', 'Giám đốc'])
            || (int) $actor->id === (int) $companyOwnerId;
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
