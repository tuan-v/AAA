<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    private function companyId(): int
    {
        $companyId = auth()->user()->company_id
            ?? auth()->user()->companies()->value('companies.id');
        abort_unless($companyId, 403, 'Tài khoản chưa thuộc công ty nào.');
        return (int) $companyId;
    }

    public function index(Request $request)
    {
        $query = Unit::where('company_id', $this->companyId())
            ->withExists('products as is_used');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }
        return $query
            ->orderByDesc('id')
            ->paginate(min((int) $request->input('per_page', 10), 100));
    }

    public function select(Request $request)
    {
        $query = Unit::where('company_id', $this->companyId());

        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId();

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('units', 'name')->where(
                    fn ($query) => $query->where('company_id', $companyId)
                ),
            ],
            'symbol' => [
                'required',
                'max:50',
                Rule::unique('units', 'symbol')->where(
                    fn ($query) => $query->where('company_id', $companyId)
                ),
            ],
            'status' => 'required|in:active,inactive',
            'allow_decimal' => 'required|boolean',
        ], [
            'name.required' => 'Vui lòng nhập tên đơn vị',
            'name.unique' => 'Tên đơn vị đã tồn tại trong công ty',
            'symbol.required' => 'Vui lòng nhập ký hiệu',
            'symbol.unique' => 'Ký hiệu đơn vị đã tồn tại trong công ty',
        ]);

        $validated['company_id'] = $companyId;

        return Unit::create($validated);
    }

    public function show($id)
    {
        return Unit::where('company_id', $this->companyId())
            ->withExists('products as is_used')
            ->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $companyId = $this->companyId();

        $unit = Unit::where('company_id', $companyId)
            ->findOrFail($id);

        if ($unit->products()->exists()) {
            return response()->json([
                'message' => 'Đơn vị tính đã được sử dụng, không thể chỉnh sửa. Bạn chỉ có thể khóa hoặc mở khóa.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('units', 'name')
                    ->ignore($unit->id)
                    ->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'symbol' => [
                'required',
                'max:50',
                Rule::unique('units', 'symbol')
                    ->ignore($unit->id)
                    ->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'allow_decimal' => 'required|boolean',
        ], [
            'name.required' => 'Vui lòng nhập tên đơn vị',
            'name.unique' => 'Tên đơn vị đã tồn tại trong công ty',
            'symbol.required' => 'Vui lòng nhập ký hiệu',
            'symbol.unique' => 'Ký hiệu đơn vị đã tồn tại trong công ty',
        ]);

        $unit->update(array_merge($validated, [
            'status' => $request->input('status', $unit->status),
        ]));

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function destroy($id)
    {
        $unit = Unit::where('company_id', $this->companyId())->findOrFail($id);

        if ($unit->products()->exists()) {
            return response()->json([
                'message' => 'Đơn vị tính đã được sử dụng, không thể xóa. Bạn có thể chuyển sang trạng thái khóa.',
            ], 422);
        }

        $unit->delete();

        return response()->json(['message' => 'Xóa đơn vị tính thành công.']);
    }

    public function toggleStatus($id)
    {
        $unit = Unit::where('company_id', $this->companyId())
            ->findOrFail($id);

        if ($unit->products()->exists()) {
            return response()->json([
                'message' => 'Đơn vị tính đã được sử dụng nên không thể thay đổi trạng thái.',
            ], 422);
        }

        $unit->status =
            $unit->status === 'active'
            ? 'inactive'
            : 'active';

        $unit->save();

        return response()->json([
            'status' => $unit->status
        ]);
    }
}
