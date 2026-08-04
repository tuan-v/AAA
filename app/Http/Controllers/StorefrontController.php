<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\Product;
use App\Models\PosCoupon;
use App\Models\SalesOrder;
use App\Models\WarehouseProductStock;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function directory(): Response
    {
        return Inertia::render('Storefront/Directory', [
            'stores' => Company::query()->where('storefront_enabled', true)
                ->whereNotNull('storefront_slug')->orderBy('name')
                ->get()->map(fn (Company $company) => $this->companyData($company)),
        ]);
    }

    public function shop(Company $company): Response
    {
        $this->ensureEnabled($company);

        return Inertia::render('Storefront/Shop', ['store' => $this->companyData($company)]);
    }

    public function accountPage(Company $company): Response
    {
        $this->ensureEnabled($company);
        return Inertia::render('Storefront/Account', ['store' => $this->companyData($company)]);
    }

    public function productPage(Company $company, Product $product): Response
    {
        $this->ensureEnabled($company);
        abort_unless($product->company_id === $company->id && $product->storefront_visible, 404);
        return Inertia::render('Storefront/Product', ['store' => $this->companyData($company), 'productId' => $product->id]);
    }
    public function cartPage(Company $company): Response { $this->ensureEnabled($company); return Inertia::render('Storefront/Cart', ['store' => $this->companyData($company)]); }
    public function checkoutPage(Company $company): Response { $this->ensureEnabled($company); return Inertia::render('Storefront/Checkout', ['store' => $this->companyData($company)]); }
    public function successPage(Company $company): Response { $this->ensureEnabled($company); return Inertia::render('Storefront/Success', ['store' => $this->companyData($company)]); }

    public function products(Request $request, Company $company): JsonResponse
    {
        $this->ensureEnabled($company);
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'name'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $products = Product::query()
            ->with(['category:id,name', 'unit:id,name'])
            ->withSum(['stocks as available_stock' => fn ($q) => $q->where('company_id', $company->id)], 'quantity')
            ->where('company_id', $company->id)->where('status', 'active')->where('storefront_visible', true)
            ->whereIn('products.id', DB::table('warehouse_product_stocks')->select('product_id')
                ->where('company_id', $company->id)->groupBy('product_id')->havingRaw('SUM(quantity) > 0'))
            ->when($validated['search'] ?? null, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($validated['category_id'] ?? null, fn ($q, $category) => $q->where('category_id', $category))
            ->when($validated['min_price'] ?? null, fn ($q, $price) => $q->where('sell_price', '>=', $price))
            ->when($validated['max_price'] ?? null, fn ($q, $price) => $q->where('sell_price', '<=', $price))
            ->when(($validated['sort'] ?? 'name') === 'price_asc', fn ($q) => $q->orderBy('sell_price'))
            ->when(($validated['sort'] ?? 'name') === 'price_desc', fn ($q) => $q->orderByDesc('sell_price'))
            ->when(($validated['sort'] ?? 'name') === 'newest', fn ($q) => $q->orderByDesc('id'))
            ->when(($validated['sort'] ?? 'name') === 'name', fn ($q) => $q->orderBy('name'))->paginate(12);

        $products->getCollection()->transform(fn (Product $product) => $this->productData($product));
        return response()->json([
            'products' => $products,
            'categories' => DB::table('categories')->where('company_id', $company->id)
                ->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'currency' => $this->currencyData($company),
        ]);
    }

    public function product(Company $company, Product $product): JsonResponse
    {
        $this->ensureEnabled($company);
        abort_unless($product->company_id === $company->id && $product->status === 'active'
            && $product->storefront_visible, 404);
        $product->load(['category:id,name', 'unit:id,name'])->loadSum('stocks as available_stock', 'quantity');
        return response()->json(['product' => $this->productData($product)]);
    }

    public function vouchers(Company $company): JsonResponse
    {
        $this->ensureEnabled($company);
        $items = PosCoupon::where('company_id', $company->id)->where('is_active', true)->get()
            ->filter(fn ($coupon) => $coupon->discountFor(max(1, (float) $coupon->minimum_order_amount)) > 0)
            ->map(fn ($coupon) => ['code' => $coupon->code, 'name' => $coupon->name, 'type' => $coupon->type,
                'value' => (float) $coupon->value, 'minimum_order_amount' => (float) $coupon->minimum_order_amount,
                'maximum_discount' => $coupon->maximum_discount !== null ? (float) $coupon->maximum_discount : null])->values();
        return response()->json(['vouchers' => $items]);
    }

    public function checkout(Request $request, Company $company, CodeGeneratorService $codes): JsonResponse
    {
        $this->ensureEnabled($company);
        $validated = $request->validate([
            'customer.name' => ['required', 'string', 'max:150'],
            'customer.phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
            'customer.email' => ['nullable', 'email', 'max:150'],
            'customer.address' => ['required', 'string', 'max:500'],
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
            'shipping_method' => ['required', Rule::in(['standard', 'express'])],
            'note' => ['nullable', 'string', 'max:1000'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ], ['customer.phone.regex' => 'Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.']);
        $currency = $company->default_currency;
        if (! $currency) {
            throw ValidationException::withMessages(['store' => 'Cửa hàng chưa cấu hình tiền tệ mặc định.']);
        }

        $order = DB::transaction(function () use ($validated, $company, $currency, $codes) {
            $lines = [];
            $subtotal = 0.0;
            foreach ($validated['items'] as $input) {
                $product = Product::query()->where('company_id', $company->id)
                    ->where('status', 'active')->lockForUpdate()->find($input['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Có sản phẩm không còn được bán tại cửa hàng này.']);
                }
                $stock = (float) WarehouseProductStock::query()->where('company_id', $company->id)
                    ->where('product_id', $product->id)->lockForUpdate()->sum('quantity');
                $quantity = (float) $input['quantity'];
                if ($quantity > $stock) {
                    throw ValidationException::withMessages(['items' => "Sản phẩm {$product->name} không đủ tồn kho."]);
                }
                $price = $this->sellingPrice($product);
                $amount = round($price * $quantity, 2);
                $subtotal += $amount;
                $lines[] = compact('product', 'quantity', 'price', 'amount');
            }

            $customerInput = $validated['customer'];
            $account = $this->sessionAccount(request(), $company);
            $customer = $account?->customer ?: Customer::query()->where('company_id', $company->id)
                ->where('phone', $customerInput['phone'])->first();
            if (! $customer) {
                $customer = Customer::create([
                    'company_id' => $company->id,
                    'code' => $codes->generate(Customer::class, 'KH', 4, $company->id),
                    'name' => $customerInput['name'], 'phone' => $customerInput['phone'],
                    'email' => $customerInput['email'] ?? null, 'address_detail' => $customerInput['address'],
                    'currency_id' => $currency->id, 'status' => 'active',
                ]);
            } else {
                $customer->update([
                    'name' => $customerInput['name'], 'email' => $customerInput['email'] ?? $customer->email,
                    'address_detail' => $customerInput['address'],
                ]);
            }

            $coupon = null; $discount = 0;
            if (! empty($validated['coupon_code'])) {
                $coupon = PosCoupon::where('company_id', $company->id)->where('code', strtoupper(trim($validated['coupon_code'])))->lockForUpdate()->first();
                $discount = $coupon?->discountFor($subtotal) ?? 0;
                if ($discount <= 0) throw ValidationException::withMessages(['coupon_code' => 'Mã giảm giá không hợp lệ hoặc chưa đủ giá trị đơn tối thiểu.']);
            }
            $shippingFee = $validated['shipping_method'] === 'express' ? 30000 : 0;
            $vatPercent = 10.0;
            $vatAmount = round($subtotal * $vatPercent / 100, 2);
            $order = SalesOrder::create([
                'code' => $codes->generate(SalesOrder::class, 'SO', 4, $company->id),
                'company_id' => $company->id, 'customer_id' => $customer->id,
                'currency_id' => $currency->id, 'exchange_rate' => 1,
                'address_detail' => $customerInput['address'], 'note' => $validated['note'] ?? null,
                'expected_delivery_date' => now()->addDays($validated['shipping_method'] === 'express' ? 1 : 3)->toDateString(),
                'customer_account_id' => $account?->id,
                'subtotal' => $subtotal, 'vat_amount' => $vatAmount, 'shipping_fee' => $shippingFee,
                'discount_amount' => $discount, 'pos_coupon_id' => $coupon?->id,
                'total_amount' => round(max(0, $subtotal + $vatAmount - $discount) + $shippingFee, 2),
                'status' => 'pending', 'sales_channel' => 'storefront',
                'payment_method' => $validated['payment_method'], 'payment_currency_id' => $currency->id,
                'shipping_method' => $validated['shipping_method'],
                'payment_exchange_rate' => 1, 'paid_amount' => 0,
                'created_by' => $company->owner_id,
            ]);
            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id, 'quantity' => $line['quantity'],
                    'unit_price' => $line['price'], 'company_unit_price' => $line['price'],
                    'vat_percent' => $vatPercent, 'amount' => $line['amount'], 'company_amount' => $line['amount'],
                ]);
            }
            if ($coupon) $coupon->increment('used_count');
            return $order;
        });

        return response()->json(['message' => 'Đặt hàng thành công.', 'order' => [
            'code' => $order->code, 'total' => (float) $order->total_amount,
        ]], 201);
    }

    private function ensureEnabled(Company $company): void
    {
        abort_unless($company->storefront_enabled && $company->storefront_slug, 404);
    }

    private function companyData(Company $company): array
    {
        $logo = $company->logo;
        if ($logo && ! preg_match('#^https?://#i', $logo) && ! str_starts_with($logo, '/')) {
            $logo = asset('storage/'.ltrim($logo, '/'));
        }
        return ['slug' => $company->storefront_slug, 'name' => $company->name, 'logo' => $logo,
            'address' => $company->address, 'phone' => $company->phone, 'email' => $company->email,
            'currency' => $this->currencyData($company)];
    }

    private function currencyData(Company $company): array
    {
        $currency = $company->default_currency;
        return ['code' => $currency?->code ?? 'VND', 'symbol' => $currency?->symbol ?? '₫'];
    }

    private function sellingPrice(Product $product): float
    {
        $promotionActive = $product->promotional_price !== null
            && (! $product->promotion_starts_at || $product->promotion_starts_at->lte(now()))
            && (! $product->promotion_ends_at || $product->promotion_ends_at->gte(now()));
        return (float) ($promotionActive ? $product->promotional_price : $product->sell_price);
    }

    private function productData(Product $product): array
    {
        return ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
            'description' => $product->description, 'image' => $product->image ? asset('storage/'.$product->image) : null,
            'sell_price' => (float) $product->sell_price, 'selling_price' => $this->sellingPrice($product),
            'has_promotion' => $this->sellingPrice($product) < (float) $product->sell_price,
            'available_stock' => (float) ($product->available_stock ?? 0),
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
            'unit' => $product->unit ? ['id' => $product->unit->id, 'name' => $product->unit->name] : null];
    }

    private function sessionAccount(Request $request, Company $company): ?CustomerAccount
    {
        if ((int) $request->session()->get('storefront_company_id') !== $company->id) return null;
        return CustomerAccount::with('customer')->where('company_id', $company->id)
            ->find($request->session()->get('storefront_customer_account_id'));
    }
}
