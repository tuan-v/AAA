<?php

namespace App\Http\Controllers;

use App\Models\PosCoupon;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    private function companyId(Request $request): int
    {
        return (int) $request->user()->company_id;
    }

    public function index(Request $request): JsonResponse
    {
        $query = PosCoupon::with('assignedCustomers:id')->withCount([
            'usages as actual_used_count' => fn ($q) => $q->where('status', 'redeemed'),
            'usages as reserved_count' => fn ($q) => $q->where('status', 'reserved'),
        ])->where('company_id', $this->companyId($request));
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('code', 'like', '%'.$request->search.'%')->orWhere('name', 'like', '%'.$request->search.'%'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('scope')) {
            $query->where('scope', $request->scope);
        }
        if ($request->filled('starts_from')) {
            $query->whereDate('starts_at', '>=', $request->starts_from);
        }
        if ($request->filled('ends_to')) {
            $query->whereDate('ends_at', '<=', $request->ends_to);
        }
        if ($request->filled('from_date')) {
            $query->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $request->from_date));
        }
        if ($request->filled('to_date')) {
            $query->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $request->to_date));
        }

        return response()->json($query->latest()->paginate($request->integer('per_page', 10)));
    }

    public function active(Request $request): JsonResponse
    {
        $channel = $request->string('channel', 'admin')->toString();
        $subtotal = $request->float('subtotal', 0);
        $customerId = $request->integer('customer_id') ?: null;
        $items = PosCoupon::where('company_id', $this->companyId($request))->where('status', 'active')->where('is_active', true)->get()
            ->filter(fn ($coupon) => $coupon->supportsChannel($channel))
            ->map(function ($coupon) use ($channel, $subtotal, $customerId) {
                $result = app(CouponService::class)->eligibility($coupon, $channel, $subtotal, $customerId);

                return [...$coupon->toArray(), 'eligible' => $result['eligible'], 'eligibility_reason' => $result['reason'], 'discount_amount' => $result['discount']];
            })->values();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['scope'] ??= 'public';
        $customerIds = $data['customer_ids'] ?? [];
        unset($data['customer_ids']);
        $data['company_id'] = $this->companyId($request);
        $data['created_by'] = $request->user()->id;
        $data['code'] = $data['code'] ? strtoupper(trim($data['code'])) : $this->nextCode($data['company_id']);
        $data['is_active'] = $data['status'] === 'active';
        $coupon = PosCoupon::create($data);
        $this->syncCustomers($coupon, $customerIds, $request);

        return response()->json(['message' => 'Tạo phiếu giảm giá thành công.', 'data' => $coupon], 201);
    }

    public function update(Request $request, PosCoupon $coupon): JsonResponse
    {
        abort_unless($coupon->company_id === $this->companyId($request), 404);
        $data = $this->validated($request, $coupon);
        $data['scope'] ??= $coupon->scope;
        $customerIds = $data['customer_ids'] ?? $coupon->assignedCustomers()->pluck('customers.id')->all();
        unset($data['customer_ids']);
        if ($coupon->used_count > 0 && ($data['type'] !== $coupon->type || (float) $data['value'] !== (float) $coupon->value)) {
            return response()->json(['message' => 'Phiếu đã được sử dụng; không thể sửa loại hoặc giá trị giảm.'], 422);
        }
        $data['code'] = $data['code'] ? strtoupper(trim($data['code'])) : $coupon->code;
        $data['is_active'] = $data['status'] === 'active';
        $coupon->update($data);
        $this->syncCustomers($coupon, $customerIds, $request);

        return response()->json(['message' => 'Cập nhật phiếu giảm giá thành công.', 'data' => $coupon->fresh()]);
    }

    public function destroy(Request $request, PosCoupon $coupon): JsonResponse
    {
        abort_unless($coupon->company_id === $this->companyId($request), 404);
        if ($coupon->usages()->exists()) {
            return response()->json(['message' => 'Phiếu đã có lịch sử sử dụng; chỉ có thể tạm dừng.'], 422);
        }
        $coupon->delete();

        return response()->json(['message' => 'Đã xóa phiếu giảm giá.']);
    }

    public function usages(Request $request, PosCoupon $coupon): JsonResponse
    {
        abort_unless($coupon->company_id === $this->companyId($request), 404);

        return response()->json($coupon->usages()->with(['order:id,code,status,total_amount', 'customer:id,code,name'])->latest()->paginate(20));
    }

    private function validated(Request $request, ?PosCoupon $coupon = null): array
    {
        $companyId = $this->companyId($request);

        return $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('pos_coupons')->where(fn ($q) => $q->where('company_id', $companyId))->ignore($coupon?->id)],
            'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::in(['fixed', 'percent'])], 'value' => ['required', 'numeric', 'gt:0', Rule::when($request->input('type') === 'percent', ['max:100'])],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'], 'maximum_discount' => ['nullable', 'numeric', 'gt:0'],
            'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'], 'status' => ['required', Rule::in(['draft', 'active', 'paused'])],
            'channels' => ['required', 'array', 'min:1'], 'channels.*' => [Rule::in(['pos', 'web', 'admin'])],
            'scope' => ['sometimes', Rule::in(['public', 'personal'])],
            'customer_ids' => ['exclude_unless:scope,personal', 'required_if:scope,personal', 'array', 'min:1'],
            'customer_ids.*' => ['integer', Rule::exists('customers', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('status', 'active'))],
            'usage_limit' => ['nullable', 'integer', 'min:1'], 'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function nextCode(int $companyId): string
    {
        $number = PosCoupon::where('company_id', $companyId)->where('code', 'like', 'PGG%')->pluck('code')
            ->map(fn (string $code) => ctype_digit(substr($code, 3)) ? (int) substr($code, 3) : 0)->max() + 1;

        return 'PGG'.str_pad((string) max(1, $number), 4, '0', STR_PAD_LEFT);
    }

    private function syncCustomers(PosCoupon $coupon, array $customerIds, Request $request): void
    {
        if ($coupon->scope !== 'personal') {
            $coupon->assignments()->delete();

            return;
        }
        $coupon->assignedCustomers()->sync(collect($customerIds)->mapWithKeys(fn ($id) => [$id => [
            'company_id' => $coupon->company_id, 'status' => 'available', 'created_by' => $request->user()->id,
        ]])->all());
    }
}
