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

        $query = Category::with('parent:id,name')
            ->withExists(['products as has_products', 'children as has_children'])
            ->where('company_id', $company->id);

        // SEARCH
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query
            ->orderByDesc('id')
            ->paginate(min((int) $request->input('per_page', 10), 100));

        $categories->getCollection()->transform(function (Category $category) {
            $category->is_used = (bool) ($category->has_products || $category->has_children);
            unset($category->has_products, $category->has_children);
            return $category;
        });

        return $categories;
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

        if (!empty($validated['parent_id'])) {
            $parent = Category::where('company_id', $company->id)->findOrFail($validated['parent_id']);
            if ($parent->products()->exists()) {
                return response()->json([
                    'message' => 'Không thể tạo danh mục con vì danh mục cha đã chứa sản phẩm.',
                    'errors' => ['parent_id' => ['Danh mục đã chứa sản phẩm không thể trở thành danh mục cha.']],
                ], 422);
            }
        }

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

        $categories = $query
            ->withExists(['products as has_products', 'children as has_children'])
            ->orderBy('name')
            ->get();
        $byId = $categories->keyBy('id');

        $categories->each(function (Category $category) use ($byId) {
            $names = [$category->name];
            $parentId = $category->parent_id;
            $visited = [$category->id];

            while ($parentId && $byId->has($parentId) && !in_array($parentId, $visited, true)) {
                $parent = $byId->get($parentId);
                array_unshift($names, $parent->name);
                $visited[] = $parentId;
                $parentId = $parent->parent_id;
            }

            $category->depth = count($names) - 1;
            $category->full_path = implode(' / ', $names);
            $category->is_leaf = ! (bool) $category->has_children;
            $category->is_used = (bool) ($category->has_products || $category->has_children);
        });

        return response()->json($categories->sortBy('full_path')->values());
    }
    public function show($id)
    {
        $company = auth()->user()->companies()->first();

        $category = Category::where('company_id', $company->id)
            ->withExists(['products as has_products', 'children as has_children'])
            ->findOrFail($id);

        $category->is_used = (bool) ($category->has_products || $category->has_children);
        unset($category->has_products, $category->has_children);

        return $category;
    }

    public function update(Request $request, $id)
    {
        $company = auth()->user()->companies()->first();

        $category = Category::where('company_id', $company->id)
            ->findOrFail($id);

        if ($this->isUsed($category)) {
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

        if ($this->isUsed($category)) {
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

        if ($this->isUsed($category)) {
            return response()->json([
                'message' => 'Danh mục đã được sử dụng nên không thể thay đổi trạng thái.',
            ], 422);
        }

        $category->status =
            $category->status === 'active'
            ? 'inactive'
            : 'active';

        $category->save();

        return response()->json([
            'status' => $category->status
        ]);
    }

    private function isUsed(Category $category): bool
    {
        return $category->products()->exists() || $category->children()->exists();
    }
}
