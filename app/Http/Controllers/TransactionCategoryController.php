<?php

namespace App\Http\Controllers;

use App\Models\TransactionCategory;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionCategoryController extends Controller
{
    // 📌 LIST
    public function index(Request $request)
    {
        $query = TransactionCategory::query();

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->keyword) {
            $query->where('name', 'like', "%{$request->keyword}%");
        }

        return $query->latest()->paginate(20);
    }

    // 📌 STORE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:income,expense,transfer',
        ]);

        return TransactionCategory::create([
            'company_id' => auth()->user()->company_id,
            'code' => $this->generateCode(),
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => true,
        ]);
    }

    // 📌 SHOW
    public function show($id)
    {
        return TransactionCategory::findOrFail($id);
    }

    // 📌 UPDATE
    public function update(Request $request, $id)
    {
        $category = TransactionCategory::findOrFail($id);

        $category->update($request->only([
            'name',
            'type',
            'description',
            'is_active'
        ]));

        return $category;
    }

    // 📌 DELETE (THEO NGHIỆP VỤ)
    public function destroy($id)
    {
        $category = TransactionCategory::findOrFail($id);

        $used = Transaction::where('category_id', $id)->exists();

        if ($used) {
            return response()->json([
                'message' => 'Không thể xóa vì đã có giao dịch sử dụng'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Deleted'
        ]);
    }

    // 📌 TOGGLE STATUS
    public function toggleStatus($id)
    {
        $category = TransactionCategory::findOrFail($id);

        $category->is_active = !$category->is_active;
        $category->save();

        return $category;
    }

    // helper
    private function generateCode()
    {
        return 'TC' . now()->format('YmdHis');
    }
}
