<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Company;
use App\Models\Position;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class DepartmentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with(['company', 'creator'])->where('company_id', Auth::user()->company_id);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $departments = $query->latest()->paginate($request->input('per_page', 50));

        // Lấy danh sách công ty cho dropdown
        $companies = Company::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Main/Departments/Index', [
            'departments' => $departments,
            'companies' => $companies,
            'filters' => $request->only(['search', 'status', 'per_page'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Tên phòng ban là bắt buộc',
            'name.max' => 'Tên phòng ban không được vượt quá 255 ký tự',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $validated['creater_id'] = Auth::id();
        $validated['company_id'] = Auth::user()->company_id;
        Department::create($validated);

        return redirect()->back()
            ->with('success', 'Thêm phòng ban thành công!');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Tên phòng ban là bắt buộc',
            'name.max' => 'Tên phòng ban không được vượt quá 255 ký tự',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $department->update($validated);

        return redirect()->back()
            ->with('success', 'Cập nhật phòng ban thành công!');
    }
    public function detail($id)
    {
        $department = Department::with(['company', 'creator'])->findOrFail($id);
        if (!$department) {
            return redirect()->back()->with('error', 'Phòng ban không tồn tại!');
        }
        $positions = Position::where('department_id', $department->id)->with('creator')->get();

        $user = UserCompany::where('department_id', $department->id)
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 'active')->with(['user', 'position'])->get();
        $roles = Role::orderBy('name')->get(['id', 'name', 'description']);
        return Inertia::render('Main/Departments/Detail', [
            'title' => 'Chi tiết phòng ban - ' . $department->name,
            'department' => $department,
            'positions' => $positions,
            'users' => $user,
            'roles' => $roles,
        ]);
    }
}
