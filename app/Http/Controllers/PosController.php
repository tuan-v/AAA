<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\CompanyCurrencyRate;
use App\Models\Currency;
use App\Models\PosCoupon;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use App\Services\InventoryMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function drafts(Request $request): JsonResponse
    {
        $orders = SalesOrder::query()
            ->where('company_id', $this->company($request)->id)
            ->where('sales_channel', 'pos')
            ->where('status', 'pending')
            ->with(['items.product', 'customer', 'currency', 'posCoupon'])
            ->latest()
            ->get()
            ->map(fn (SalesOrder $order) => $this->receipt($order));

        return response()->json(['data' => $orders]);
    }

    public function options(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $currency = $company->default_currency;
        $customers = Customer::query()
            ->where('company_id', $company->id)->where('status', 'active')
            ->where('code', '!=', 'KH_LE')
            ->withExists(['orders as debt_eligible' => fn ($query) => $query->where('status', 'completed')])
            ->orderBy('name')->get(['id', 'code', 'name', 'phone'])
            ->map(fn (Customer $customer) => [...$customer->toArray(), 'debt_eligible' => (bool) $customer->debt_eligible]);
        $warehouses = Warehouse::query()->where('company_id', $company->id)
            ->where('status', 'active')->orderBy('name')->get(['id', 'code', 'name']);
        $products = Product::query()->where('products.company_id', $company->id)
            ->where('products.status', 'active')->orderBy('products.name')
            ->get(['id', 'sku', 'barcode', 'name', 'sell_price'])
            ->map(function (Product $product) use ($company) {
                $stocks = WarehouseProductStock::query()->where('company_id', $company->id)
                    ->where('product_id', $product->id)->pluck('quantity', 'warehouse_id');
                return [...$product->toArray(), 'sell_price' => (float) $product->sell_price, 'stocks' => $stocks];
            });
        $coupons = PosCoupon::query()->where('company_id', $company->id)->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('code')->get();
        $currencies = Currency::query()->where('is_active', true)->orderBy('code')
            ->get(['id', 'code', 'name', 'symbol', 'exchange_rate'])
            ->map(fn (Currency $item) => [
                ...$item->only(['id', 'code', 'name', 'symbol']),
                'rate' => $this->paymentRate($company->id, $item),
            ]);

        return response()->json([
            'customers' => $customers, 'warehouses' => $warehouses,
            'products' => $products, 'coupons' => $coupons,
            'currencies' => $currencies,
            'currency' => $currency ? $currency->only(['id', 'code', 'symbol']) : ['id' => null, 'code' => 'VND', 'symbol' => '₫'],
        ]);
    }

    public function createDraft(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $currency = $company->default_currency;
        abort_unless($currency, 422, 'Công ty chưa cấu hình tiền tệ mặc định.');
        $customer = $this->walkInCustomer($company->id, $currency->id);
        $order = SalesOrder::create([
            'company_id' => $company->id, 'customer_id' => $customer->id,
            'currency_id' => $currency->id, 'exchange_rate' => 1,
            'expected_delivery_date' => now()->toDateString(), 'status' => 'pending',
            'sales_channel' => 'pos', 'created_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Đã tạo hóa đơn chờ.', 'data' => $this->receipt($order)], 201);
    }

    public function storeCustomer(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
        $currency = $company->default_currency;
        abort_unless($currency, 422, 'Công ty chưa cấu hình tiền tệ mặc định.');

        $customer = Customer::create([
            ...$validated,
            'company_id' => $company->id,
            'currency_id' => $currency->id,
            'opening_debt' => 0,
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Đã thêm khách hàng.', 'data' => [
            ...$customer->only(['id', 'code', 'name', 'phone', 'email']),
            'debt_eligible' => false,
        ]], 201);
    }

    public function updateDraft(Request $request, SalesOrder $order): JsonResponse
    {
        $company = $this->company($request);
        abort_unless($order->company_id === $company->id && $order->sales_channel === 'pos', 404);
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['draft' => 'Hóa đơn này không còn ở trạng thái chờ.']);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('status', 'active'))],
            'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('status', 'active'))],
            'invoice_type' => ['required', Rule::in(['retail', 'vat'])],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'items' => ['array'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);
        $currency = $company->default_currency;
        $customer = ! empty($validated['customer_id'])
            ? Customer::where('company_id', $company->id)->findOrFail($validated['customer_id'])
            : $this->walkInCustomer($company->id, $currency->id);

        DB::transaction(function () use ($order, $validated, $customer, $company) {
            $subtotal = 0;
            $vatAmount = 0;
            $items = [];
            foreach ($validated['items'] ?? [] as $input) {
                $product = Product::where('company_id', $company->id)->where('status', 'active')->findOrFail($input['product_id']);
                $quantity = (float) $input['quantity'];
                if (! empty($validated['warehouse_id'])) {
                    $stock = WarehouseProductStock::where('company_id', $company->id)
                        ->where('warehouse_id', $validated['warehouse_id'])->where('product_id', $product->id)->first();
                    if (! $stock || $quantity > (float) $stock->quantity) {
                        throw ValidationException::withMessages(['items' => "Sản phẩm {$product->name} không đủ tồn trong kho đã chọn."]);
                    }
                }
                $unitPrice = (float) $product->sell_price;
                $vatPercent = 10;
                $amount = round($quantity * $unitPrice, 2);
                $subtotal += $amount;
                $vatAmount += round($amount * $vatPercent / 100, 2);
                $items[] = compact('product', 'quantity', 'unitPrice', 'vatPercent', 'amount');
            }
            $coupon = null;
            $discount = 0;
            if (! empty($validated['coupon_code'])) {
                $coupon = PosCoupon::where('company_id', $company->id)->where('code', strtoupper(trim($validated['coupon_code'])))->first();
                $discount = $coupon?->discountFor($subtotal) ?? 0;
            }
            $order->update([
                'customer_id' => $customer->id,
                'pos_warehouse_id' => $validated['warehouse_id'] ?? null,
                'invoice_type' => 'vat',
                'pos_coupon_id' => $coupon?->id,
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'discount_amount' => $discount,
                'total_amount' => round(max(0, $subtotal + $vatAmount - $discount), 2),
            ]);
            $order->items()->delete();
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id, 'quantity' => $item['quantity'],
                    'unit_price' => $item['unitPrice'], 'company_unit_price' => $item['unitPrice'],
                    'vat_percent' => $item['vatPercent'], 'amount' => $item['amount'], 'company_amount' => $item['amount'],
                ]);
            }
        });

        return response()->json(['message' => 'Đã lưu hóa đơn chờ.', 'data' => $this->receipt($order->fresh())]);
    }

    public function cancelDraft(Request $request, SalesOrder $order): JsonResponse
    {
        $company = $this->company($request);
        abort_unless($order->company_id === $company->id && $order->sales_channel === 'pos', 404);
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['draft' => 'Chỉ có thể hủy hóa đơn đang chờ.']);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Đã hủy hóa đơn chờ.']);
    }

    public function store(Request $request, InventoryMovementService $movements): JsonResponse
    {
        $company = $this->company($request);
        $validated = $request->validate([
            'draft_id' => ['nullable', 'integer'],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('status', 'active'))],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('status', 'active'))],
            'payment_method' => ['required', Rule::in(['cash', 'momo'])],
            'payment_currency_id' => ['nullable', Rule::exists('currencies', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'invoice_type' => ['sometimes', Rule::in(['retail', 'vat'])],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'payment_reference' => ['nullable', 'required_if:payment_method,momo', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.vat_percent' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);
        $currency = $company->default_currency;
        abort_unless($currency, 422, 'Công ty chưa cấu hình tiền tệ mặc định.');
        $customer = ! empty($validated['customer_id'])
            ? Customer::where('company_id', $company->id)->findOrFail($validated['customer_id'])
            : $this->walkInCustomer($company->id, $currency->id);
        $paymentCurrencyId = (int) ($validated['payment_currency_id'] ?? $currency->id);
        $paymentRate = $this->paymentRate($company->id, Currency::findOrFail($paymentCurrencyId));

        $order = DB::transaction(function () use ($validated, $customer, $company, $currency, $paymentCurrencyId, $paymentRate, $request, $movements) {
            $order = null;
            if (! empty($validated['draft_id'])) {
                $order = SalesOrder::query()
                    ->where('company_id', $company->id)
                    ->where('sales_channel', 'pos')
                    ->lockForUpdate()
                    ->find($validated['draft_id']);
                if (! $order || $order->status !== 'pending') {
                    throw ValidationException::withMessages(['draft_id' => 'Hóa đơn chờ không tồn tại hoặc đã được thanh toán.']);
                }
            }
            $subtotal = 0; $vatAmount = 0; $items = [];
            foreach ($validated['items'] as $input) {
                $product = Product::query()->where('company_id', $company->id)->where('status', 'active')->findOrFail($input['product_id']);
                $stock = WarehouseProductStock::query()->where('company_id', $company->id)
                    ->where('warehouse_id', $validated['warehouse_id'])->where('product_id', $product->id)
                    ->lockForUpdate()->first();
                $quantity = (float) $input['quantity'];
                if (! $stock || $quantity > (float) $stock->quantity) {
                    throw ValidationException::withMessages(['items' => "Sản phẩm {$product->name} không đủ tồn trong kho đã chọn."]);
                }
                $unitPrice = (float) $product->sell_price;
                $vatPercent = 10.0;
                $amount = round($quantity * $unitPrice, 2);
                $vat = round($amount * $vatPercent / 100, 2);
                $subtotal += $amount; $vatAmount += $vat;
                $items[] = compact('product', 'stock', 'quantity', 'unitPrice', 'vatPercent', 'amount');
            }

            $coupon = null; $discount = 0;
            if (! empty($validated['coupon_code'])) {
                $coupon = PosCoupon::query()->where('company_id', $company->id)
                    ->where('code', strtoupper(trim($validated['coupon_code'])))->lockForUpdate()->first();
                if (! $coupon || ($discount = $coupon->discountFor($subtotal)) <= 0) {
                    throw ValidationException::withMessages(['coupon_code' => 'Phiếu giảm giá không hợp lệ hoặc chưa đủ giá trị đơn tối thiểu.']);
                }
            }
            $total = round(max(0, $subtotal + $vatAmount - $discount), 2);
            $paymentTenderedAmount = round((float) $validated['paid_amount'], 2);
            $tenderedAmount = round($paymentTenderedAmount * $paymentRate, 2);
            if ($validated['payment_method'] === 'momo' && $tenderedAmount > $total) {
                throw ValidationException::withMessages(['paid_amount' => 'Số tiền MoMo không được lớn hơn tổng tiền.']);
            }
            $paidAmount = min($tenderedAmount, $total);
            $changeAmount = $validated['payment_method'] === 'cash'
                ? round(max(0, $tenderedAmount - $total), 2)
                : 0;
            $isWalkIn = $customer->code === 'KH_LE';
            $hasPurchased = ! $isWalkIn && $customer->orders()->where('status', 'completed')->exists();
            if ($paidAmount < $total && ! $hasPurchased) {
                throw ValidationException::withMessages(['paid_amount' => 'Khách lẻ hoặc khách hàng mới chưa từng mua hàng phải thanh toán đủ.']);
            }

            $orderData = [
                'company_id' => $company->id, 'customer_id' => $customer->id,
                'currency_id' => $currency->id, 'exchange_rate' => 1,
                'province_id' => $customer->province_id, 'ward_id' => $customer->ward_id,
                'address_detail' => $customer->address_detail, 'expected_delivery_date' => now()->toDateString(),
                'note' => $validated['note'] ?? null, 'subtotal' => $subtotal, 'vat_amount' => $vatAmount,
                'discount_amount' => $discount, 'total_amount' => $total, 'status' => 'completed',
                'sales_channel' => 'pos', 'pos_warehouse_id' => $validated['warehouse_id'],
                'payment_method' => $validated['payment_method'], 'invoice_type' => 'vat',
                'payment_currency_id' => $paymentCurrencyId, 'payment_exchange_rate' => $paymentRate,
                'payment_tendered_amount' => $paymentTenderedAmount,
                'paid_amount' => $paidAmount,
                'tendered_amount' => $tenderedAmount, 'change_amount' => $changeAmount,
                'pos_coupon_id' => $coupon?->id, 'payment_reference' => $validated['payment_reference'] ?? null,
                'completed_at' => now(), 'approved_by' => $request->user()->id, 'approved_at' => now(),
                'created_by' => $request->user()->id,
            ];
            if ($order) {
                $order->update($orderData);
                $order->items()->delete();
            } else {
                $order = SalesOrder::create($orderData);
            }
            if ($coupon) {
                $coupon->increment('used_count');
            }

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id, 'quantity' => $item['quantity'],
                    'unit_price' => $item['unitPrice'], 'company_unit_price' => $item['unitPrice'],
                    'vat_percent' => $item['vatPercent'], 'amount' => $item['amount'], 'company_amount' => $item['amount'],
                ]);
                $stock = $item['stock'];
                $beforeQuantity = (float) $stock->quantity; $beforeValue = (float) $stock->stock_value;
                $unitCost = (float) $item['product']->purchase_price;
                $stock->quantity = round($beforeQuantity - $item['quantity'], 3);
                $stock->stock_value = round((float) $stock->quantity * $unitCost, 2);
                $stock->save();
                $movements->record($stock, 'pos_export', $item['quantity'], $unitCost, $beforeQuantity, $beforeValue, $order);
            }

            $debt = round($total - $paidAmount, 2);
            if ($debt > 0) {
                CustomerDebt::create([
                    'customer_id' => $customer->id, 'type' => 'sale', 'amount' => $debt,
                    'currency_id' => $currency->id, 'original_amount' => $debt,
                    'exchange_rate' => 1, 'amount_base' => $debt,
                    'reference_type' => SalesOrder::class, 'reference_id' => $order->id,
                    'note' => "Công nợ bán tại quầy {$order->code}",
                ]);
            }

            return $order->load(['items.product', 'customer', 'currency']);
        });

        return response()->json(['message' => 'Thanh toán thành công.', 'data' => $this->receipt($order)], 201);
    }

    public function history(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $orders = SalesOrder::with(['customer', 'currency'])->where('company_id', $company->id)
            ->where('sales_channel', 'pos')->latest()->paginate(20);
        return response()->json($orders);
    }

    public function show(Request $request, SalesOrder $order): JsonResponse
    {
        abort_unless($order->company_id === $this->company($request)->id && $order->sales_channel === 'pos', 404);
        return response()->json(['data' => $this->receipt($order->load(['items.product', 'customer', 'currency']))]);
    }

    private function company(Request $request)
    {
        return $request->user()->company ?? $request->user()->companies()->firstOrFail();
    }

    private function walkInCustomer(int $companyId, int $currencyId): Customer
    {
        return Customer::firstOrCreate(
            ['company_id' => $companyId, 'code' => 'KH_LE'],
            ['name' => 'Khách lẻ', 'currency_id' => $currencyId, 'opening_debt' => 0, 'status' => 'active']
        );
    }

    private function paymentRate(int $companyId, Currency $currency): float
    {
        $company = \App\Models\Company::with('currencies')->findOrFail($companyId);
        $defaultCurrencyId = $company->currencies
            ->first(fn ($item) => (bool) $item->pivot->is_default)?->id;
        if ((int) $defaultCurrencyId === (int) $currency->id) {
            return 1.0;
        }

        $rate = CompanyCurrencyRate::query()
            ->where('company_id', $companyId)
            ->where('currency_id', $currency->id)
            ->whereDate('effective_date', '<=', now())
            ->latest('effective_date')
            ->value('rate_to_base');
        $rate ??= $currency->exchange_rate;
        if (! $rate || (float) $rate <= 0) {
            throw ValidationException::withMessages([
                'payment_currency_id' => "Tiền tệ {$currency->code} chưa có tỷ giá hợp lệ.",
            ]);
        }

        return (float) $rate;
    }

    private function receipt(SalesOrder $order): array
    {
        $order->loadMissing(['items.product', 'customer', 'currency', 'posCoupon', 'paymentCurrency']);
        return [
            'id' => $order->id, 'code' => $order->code, 'status' => $order->status,
            'created_at' => $order->created_at, 'completed_at' => $order->completed_at,
            'customer' => $order->customer?->only(['id', 'code', 'name', 'phone']),
            'currency' => $order->currency?->only(['code', 'symbol']),
            'items' => $order->items->map(fn ($item) => [
                'product_id' => $item->product_id, 'name' => $item->product?->name, 'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price, 'amount' => (float) $item->amount,
            ]),
            'subtotal' => (float) $order->subtotal, 'vat_amount' => (float) $order->vat_amount,
            'discount_amount' => (float) $order->discount_amount, 'total_amount' => (float) $order->total_amount,
            'paid_amount' => (float) $order->paid_amount,
            'tendered_amount' => (float) $order->tendered_amount,
            'change_amount' => (float) $order->change_amount,
            'debt_amount' => max(0, (float) $order->total_amount - (float) $order->paid_amount),
            'payment_method' => $order->payment_method, 'payment_reference' => $order->payment_reference,
            'payment_currency' => $order->paymentCurrency?->only(['id', 'code', 'name', 'symbol']),
            'payment_exchange_rate' => (float) ($order->payment_exchange_rate ?: 1),
            'payment_tendered_amount' => (float) $order->payment_tendered_amount,
            'invoice_type' => $order->invoice_type,
            'warehouse_id' => $order->pos_warehouse_id,
            'coupon_code' => $order->posCoupon?->code,
        ];
    }
}
