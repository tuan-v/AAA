<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(
        protected CurrencyService $currencyService
    ) {}

    private function companyId(): int
    {
        $companyId = auth()->user()->company_id
            ?? auth()->user()->companies()->value('companies.id');
        abort_unless($companyId, 403, 'Tài khoản chưa thuộc công ty nào.');
        return (int) $companyId;
    }

    private function isUsed(Product $product): bool
    {
        return DB::table('purchase_order_items')->where('product_id', $product->id)->exists()
            || DB::table('sales_order_items')->where('product_id', $product->id)->exists()
            || DB::table('warehouse_slip_items')->where('product_id', $product->id)->exists()
            || DB::table('warehouse_product_stocks')->where('product_id', $product->id)->exists();
    }

    private function categoryRules(int $companyId): array
    {
        return [
            'required',
            Rule::exists('categories', 'id')->where(
                fn ($query) => $query->where('company_id', $companyId)->where('status', 'active')
            ),
            function (string $attribute, mixed $value, \Closure $fail) use ($companyId) {
                $category = Category::where('company_id', $companyId)->find($value);
                if ($category?->children()->exists()) {
                    $fail('Danh mục cha chỉ dùng để phân nhóm. Vui lòng chọn danh mục con cuối cùng.');
                }
            },
        ];
    }
    // Danh sách
    public function index(Request $request)
    {
        $company = auth()->user()->company;
        $currency = $this->currencyService->getCompanyCurrency($company);
        $query = Product::with([
            'category',
            'unit',
            'stocks',
            'stocks.warehouse'
        ])
            ->select('products.*')
            ->leftJoin('warehouse_product_stocks as wps', 'products.id', '=', 'wps.product_id')
            ->selectRaw('COALESCE(SUM(wps.quantity), 0) as total_quantity')
            ->groupBy('products.id');

        // Danh sách thuộc phân hệ kho chỉ hiển thị sản phẩm còn tồn thực tế.
        // Danh mục mua hàng vẫn giữ sản phẩm hết tồn để có thể lập đơn nhập lại.
        if ($request->is('api/warehouse/products')) {
            $query->havingRaw('COALESCE(SUM(wps.quantity), 0) > 0');
        }
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
            $warehouseId = (int) $request->warehouse_id;
            $query->where('wps.warehouse_id', $warehouseId);
            $query->whereHas('stocks', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id)
                    ->where('quantity', '>', 0);
            });
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 5))
            ->through(function ($p) use ($currency, $request) {

                $stocks = $p->stocks
                    ->when(
                        $request->filled('warehouse_id'),
                        fn ($stocks) => $stocks->where('warehouse_id', (int) $request->warehouse_id)
                    );

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,

                    'category_id' => $p->category_id,
                    'category_name' => $p->category?->name,

                    'unit_id' => $p->unit_id,
                    'unit_name' => $p->unit?->name,
                    'allow_decimal' => (bool) $p->unit?->allow_decimal,

                    'quantity' => $stocks->sum('quantity'),
                    'warehouse' => $stocks
                        ->filter(fn ($s) => (float) $s->quantity > 0)
                        ->values()
                        ->map(function ($s) {
                        return [
                            'warehouse_name' => $s->warehouse?->name,
                            'quantity' => $s->quantity,
                        ];
                    }),
                    'purchase_price' => $this->currencyService
                        ->convertByCurrency($p->purchase_price, $currency),

                    'sell_price' => $this->currencyService
                        ->convertByCurrency($p->sell_price, $currency),

                    'currency_symbol' => $currency?->symbol,

                    'currency_code' => $currency?->code,

                    'image' => $p->image
                        ? asset('storage/' . $p->image)
                        : null,

                    'description' => $p->description,
                    'status' => $p->status,
                    'is_used' => $this->isUsed($p),
                ];
            });

        return response()->json($products);
    }
    // API trả về tất cả sản phẩm cho dropdown (không phân trang)
    public function forSelect()
    {
        $products = Product::with(['stocks', 'unit'])
            ->where('status', 'active')
            ->whereHas('stocks', fn ($query) => $query->where('quantity', '>', 0))
            ->get()
            ->filter(fn ($product) => (float) $product->stocks->sum('quantity') > 0)
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'sale_price' => $p->sell_price,
                    'stock_quantity' => $p->stocks->sum('quantity'),
                    'allow_decimal' => (bool) $p->unit?->allow_decimal,
                    'unit_name' => $p->unit?->name,
                ];
            });

        return response()->json($products);
    }

    // Thêm sản phẩm
    public function store(Request $request)
    {
        $companyId = $this->companyId();

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'max:255',
                    Rule::unique('products', 'name')->where(
                        fn ($query) => $query->where('company_id', $companyId)
                    ),
                ],
                'sku' => [
                    'required',
                    'max:255',
                    Rule::unique('products', 'sku')->where(
                        fn ($query) => $query->where('company_id', $companyId)
                    ),
                ],

                'category_id' => $this->categoryRules($companyId),
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
                'name.unique' => 'Tên sản phẩm đã tồn tại trong công ty.',
                'sku.required' => 'Vui lòng nhập mã hàng.',
                'sku.unique' => 'Mã hàng đã tồn tại trong công ty.',
                'sku.max' => 'Mã hàng tối đa 255 ký tự.',

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
        $companyId = auth()->user()->company_id;

        $product = Product::where('company_id', $companyId)
            ->findOrFail($id);

        if ($this->isUsed($product)) {
            return response()->json([
                'message' => 'Sản phẩm đã phát sinh giao dịch, không thể chỉnh sửa. Bạn chỉ có thể khóa hoặc mở khóa.',
            ], 422);
        }

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'max:255',
                    Rule::unique('products', 'name')
                        ->ignore($product->id)
                        ->where(fn ($query) => $query->where('company_id', $companyId)),
                ],

                'sku' => [
                    'nullable',
                    'max:255',
                    Rule::unique('products', 'sku')
                        ->ignore($product->id)
                        ->where(fn ($query) => $query->where('company_id', $companyId)),
                ],

                'category_id' => $this->categoryRules($companyId),
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
                'name.unique' => 'Tên sản phẩm đã tồn tại trong công ty.',

                'sku.unique' => 'Mã hàng đã tồn tại trong công ty.',
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

    public function destroy($id)
    {
        $product = Product::where('company_id', $this->companyId())->findOrFail($id);

        if ($this->isUsed($product)) {
            return response()->json([
                'message' => 'Sản phẩm đã phát sinh giao dịch, không thể xóa. Bạn có thể chuyển sang trạng thái khóa.',
            ], 422);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json(['message' => 'Xóa sản phẩm thành công.']);
    }
    // Đổi trạng thái
    public function toggleStatus($id)
    {
        $product = Product::where('company_id', $this->companyId())->findOrFail($id);

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
