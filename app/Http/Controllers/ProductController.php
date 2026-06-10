<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Danh sách
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit']);

        // FILTER TỒN KHO
        if ($request->stock === 'in_stock') {
            $query->where('quantity', '>', 0);
        }

        if ($request->stock === 'out_stock') {
            $query->where('quantity', '=', 0);
        }

        // =========================
        // SEARCH (TÊN + DANH MỤC)
        // =========================
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate(5)
            ->through(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,

                    'category_id' => $p->category_id,
                    'category_name' => $p->category?->name,

                    'unit_id' => $p->unit_id,
                    'unit_name' => $p->unit?->name,

                    'quantity' => $p->quantity,
                    'purchase_price' => $p->purchase_price,
                    'sell_price' => $p->sell_price,

                    'image' => $p->image
                        ? asset('storage/' . $p->image)
                        : null,

                    'description' => $p->description,
                    'status' => $p->status,
                ];
            });

        return response()->json($products);
    }

    // Thêm sản phẩm
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'sku' => 'nullable|max:255|unique:products,sku',

            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',

            'type' => 'required|in:hang_hoa,vat_tu,dich_vu',

            'purchase_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',

            'quantity' => 'required|integer|min:0',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // upload ảnh
        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Thêm sản phẩm thành công',
            'data' => $product
        ]);
    }

    // Chi tiết
    public function show($id)
    {
        return response()->json(
            Product::with([
                'category',
                'unit'
            ])->findOrFail($id)
        );
    }

    // Cập nhật
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',

            'sku' => 'nullable|max:255|unique:products,sku,' . $id,

            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',

            'type' => 'required|in:hang_hoa,vat_tu,dich_vu',

            'purchase_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',

            'quantity' => 'required|integer|min:0',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {

            // xóa ảnh cũ
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }
    // Đổi trạng thái
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);

        $product->status =
            $product->status === 'active'
            ? 'inactive'
            : 'active';

        $product->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công',
            'status' => $product->status
        ]);
    }
}
