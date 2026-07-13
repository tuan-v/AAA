<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $company = auth()->user()->companies()->first();

        $query = Category::where('company_id', $company->id);

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
        $validated = $request->validate(
            [
                'name' => 'required|min:2|max:255|unique:categories,name',
                'description' => 'nullable|max:1000',
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
        $company = auth()->user()->companies()->first();

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
            $query->orderBy('id')->get()
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

        $validated = $request->validate(
            [
                'name' => 'required|min:2|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|max:1000',
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

        $category->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
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
