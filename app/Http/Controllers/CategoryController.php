<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $company = auth()->user()->companies()->first();

        $query = Category::with('parent:id,name')->where('company_id', $company->id);

        // SEARCH
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return $query
            ->orderByDesc('id')
            ->paginate(min((int) $request->input('per_page', 10), 100));
    }

    public function store(Request $request)
    {
        $company = auth()->user()->companies()->first();

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'min:2',
                    'max:255',
                    Rule::unique('categories', 'name')->where(
                        fn ($query) => $query->where('company_id', $company->id)
                    ),
                ],
                'description' => 'nullable|max:1000',
                'parent_id' => ['nullable', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('company_id', $company->id))],
                'status' => 'required|in:active,inactive',
            ],
            [
                'name.required' => 'Vui lòng nhập tên danh mục',
                'name.min' => 'Tên danh mục phải từ 2 ký tự trở lên',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',

                'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            ]
        );

        $validated['company_id'] = $company->id;
        return Category::create($validated);
        // return response()->json([
        //     'message' => 'Thêm danh mục thành công',
        //     'data' => $category,
        // ]);
    }
    public function select(Request $request)
    {
        $company = auth()->user()->companies()->first();

        $query = Category::where('company_id', $company->id);

        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }

        return response()->json(
            $query->with('parent:id,name')->orderBy('name')->get()
        );
    }
    public function show($id)
    {
        $company = auth()->user()->companies()->first();

        return Category::where('company_id', $company->id)
            ->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $company = auth()->user()->companies()->first();

        $category = Category::where('company_id', $company->id)
            ->findOrFail($id);

        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Danh mục đã được sử dụng, không thể chỉnh sửa. Bạn chỉ có thể khóa hoặc mở khóa.',
            ], 422);
        }

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'min:2',
                    'max:255',
                    Rule::unique('categories', 'name')
                        ->ignore($category->id)
                        ->where(fn ($query) => $query->where('company_id', $company->id)),
                ],
                'description' => 'nullable|max:1000',
                'parent_id' => [
                    'nullable',
                    Rule::notIn([$category->id]),
                    Rule::exists('categories', 'id')->where(fn ($query) => $query->where('company_id', $company->id)),
                ],
                'status' => 'required|in:active,inactive',
            ],
            [
                'name.required' => 'Vui lòng nhập tên danh mục',
                'name.min' => 'Tên danh mục phải từ 2 ký tự trở lên',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',

                'description.max' => 'Mô tả không được vượt quá 1000 ký tự',

            ]
        );

        if (!empty($validated['parent_id']) && in_array((int) $validated['parent_id'], $this->descendantIds($category), true)) {
            return response()->json([
                'message' => 'Không thể chọn danh mục con làm danh mục cha.',
                'errors' => ['parent_id' => ['Danh mục cha không hợp lệ.']],
            ], 422);
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }
    private function descendantIds(Category $category): array
    {
        $ids = [];
        $queue = [$category->id];
        while ($queue) {
            $children = Category::where('company_id', $category->company_id)
                ->whereIn('parent_id', $queue)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $ids = array_merge($ids, $children);
            $queue = $children;
        }
        return $ids;
    }

    public function destroy($id)
    {
        $company = auth()->user()->companies()->firstOrFail();
        $category = Category::where('company_id', $company->id)->findOrFail($id);

        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Danh mục đã được sử dụng, không thể xóa. Bạn có thể chuyển sang trạng thái khóa.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Xóa danh mục thành công.']);
    }
    public function toggleStatus($id)
    {
        $company = auth()->user()->companies()->first();

        $category = Category::where('company_id', $company->id)
            ->findOrFail($id);

        $category->status =
            $category->status === 'active'
            ? 'inactive'
            : 'active';

        $category->save();

        return response()->json([
            'status' => $category->status
        ]);
    }
}
