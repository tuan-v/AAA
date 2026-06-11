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

        $lastCategory = Category::latest('id')->first();

        $nextNumber = $lastCategory
            ? $lastCategory->id + 1
            : 1;

        $validated['code'] =
            'DM' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $lastCode = Category::max('code');

        if (!$lastCode) {
            $validated['code'] = 'DM0001';
        } else {
            $number = (int) str_replace('DM', '', $lastCode);

            $validated['code'] =
                'DM' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
        }
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
