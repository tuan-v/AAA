<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // SEARCH
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        return $query
            ->orderByDesc('id')
            ->paginate(5);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|min:2|max:255|unique:categories,name',
                'code' => 'nullable|max:50|unique:categories,code',
                'description' => 'nullable|max:1000',
                'status' => 'required|in:active,inactive',
            ],
            [
                'name.required' => 'Vui lòng nhập tên danh mục',
                'name.min' => 'Tên danh mục phải từ 2 ký tự trở lên',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',

                'code.max' => 'Mã danh mục không được vượt quá 50 ký tự',
                'code.unique' => 'Mã danh mục đã tồn tại',

                'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            ]
        );

        return Category::create($validated);
    }

    public function show($id)
    {
        return Category::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate(
            [
                'name' => 'required|min:2|max:255|unique:categories,name,' . $id,
                'code' => 'nullable|max:50|unique:categories,code,' . $id,
                'description' => 'nullable|max:1000',
                'status' => 'required|in:active,inactive',
            ],
            [
                'name.required' => 'Vui lòng nhập tên danh mục',
                'name.min' => 'Tên danh mục phải từ 2 ký tự trở lên',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',

                'code.max' => 'Mã danh mục không được vượt quá 50 ký tự',
                'code.unique' => 'Mã danh mục đã tồn tại',

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
        $category = Category::findOrFail($id);

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
