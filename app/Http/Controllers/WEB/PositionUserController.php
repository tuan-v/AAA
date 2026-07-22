<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PositionUserController extends Controller
{
    //

    public function index(Request $request)
    {

        $postion = Position::with(['department']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $postion->where(function ($myQuery) use ($search) {
                $myQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('department_id')) {
            $postion->where('department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $postion->where('status', $request->status);
        }

        $postion->orderBy('id', 'desc');



        $postions = $postion->paginate($request->get('per_page', 1));

        $departments = Department::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);



        return Inertia::render('Main/Position/Index', [
            'positions' => $postions,
            'departments' => $departments,
            'filters' => $request->only(['search', 'status', 'per_page'])
        ]);
    }

    /**
     * Store a newly created position
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions')->where('company_id', Auth::user()->company_id),
            ],
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive'
        ], [
            'name.required' => 'Tên vị trí là bắt buộc',
            'name.unique' => 'Tên vị trí đã tồn tại trong phòng ban này',
            'name.max' => 'Tên vị trí không được vượt quá 255 ký tự',
            'department_id.required' => 'Vui lòng chọn phòng ban',
            'department_id.exists' => 'Phòng ban không tồn tại',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ'
        ]);

        try {
            DB::beginTransaction();

            // Check if department is active
            $department = Department::find($validated['department_id']);
            if ($department->status !== 'active') {
                return back()->withErrors([
                    'department_id' => 'Phòng ban đã ngừng hoạt động, không thể thêm vị trí mới'
                ]);
            }

            $position = Position::create([
                'name' => $validated['name'],
                'department_id' => $validated['department_id'],
                'creater_id' => Auth::id(),
                'status' => $validated['status']
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Thêm vị trí thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Update the specified position
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions')->where('company_id', Auth::user()->company_id)
                    ->ignore($position->id),
            ],
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive'
        ], [
            'name.required' => 'Tên vị trí là bắt buộc',
            'name.unique' => 'Tên vị trí đã tồn tại trong phòng ban này',
            'name.max' => 'Tên vị trí không được vượt quá 255 ký tự',
            'department_id.required' => 'Vui lòng chọn phòng ban',
            'department_id.exists' => 'Phòng ban không tồn tại',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ'
        ]);

        try {
            DB::beginTransaction();

            // Check if department is active when changing department
            if ($position->department_id != $validated['department_id']) {
                $department = Department::find($validated['department_id']);
                if ($department->status !== 'active') {
                    return back()->withErrors([
                        'department_id' => 'Phòng ban đã ngừng hoạt động'
                    ]);
                }
            }

            $position->update([
                'name' => $validated['name'],
                'department_id' => $validated['department_id'],
                'status' => $validated['status']
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Cập nhật vị trí thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Get positions by department (for AJAX/API calls)
     */
    public function getByDepartment(Request $request, $departmentId)
    {
        $positions = Position::where('department_id', $departmentId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $positions
        ]);
    }
}
