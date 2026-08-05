<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\CustomerAddress;
use App\Models\Notification;
use App\Models\Province;
use App\Models\Ward;
use App\Services\CodeGeneratorService;
use App\Services\CouponService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontAccountController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function orderPage(Request $request, Company $company, string $code): Response
    {
        $this->enabled($company);
        $account = $this->account($request, $company);
        $order = $account->orders()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->with([
                'customer:id,name,email,phone',
                'currency:id,code,symbol',
                'posCoupon:id,code,name',
                'items.product:id,name,sku,image,unit_id,status,storefront_visible,sell_price,promotional_price,promotion_starts_at,promotion_ends_at',
                'items.product.unit:id,name',
                'shippingPartner:id,name,tracking_url_template',
            ])
            ->firstOrFail();

        $currency = $order->currency ?? $company->default_currency;
        $total = (float) $order->total_amount;
        $paid = (float) $order->paid_amount;
        $paymentStatus = $order->payment_status
            ?: ($paid >= $total && $total > 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'));

        return Inertia::render('Storefront/OrderDetail', [
            'store' => [
                'slug' => $company->storefront_slug,
                'name' => $company->name,
                'logo' => $company->logo,
                'phone' => $company->phone,
                'email' => $company->email,
                'currency' => ['code' => $currency?->code ?? 'VND', 'symbol' => $currency?->symbol ?? '₫'],
            ],
            'order' => [
                'code' => $order->code,
                'status' => $order->effective_status,
                'status_label' => $this->orderStatusLabel($order->effective_status),
                'created_at' => $order->created_at?->format('d/m/Y H:i'),
                'expected_delivery_date' => $order->expected_delivery_date?->format('d/m/Y'),
                'recipient' => [
                    'name' => $order->recipient_name ?? $order->customer?->name,
                    'phone' => $order->recipient_phone ?? $order->customer?->phone,
                    'email' => $order->recipient_email ?? $order->customer?->email,
                    'address' => $order->address_detail,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name ?? 'Sản phẩm không còn tồn tại',
                    'sku' => $item->product?->sku,
                    'image' => $item->product?->image ? asset('storage/'.ltrim($item->product->image, '/')) : null,
                    'unit' => $item->product?->unit?->name,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'vat_percent' => (float) $item->vat_percent,
                    'amount' => (float) $item->amount,
                    'repurchase' => $this->repurchaseProductData($item->product, (float) $item->quantity),
                ])->values(),
                'subtotal' => (float) $order->subtotal,
                'vat_amount' => (float) $order->vat_amount,
                'discount_amount' => (float) $order->discount_amount,
                'shipping_fee' => (float) $order->shipping_fee,
                'total' => $total,
                'coupon' => $order->posCoupon?->code,
                'payment_method' => $order->payment_method,
                'payment_status' => $paymentStatus,
                'paid_amount' => $paid,
                'payment_reference' => $order->payment_reference,
                'shipping_method' => $order->shipping_method,
                'tracking_code' => $order->tracking_code,
                'shipping_partner' => $order->shippingPartner?->name,
                'tracking_url' => $order->tracking_code && $order->shippingPartner?->tracking_url_template
                    ? str_replace('{tracking_code}', rawurlencode($order->tracking_code), $order->shippingPartner->tracking_url_template)
                    : null,
                'shipping_note' => $order->shipping_note,
                'note' => $order->note,
                'cancellation_reason' => $order->cancellation_reason,
                'cancelable' => $order->status === 'pending',
                'repurchasable' => in_array($order->effective_status, ['cancelled', 'completed'], true),
                'timeline' => collect([
                    ['key' => 'placed', 'label' => 'Đã đặt hàng', 'date' => $order->created_at?->format('d/m/Y H:i'), 'done' => true],
                    ['key' => 'confirmed', 'label' => 'Đã xác nhận', 'date' => $order->approved_at?->format('d/m/Y H:i'), 'done' => (bool) $order->approved_at],
                    ['key' => 'shipping', 'label' => 'Đang giao hàng', 'date' => $order->shipping_started_at?->format('d/m/Y H:i'), 'done' => (bool) $order->shipping_started_at],
                    ['key' => 'completed', 'label' => 'Hoàn thành', 'date' => $order->completed_at?->format('d/m/Y H:i'), 'done' => (bool) $order->completed_at],
                ])->values(),
            ],
        ]);
    }

    public function notificationPage(Request $request, Company $company): Response
    {
        $this->enabled($company);
        $this->account($request, $company);

        return Inertia::render('Storefront/Notifications', [
            'store' => [
                'slug' => $company->storefront_slug,
                'name' => $company->name,
                'logo' => $company->logo,
            ],
        ]);
    }

    public function register(Request $request, Company $company, CodeGeneratorService $codes): JsonResponse
    {
        $this->enabled($company);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
            'email' => ['required', 'email', 'max:150', Rule::unique('customer_accounts')->where('company_id', $company->id)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], ['phone.regex' => 'Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.']);
        $customer = Customer::where('company_id', $company->id)->where('phone', $data['phone'])->first();
        if (! $customer) {
            $customer = Customer::create([
                'company_id' => $company->id,
                'code' => $codes->generate(Customer::class, 'KH', 4, $company->id),
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'currency_id' => $company->default_currency?->id,
                'status' => 'active',
            ]);
        }
        $account = CustomerAccount::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'is_active' => true,
        ]);
        $this->loginSession($request, $account);

        return response()->json(['message' => 'Đăng ký thành công.', 'account' => $this->data($account)], 201);
    }

    public function login(Request $request, Company $company): JsonResponse
    {
        $this->enabled($company);
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $account = CustomerAccount::with('customer')->where('company_id', $company->id)
            ->where('email', strtolower($data['email']))->where('is_active', true)->first();
        if (! $account || ! Hash::check($data['password'], $account->password)) {
            return response()->json(['message' => 'Email hoặc mật khẩu không đúng.'], 422);
        }
        $account->update(['last_login_at' => now()]);
        $request->session()->regenerate();
        $this->loginSession($request, $account);

        return response()->json(['message' => 'Đăng nhập thành công.', 'account' => $this->data($account)]);
    }

    public function logout(Request $request, Company $company): JsonResponse
    {
        $this->account($request, $company);
        $request->session()->forget(['storefront_customer_account_id', 'storefront_company_id']);

        return response()->json(['message' => 'Đã đăng xuất.']);
    }

    public function me(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company, false);

        return response()->json(['account' => $account ? $this->data($account) : null]);
    }

    public function updateProfile(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
        ], ['phone.regex' => 'Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.']);
        $account->customer->update($data);

        return response()->json(['message' => 'Đã cập nhật thông tin tài khoản.', 'account' => $this->data($account)]);
    }

    public function updatePassword(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company);
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        if (! Hash::check($data['current_password'], $account->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng.'], 422);
        }
        $account->update(['password' => $data['password']]);
        $request->session()->regenerate();
        $this->loginSession($request, $account);

        return response()->json(['message' => 'Đổi mật khẩu thành công.']);
    }

    public function orders(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company);
        $orders = $account->orders()->with('items.product:id,name,image,status,storefront_visible,sell_price,promotional_price,promotion_starts_at,promotion_ends_at')->where('company_id', $company->id)
            ->latest()->paginate(10)->through(fn ($order) => [
                'code' => $order->code,
                'status' => $order->effective_status,
                'status_label' => $this->orderStatusLabel($order->effective_status),
                'cancelable' => $order->status === 'pending',
                'repurchasable' => in_array($order->effective_status, ['cancelled', 'completed'], true),
                'cancellation_reason' => $order->cancellation_reason,
                'total' => (float) $order->total_amount,
                'shipping_method' => $order->shipping_method,
                'tracking_code' => $order->tracking_code,
                'date' => $order->created_at->format('d/m/Y H:i'),
                'items' => $order->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name,
                    'quantity' => (float) $item->quantity,
                    'price' => (float) $item->unit_price,
                    'repurchase' => $this->repurchaseProductData($item->product, (float) $item->quantity),
                ]),
            ]);

        return response()->json($orders);
    }

    public function notifications(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company);
        $notifications = Notification::query()
            ->where('company_id', $company->id)
            ->where('customer_account_id', $account->id)
            ->whereNull('delivered_at')
            ->oldest()
            ->limit(20)
            ->get(['id', 'title', 'message', 'data', 'url_link', 'created_at']);

        if ($notifications->isNotEmpty()) {
            Notification::whereKey($notifications->pluck('id'))->update(['delivered_at' => now()]);
        }

        return response()->json(['notifications' => $notifications]);
    }

    public function notificationHistory(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company);
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'unread', 'read'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $status = $validated['status'] ?? 'all';
        $query = Notification::query()
            ->where('company_id', $company->id)
            ->where('customer_account_id', $account->id)
            ->when($status === 'unread', fn ($q) => $q->unread())
            ->when($status === 'read', fn ($q) => $q->read())
            ->latest();

        $unreadCount = Notification::query()
            ->where('company_id', $company->id)
            ->where('customer_account_id', $account->id)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $query->paginate(12),
            'unread_count' => $unreadCount,
        ]);
    }

    public function notificationUnreadCount(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company, false);
        $count = $account
            ? Notification::query()
                ->where('company_id', $company->id)
                ->where('customer_account_id', $account->id)
                ->unread()
                ->count()
            : 0;

        return response()->json(['count' => $count]);
    }

    public function markNotificationRead(Request $request, Company $company, Notification $notification): JsonResponse
    {
        $account = $this->account($request, $company);
        $this->ensureOwnedNotification($notification, $company, $account);
        $notification->update(['read_at' => $notification->read_at ?? now()]);

        return response()->json(['message' => 'Đã đánh dấu thông báo là đã đọc.']);
    }

    public function markAllNotificationsRead(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company);
        Notification::query()
            ->where('company_id', $company->id)
            ->where('customer_account_id', $account->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Đã đọc tất cả thông báo.']);
    }

    public function destroyNotification(Request $request, Company $company, Notification $notification): JsonResponse
    {
        $account = $this->account($request, $company);
        $this->ensureOwnedNotification($notification, $company, $account);
        $notification->delete();

        return response()->json(['message' => 'Đã xóa thông báo.']);
    }

    private function ensureOwnedNotification(Notification $notification, Company $company, CustomerAccount $account): void
    {
        abort_unless(
            $notification->company_id === $company->id
            && $notification->customer_account_id === $account->id,
            404
        );
    }

    private function repurchaseProductData($product, float $orderedQuantity): ?array
    {
        if (! $product) {
            return null;
        }

        $stock = max(0, (float) $product->stock_quantity);
        $promotionActive = $product->promotional_price !== null
            && (! $product->promotion_starts_at || $product->promotion_starts_at->lte(now()))
            && (! $product->promotion_ends_at || $product->promotion_ends_at->gte(now()));

        return [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $product->image ? asset('storage/'.ltrim($product->image, '/')) : null,
            'selling_price' => (float) ($promotionActive ? $product->promotional_price : $product->sell_price),
            'available_stock' => $stock,
            'requested_quantity' => $orderedQuantity,
            'available' => $product->status === 'active' && $product->storefront_visible && $stock > 0,
        ];
    }

    public function cancelOrder(Request $request, Company $company, string $code): JsonResponse
    {
        $account = $this->account($request, $company);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $order = $account->orders()->where('company_id', $company->id)->where('code', $code)->firstOrFail();
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Chỉ có thể hủy đơn đang chờ xác nhận.'], 422);
        }
        $order->update(['status' => 'cancelled', 'cancellation_reason' => $data['reason']]);
        app(CouponService::class)->reverseForOrder($order);

        $this->notificationService->createForRole(
            'Quản lý bán hàng',
            $company->id,
            'Khách hàng đã hủy đơn trên website',
            "Khách {$order->recipient_name} đã hủy đơn {$order->code}. Lý do: {$data['reason']}",
            [
                'sales_order_id' => $order->id,
                'status' => 'cancelled',
                'sales_channel' => 'storefront',
                'event_type' => 'storefront_order_cancelled',
                'cancellation_reason' => $data['reason'],
                'toast_type' => 'warning',
            ],
            '/sale/orders',
            'sale',
            excludeCompanyOwner: true
        );

        return response()->json(['message' => 'Đã hủy đơn hàng.']);
    }

    public function addresses(Request $request, Company $company): JsonResponse
    {
        return response()->json(['addresses' => $this->account($request, $company)->addresses()->latest()->get()]);
    }

    public function storeAddress(Request $request, Company $company): JsonResponse
    {
        $account = $this->account($request, $company);
        $data = $request->validate(
            [
                'label' => ['required', 'string', 'max:50'],
                'recipient_name' => ['required', 'string', 'max:150'],
                'phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
                'province_id' => ['required', 'integer', 'exists:provinces,id'],
                'ward_id' => ['required', 'integer', 'exists:wards,id'],
                'address_detail' => ['required', 'string', 'max:500'],
                'is_default' => ['boolean'],
            ],
            ['phone.regex' => 'Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.']
        );
        $province = Province::findOrFail($data['province_id']);
        $ward = Ward::where('province_id', $province->id)->findOrFail($data['ward_id']);
        $data['province_name'] = $province->name;
        $data['ward_name'] = $ward->name;
        $data['address'] = collect([$data['address_detail'], $ward->name, $province->name])->filter()->join(', ');
        if (! $account->addresses()->exists()) {
            $data['is_default'] = true;
        }
        if ($data['is_default'] ?? false) {
            $account->addresses()->update(['is_default' => false]);
        }
        $address = $account->addresses()->create($data);
        if ($address->is_default) {
            $this->syncCustomerAddress($account, $address);
        }

        return response()->json(['address' => $address], 201);
    }

    public function destroyAddress(Request $request, Company $company, CustomerAddress $address): JsonResponse
    {
        $account = $this->account($request, $company);
        abort_unless($address->customer_account_id === $account->id, 404);
        $wasDefault = $address->is_default;
        $address->delete();
        if ($wasDefault) {
            $replacement = $account->addresses()->latest()->first();
            if ($replacement) {
                $replacement->update(['is_default' => true]);
                $this->syncCustomerAddress($account, $replacement);
            } else {
                $account->customer->update(['province_id' => null, 'ward_id' => null, 'address_detail' => null]);
            }
        }

        return response()->json(['message' => 'Đã xóa địa chỉ.']);
    }

    public function updateAddress(Request $request, Company $company, CustomerAddress $address): JsonResponse
    {
        $account = $this->account($request, $company);
        abort_unless($address->customer_account_id === $account->id, 404);
        $wasDefault = $address->is_default;
        $data = $this->validatedAddress($request);
        if ($data['is_default'] ?? false) {
            $account->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }
        $address->update($data);
        if ($address->is_default) {
            $this->syncCustomerAddress($account, $address);
        } elseif ($wasDefault) {
            $replacement = $account->addresses()->whereKeyNot($address->id)->latest()->first();
            if ($replacement) {
                $replacement->update(['is_default' => true]);
                $this->syncCustomerAddress($account, $replacement);
            }
        }

        return response()->json(['message' => 'Đã cập nhật địa chỉ.', 'address' => $address->fresh()]);
    }

    private function validatedAddress(Request $request): array
    {
        $data = $request->validate(
            [
                'label' => ['required', 'string', 'max:50'],
                'recipient_name' => ['required', 'string', 'max:150'],
                'phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
                'province_id' => ['required', 'integer', 'exists:provinces,id'],
                'ward_id' => ['required', 'integer', 'exists:wards,id'],
                'address_detail' => ['required', 'string', 'max:500'],
                'is_default' => ['boolean'],
            ],
            ['phone.regex' => 'Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.']
        );
        $province = Province::findOrFail($data['province_id']);
        $ward = Ward::where('province_id', $province->id)->findOrFail($data['ward_id']);
        $data['province_name'] = $province->name;
        $data['ward_name'] = $ward->name;
        $data['address'] = collect([$data['address_detail'], $ward->name, $province->name])->filter()->join(', ');

        return $data;
    }

    private function syncCustomerAddress(CustomerAccount $account, CustomerAddress $address): void
    {
        $account->customer->update([
            'province_id' => $address->province_id,
            'ward_id' => $address->ward_id,
            'address_detail' => $address->address_detail,
        ]);
    }

    private function account(Request $request, Company $company, bool $required = true): ?CustomerAccount
    {
        $id = $request->session()->get('storefront_customer_account_id');
        $companyId = $request->session()->get('storefront_company_id');
        $account = $id && (int) $companyId === $company->id
            ? CustomerAccount::with('customer')->where('company_id', $company->id)->where('is_active', true)->find($id) : null;
        if ($required) {
            abort_unless($account, 401, 'Vui lòng đăng nhập tài khoản khách hàng.');
        }

        return $account;
    }

    private function loginSession(Request $request, CustomerAccount $account): void
    {
        $request->session()->put(['storefront_customer_account_id' => $account->id, 'storefront_company_id' => $account->company_id]);
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xác nhận',
            'approved', 'confirmed' => 'Đã xác nhận',
            'partial', 'shipping' => 'Đang giao hàng',
            'completed', 'delivered' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'returned' => 'Đã hoàn hàng',
            default => 'Đang xử lý',
        };
    }

    private function data(CustomerAccount $account): array
    {
        $account->loadMissing('customer');

        return [
            'email' => $account->email,
            'name' => $account->customer->name,
            'phone' => $account->customer->phone,
        ];
    }

    private function enabled(Company $company): void
    {
        abort_unless($company->storefront_enabled, 404);
    }
}
