<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\CustomerAddress;
use App\Models\Province;
use App\Models\Ward;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontAccountController extends Controller
{
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
                'items.product:id,name,sku,image,unit_id',
                'items.product.unit:id,name',
            ])
            ->firstOrFail();

        $currency = $order->currency ?? $company->default_currency;
        $total = (float) $order->total_amount;
        $paid = (float) $order->paid_amount;
        $paymentStatus = $paid >= $total && $total > 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

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
                    'name' => $order->customer?->name,
                    'phone' => $order->customer?->phone,
                    'email' => $order->customer?->email,
                    'address' => $order->address_detail,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product?->name ?? 'Sản phẩm không còn tồn tại',
                    'sku' => $item->product?->sku,
                    'image' => $item->product?->image ? asset('storage/'.ltrim($item->product->image, '/')) : null,
                    'unit' => $item->product?->unit?->name,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'vat_percent' => (float) $item->vat_percent,
                    'amount' => (float) $item->amount,
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
                'note' => $order->note,
                'cancellation_reason' => $order->cancellation_reason,
                'cancelable' => $order->status === 'pending',
                'timeline' => collect([
                    ['key' => 'placed', 'label' => 'Đã đặt hàng', 'date' => $order->created_at?->format('d/m/Y H:i'), 'done' => true],
                    ['key' => 'confirmed', 'label' => 'Đã xác nhận', 'date' => $order->approved_at?->format('d/m/Y H:i'), 'done' => (bool) $order->approved_at],
                    ['key' => 'shipping', 'label' => 'Đang giao hàng', 'date' => $order->shipping_started_at?->format('d/m/Y H:i'), 'done' => (bool) $order->shipping_started_at],
                    ['key' => 'completed', 'label' => 'Hoàn thành', 'date' => $order->completed_at?->format('d/m/Y H:i'), 'done' => (bool) $order->completed_at],
                ])->values(),
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
                'status' => 'active'
            ]);
        }
        $account = CustomerAccount::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'is_active' => true
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
        $orders = $account->orders()->with('items.product:id,name,image')->where('company_id', $company->id)
            ->latest()->paginate(10)->through(fn($order) => [
                'code' => $order->code,
                'status' => $order->effective_status,
                'status_label' => $this->orderStatusLabel($order->effective_status),
                'cancelable' => $order->status === 'pending',
                'cancellation_reason' => $order->cancellation_reason,
                'total' => (float) $order->total_amount,
                'shipping_method' => $order->shipping_method,
                'tracking_code' => $order->tracking_code,
                'date' => $order->created_at->format('d/m/Y H:i'),
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product?->name,
                    'quantity' => (float) $item->quantity,
                    'price' => (float) $item->unit_price
                ]),
            ]);
        return response()->json($orders);
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
                'is_default' => ['boolean']
            ],
            ['phone.regex' => 'Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.']
        );
        $province = Province::findOrFail($data['province_id']);
        $ward = Ward::where('province_id', $province->id)->findOrFail($data['ward_id']);
        $data['province_name'] = $province->name;
        $data['ward_name'] = $ward->name;
        $data['address'] = collect([$data['address_detail'], $ward->name, $province->name])->filter()->join(', ');
        if ($data['is_default'] ?? false) $account->addresses()->update(['is_default' => false]);
        $address = $account->addresses()->create($data);
        return response()->json(['address' => $address], 201);
    }

    public function destroyAddress(Request $request, Company $company, CustomerAddress $address): JsonResponse
    {
        $account = $this->account($request, $company);
        abort_unless($address->customer_account_id === $account->id, 404);
        $address->delete();
        return response()->json(['message' => 'Đã xóa địa chỉ.']);
    }

    private function account(Request $request, Company $company, bool $required = true): ?CustomerAccount
    {
        $id = $request->session()->get('storefront_customer_account_id');
        $companyId = $request->session()->get('storefront_company_id');
        $account = $id && (int) $companyId === $company->id
            ? CustomerAccount::with('customer')->where('company_id', $company->id)->where('is_active', true)->find($id) : null;
        if ($required) abort_unless($account, 401, 'Vui lòng đăng nhập tài khoản khách hàng.');
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
            'phone' => $account->customer->phone
        ];
    }
    private function enabled(Company $company): void
    {
        abort_unless($company->storefront_enabled, 404);
    }
}
