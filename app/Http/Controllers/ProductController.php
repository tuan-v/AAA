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
        $companyCurrency = auth()
            ->user()
            ->company
            ->currencies()
            ->first();

        $query = Product::with([
            'category',
            'unit',
            'stocks',
            'stocks.warehouse'
        ]);
        $query = Product::query()
            ->select('products.*')
            ->leftJoin('warehouse_product_stocks as wps', 'products.id', '=', 'wps.product_id')
            ->selectRaw('COALESCE(SUM(wps.quantity), 0) as total_quantity')
            ->groupBy('products.id');
        if ($request->stock === 'in_stock') {
            // còn hàng (> 10)
            $query->havingRaw('COALESCE(SUM(wps.quantity),0) > 10');
        }

        if ($request->stock === 'low_stock') {
            // sắp hết (1 - 10)
            $query->havingRaw('COALESCE(SUM(wps.quantity),0) BETWEEN 1 AND 10');
        }

        if ($request->stock === 'out_stock') {
            // hết hàng (= 0)
            $query->havingRaw('COALESCE(SUM(wps.quantity),0) = 0');
        }
        // if ($request->stock === 'in_stock') {
        //     $query->where('quantity', '>', 0);
        // }

        // if ($request->stock === 'out_stock') {
        //     $query->where('quantity', '=', 0);
        // }

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('warehouse_id')) {
            $query->whereHas('stocks', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate(5)
            ->through(function ($p) use ($companyCurrency) {

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,

                    'category_id' => $p->category_id,
                    'category_name' => $p->category?->name,

                    'unit_id' => $p->unit_id,
                    'unit_name' => $p->unit?->name,

                    'quantity' => $p->stocks->sum('quantity'),
                    'warehouse' => $p->stocks->map(function ($s) {
                        return [
                            'warehouse_name' => $s->warehouse?->name,
                            'quantity' => $s->quantity,
                        ];
                    }),
                    'purchase_price' => $p->purchase_price,
                    'sell_price' => $p->sell_price,

                    'currency_symbol' => $companyCurrency?->symbol,

                    'image' => $p->image
                        ? asset('storage/' . $p->image)
                        : null,

                    'description' => $p->description,
                    'status' => $p->status,
                ];
            });

        return response()->json($products);
    }
    // API trả về tất cả sản phẩm cho dropdown (không phân trang)
    public function forSelect()
    {
        $products = Product::with('stocks')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'sale_price' => $p->sell_price,
                    'stock_quantity' => $p->stocks->sum('quantity'),
                ];
            });

        return response()->json($products);
    }

    // Thêm sản phẩm
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|max:255',
                'sku' => 'nullable|max:255|unique:products,sku',

                'category_id' => 'required|exists:categories,id',
                'unit_id' => 'required|exists:units,id',

                'type' => 'required|in:hang_hoa,vat_tu,dich_vu',

                'purchase_price' => 'numeric|min:0',
                'sell_price' => 'numeric|min:0',

                'quantity' => 'required|integer|min:0',

                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ],
            [
                'name.required' => 'Tên sản phẩm không được để trống.',
                'name.max' => 'Tên sản phẩm tối đa 255 ký tự.',

                'sku.unique' => 'Mã SKU đã tồn tại.',
                'sku.max' => 'SKU tối đa 255 ký tự.',

                'category_id.required' => 'Vui lòng chọn danh mục.',
                'category_id.exists' => 'Danh mục không tồn tại.',

                'unit_id.required' => 'Vui lòng chọn đơn vị tính.',
                'unit_id.exists' => 'Đơn vị tính không tồn tại.',

                'type.required' => 'Vui lòng chọn loại sản phẩm.',
                'type.in' => 'Loại sản phẩm không hợp lệ.',

                'purchase_price.numeric' => 'Giá nhập phải là số.',
                'purchase_price.min' => 'Giá nhập không được nhỏ hơn 0.',

                'sell_price.numeric' => 'Giá bán phải là số.',
                'sell_price.min' => 'Giá bán không được nhỏ hơn 0.',

                'quantity.required' => 'Vui lòng nhập số lượng tồn.',
                'quantity.integer' => 'Số lượng phải là số nguyên.',
                'quantity.min' => 'Số lượng tồn không được nhỏ hơn 0.',

                'image.image' => 'File tải lên phải là hình ảnh.',
                'image.mimes' => 'Ảnh chỉ được phép có định dạng jpg, jpeg, png hoặc webp.',
                'image.max' => 'Dung lượng ảnh tối đa 2MB.',

                'description.string' => 'Mô tả không hợp lệ.',

                'status.required' => 'Vui lòng chọn trạng thái.',
                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

        // upload ảnh
        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        $product = Product::create($validated);

        return response()->json(
            [
                'message' => 'Thêm sản phẩm thành công',
                'data' => $product
            ]
        );
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

        $validated = $request->validate(
            [
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
            ],
            [
                'name.required' => 'Tên sản phẩm không được để trống.',
                'name.max' => 'Tên sản phẩm tối đa 255 ký tự.',

                'sku.unique' => 'Mã SKU đã tồn tại.',
                'sku.max' => 'SKU tối đa 255 ký tự.',

                'category_id.required' => 'Vui lòng chọn danh mục.',
                'category_id.exists' => 'Danh mục không tồn tại.',

                'unit_id.required' => 'Vui lòng chọn đơn vị tính.',
                'unit_id.exists' => 'Đơn vị tính không tồn tại.',

                'type.required' => 'Vui lòng chọn loại sản phẩm.',
                'type.in' => 'Loại sản phẩm không hợp lệ.',

                'purchase_price.required' => 'Vui lòng nhập giá nhập.',
                'purchase_price.numeric' => 'Giá nhập phải là số.',
                'purchase_price.min' => 'Giá nhập không được nhỏ hơn 0.',

                'sell_price.required' => 'Vui lòng nhập giá bán.',
                'sell_price.numeric' => 'Giá bán phải là số.',
                'sell_price.min' => 'Giá bán không được nhỏ hơn 0.',

                'quantity.required' => 'Vui lòng nhập số lượng tồn.',
                'quantity.integer' => 'Số lượng phải là số nguyên.',
                'quantity.min' => 'Số lượng tồn không được nhỏ hơn 1.',

                'image.image' => 'File tải lên phải là hình ảnh.',
                'image.mimes' => 'Ảnh chỉ được phép có định dạng jpg, jpeg, png hoặc webp.',
                'image.max' => 'Dung lượng ảnh tối đa 2MB.',

                'description.string' => 'Mô tả không hợp lệ.',

                'status.required' => 'Vui lòng chọn trạng thái.',
                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

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
