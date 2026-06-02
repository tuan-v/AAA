<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Models\UserCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\Permission\Models\Role;



class UserController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $query = User::query()->where('is_employee', 1)
            ->with(['companies'])
            ->whereHas('companies', function ($q) use ($companyId) {
                $q->where('companies.id', $companyId);
            });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        if ($request->filled('company_id')) {
            $query->whereHas('companies', function ($q) use ($request) {
                $q->where('companies.id', $request->company_id);
            });
        }



        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $users = $query->latest()->paginate($perPage);

        $departments =[];

        // Lấy danh sách roles
        $roles = Role::orderBy('name')->get(['id', 'name', 'description']);

        // Tự động append departments_in_company và positions_in_company
        $users->getCollection()->each(function ($user) {
            $user->append(['department', 'position']);
            // Load roles của user
            $user->load('roles:id,name');
        });

        return Inertia::render('Main/User/Index', [
            'users' => $users,
            'departments' => $departments,
            'roles' => $roles,
            'filters' => $request->only(['search', 'status', 'per_page'])
        ]);
    }

    public function create(){
        return Inertia('createPro');
    }

    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive,pending,blocked'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ], [

            'name.required' => 'Vui lòng nhập họ tên.',
            'name.max' => 'Họ tên không được vượt quá 255 ký tự.',

            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã tồn tại.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất :min ký tự.',

            'password.mixed' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa và 1 chữ thường.',
            'password.letters' => 'Mật khẩu phải chứa ít nhất 1 ký tự chữ.',
            'password.numbers' => 'Mật khẩu phải chứa ít nhất 1 chữ số.',
            'password.symbols' => 'Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt.',
            'password.uncompromised' => 'Mật khẩu không an toàn, vui lòng chọn mật khẩu khác.',

            'company_id.exists' => 'Công ty không hợp lệ.',
            'department_id.exists' => 'Phòng ban không hợp lệ.',
            'position_id.exists' => 'Chức vụ không hợp lệ.',
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'position_id.required' => 'Vui lòng chọn chức vụ.',

            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',

            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'avatar.image' => 'Ảnh đại diện phải là định dạng hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận các định dạng: jpg, jpeg, png, gif.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        DB::beginTransaction();

        try {
            // Handle avatar upload
            $avatarPath = null;
            $thumbnailPath = null;

            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarPath = $avatar->store('avatars', 'public');

                // Generate thumbnail (optional - requires intervention/image package)
                $thumbnailPath = $this->generateThumbnail($avatar);
            }


            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['email'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'company_id' => Auth::user()->company_id,
                'department_id' => $validated['department_id'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'],
                'is_employee' => 1,
                'avatar' => $avatarPath,
                'thumbnail' => $thumbnailPath,
                'creater_id' => auth()->id(),
                'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            ]);

            UserCompany::createOrFirst([
                'user_id' => $user->id,
                'company_id' => Auth::user()->company_id,
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
            ]);
            // Gán vai trò cho user
            if (!empty($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Tạo người dùng thành công!');
        } catch (Exception $e) {
            DB::rollBack();
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }
            if ($thumbnailPath) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }


    public function update(Request $request, User $user)
    {
        // Validate (khác với store: password không bắt buộc, username/email/phone unique bỏ qua chính nó)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'phone' => ['required', 'string', 'max:20', "unique:users,phone,{$user->id}"],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive,pending,blocked'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã được sử dụng bởi tài khoản khác.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại đã được sử dụng bởi tài khoản khác.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.mixed' => 'Mật khẩu phải chứa cả chữ hoa và chữ thường.',
            'password.numbers' => 'Mật khẩu phải chứa ít nhất một chữ số.',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'avatar.image' => 'Ảnh đại diện phải là định dạng hình ảnh.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận các định dạng: jpg, jpeg, png, gif.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'department_id.exists' => 'Phòng ban không hợp lệ.',
            'position_id.required' => 'Vui lòng chọn chức vụ.',
            'position_id.exists' => 'Chức vụ không hợp lệ.',
        ]);

        DB::beginTransaction();

        try {
            $avatarPath = $user->avatar;
            $thumbnailPath = $user->thumbnail;

            // Xử lý ảnh mới nếu có
            if ($request->hasFile('avatar')) {
                // Xóa ảnh cũ
                if ($avatarPath) {
                    Storage::disk('public')->delete($avatarPath);
                }
                if ($thumbnailPath) {
                    Storage::disk('public')->delete($thumbnailPath);
                }

                $avatar = $request->file('avatar');
                $avatarPath = $avatar->store('avatars', 'public');
                $thumbnailPath = $this->generateThumbnail($avatar);
            }

            // Cập nhật thông tin cơ bản
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'],
                'avatar' => $avatarPath,
                'thumbnail' => $thumbnailPath,
            ];

            // Chỉ cập nhật password nếu có nhập
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);
            $userCompany = UserCompany::where('user_id', $user->id)
                ->where('company_id', Auth::user()->company_id)
                ->first();
            if ($userCompany) {

                if ($userCompany->department_id != $validated['department_id'] || $userCompany->position_id != $validated['position_id']) {

                    $userCompany->update([
                        'status' => 'inactive',
                    ]);
                    UserCompany::create([
                        'user_id' => $user->id,
                        'company_id' => Auth::user()->company_id,
                        'department_id' => $validated['department_id'],
                        'position_id' => $validated['position_id'],
                    ]);
                }
                // Cập nhật vai trò cho user
                if (isset($validated['roles'])) {
                    $user->syncRoles($validated['roles']);
                }

            } else {
                UserCompany::create([
                    'user_id' => $user->id,
                    'company_id' => Auth::user()->company_id,
                    'department_id' => $validated['department_id'],
                    'position_id' => $validated['position_id'],
                ]);
            }

            $service = app(\App\Services\NotificationService::class);
            $service->create(
                userId: Auth::user()->id,
                companyId: Auth::user()->company_id,
                title: 'Cập nhật thông tin nhân sự',
                message: 'Thông tin của bạn đã được cập nhật bởi ' . Auth::user()->name,
                data: [
                    'category' => 'user',
                    'user' => [
                        'name' => Auth::user()->name,
                        'avatar' => Auth::user()->thumbnail,
                    ],
                ],
                urlLink: '/profile',
                category: 'user'
            );

            DB::commit();

            return redirect()->back()->with('success', 'Cập nhật nhân sự thành công!');
        } catch (Exception $e) {
            DB::rollBack();

            // Xóa ảnh mới nếu lỗi
            if ($request->hasFile('avatar')) {
                if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                    Storage::disk('public')->delete($avatarPath);
                }
                if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Cập nhật thất bại: ' . $e->getMessage()]);
        }
    }


    /**
     * Get departments by company ID
     */
    public function getDepartmentsByCompany(Request $request, $companyId)
    {
        $departments = Department::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($departments);
    }


    /**
     * Generate thumbnail for avatar (optional)
     */
    private function generateThumbnail($file)
    {
        // Khởi tạo ImageManager đúng chuẩn v3
        $manager = new ImageManager(new Driver());

        // Lấy đường dẫn file tạm
        $path = $file->getRealPath();

        // Đọc file ảnh (v3 dùng read())
        $image = $manager->read($path);

        // Cắt vuông 200x200 (tương đương fit() của v2)
        $image->cover(200, 200);

        // Tên file sau khi lưu
        $thumbnailPath = 'avatars/thumbnails/' . time() . '_' . $file->getClientOriginalName();

        // Lưu vào storage
        Storage::disk('public')->put($thumbnailPath, (string) $image->encode());

        return $thumbnailPath;
    }

    public function changeCompany($id)
    {
        $user = Auth::user();
        // Kiểm tra user có liên kết với công ty này không
        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('company_id', $id)
            ->first();
        if (!$userCompany) {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập công ty này.');
        }
        $user->company_id = $userCompany->company_id;
        $user->department_id = $userCompany->department_id;
        $user->save();


        return redirect()->back()->with('success', 'Đã chuyển sang công ty: ' . $userCompany->name);
    }
}
