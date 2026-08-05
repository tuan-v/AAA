<?php

namespace App\Services;

use App\Models\CouponCustomerAssignment;
use App\Models\CouponUsage;
use App\Models\PosCoupon;
use App\Models\SalesOrder;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function eligibility(PosCoupon $coupon, string $channel, float $subtotal, ?int $customerId = null, ?int $excludeOrderId = null): array
    {
        $reason = null;
        if (! $coupon->is_active || $coupon->status !== 'active') {
            $reason = 'Phiếu đang tạm dừng.';
        } elseif (! $coupon->supportsChannel($channel)) {
            $reason = 'Không áp dụng cho kênh bán này.';
        } elseif ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            $reason = 'Phiếu chưa đến thời gian sử dụng.';
        } elseif ($coupon->ends_at && $coupon->ends_at->isPast()) {
            $reason = 'Phiếu đã hết hạn.';
        } elseif ($subtotal < (float) $coupon->minimum_order_amount) {
            $reason = 'Đơn hàng chưa đạt giá trị tối thiểu.';
        } elseif ($coupon->scope === 'personal' && ! $customerId) {
            $reason = 'Vui lòng chọn khách hàng để sử dụng phiếu cá nhân.';
        } elseif ($coupon->scope === 'personal' && ! $coupon->assignments()->where('customer_id', $customerId)->where('status', 'available')->exists()) {
            $reason = 'Phiếu không được cấp cho khách hàng này hoặc đã sử dụng.';
        }

        $activeUsages = $coupon->usages()->whereIn('status', ['reserved', 'redeemed']);
        if ($excludeOrderId) {
            $activeUsages->where('sales_order_id', '!=', $excludeOrderId);
        }
        $activeUsageCount = $activeUsages->count();
        if (! $excludeOrderId) {
            $activeUsageCount = max($activeUsageCount, (int) $coupon->used_count);
        }
        if (! $reason && $coupon->usage_limit !== null && $activeUsageCount >= $coupon->usage_limit) {
            $reason = 'Phiếu đã hết lượt sử dụng.';
        }
        if (! $reason && $customerId && $coupon->usage_limit_per_customer !== null) {
            $customerUsages = $coupon->usages()->where('customer_id', $customerId)->whereIn('status', ['reserved', 'redeemed']);
            if ($excludeOrderId) {
                $customerUsages->where('sales_order_id', '!=', $excludeOrderId);
            }
            if ($customerUsages->count() >= $coupon->usage_limit_per_customer) {
                $reason = 'Khách hàng đã sử dụng hết số lượt cho phiếu này.';
            }
        }

        return ['eligible' => $reason === null, 'reason' => $reason, 'discount' => $reason ? 0.0 : $coupon->discountFor($subtotal)];
    }

    public function resolve(int $companyId, ?string $code, string $channel, float $subtotal, ?int $customerId = null, bool $lock = false, ?int $excludeOrderId = null): array
    {
        if (! $code) {
            return ['coupon' => null, 'discount' => 0.0];
        }
        $query = PosCoupon::query()->where('company_id', $companyId)->where('code', strtoupper(trim($code)));
        if ($lock) {
            $query->lockForUpdate();
        }
        $coupon = $query->first();
        if (! $coupon) {
            throw ValidationException::withMessages(['coupon_code' => 'Không tìm thấy phiếu giảm giá.']);
        }
        $result = $this->eligibility($coupon, $channel, $subtotal, $customerId, $excludeOrderId);
        if (! $result['eligible']) {
            throw ValidationException::withMessages(['coupon_code' => $result['reason']]);
        }

        return ['coupon' => $coupon, 'discount' => $result['discount']];
    }

    public function applyToOrder(SalesOrder $order, ?PosCoupon $coupon, float $discount, string $channel, bool $redeem = true): void
    {
        if (! $coupon) {
            $this->removeFromOrder($order);

            return;
        }
        $order->update(['pos_coupon_id' => $coupon->id, 'discount_amount' => $discount, 'coupon_code_snapshot' => $coupon->code,
            'coupon_name_snapshot' => $coupon->name, 'coupon_type_snapshot' => $coupon->type, 'coupon_value_snapshot' => $coupon->value]);
        $existingUsage = CouponUsage::where('sales_order_id', $order->id)->first();
        $wasRedeemed = $existingUsage?->status === 'redeemed';
        $usage = CouponUsage::updateOrCreate(['sales_order_id' => $order->id], ['company_id' => $order->company_id,
            'coupon_id' => $coupon->id, 'customer_id' => $order->customer_id, 'channel' => $channel,
            'discount_amount' => $discount, 'status' => $redeem ? 'redeemed' : 'reserved',
            'redeemed_at' => $redeem ? now() : null, 'reversed_at' => null]);
        if ($redeem && ! $wasRedeemed) {
            $coupon->increment('used_count');
        }
        if ($redeem) {
            $this->markPersonalAssignment($coupon, $order->customer_id, 'redeemed');
        }
    }

    public function redeemForOrder(SalesOrder $order): void
    {
        $usage = CouponUsage::where('sales_order_id', $order->id)->where('status', 'reserved')->lockForUpdate()->first();
        if (! $usage) {
            return;
        }
        $usage->update(['status' => 'redeemed', 'redeemed_at' => now()]);
        PosCoupon::whereKey($usage->coupon_id)->increment('used_count');
        $this->markPersonalAssignment($usage->coupon, $usage->customer_id, 'redeemed');
    }

    public function removeFromOrder(SalesOrder $order): void
    {
        $this->reverseForOrder($order);
        $order->update(['pos_coupon_id' => null, 'discount_amount' => 0, 'coupon_code_snapshot' => null,
            'coupon_name_snapshot' => null, 'coupon_type_snapshot' => null, 'coupon_value_snapshot' => null]);
    }

    public function reverseForOrder(SalesOrder $order): void
    {
        $usage = CouponUsage::where('sales_order_id', $order->id)->whereIn('status', ['reserved', 'redeemed'])->lockForUpdate()->first();
        if (! $usage) {
            return;
        }
        $wasRedeemed = $usage->status === 'redeemed';
        $usage->update(['status' => 'reversed', 'reversed_at' => now()]);
        if ($wasRedeemed) {
            PosCoupon::whereKey($usage->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
        }
        $this->markPersonalAssignment($usage->coupon, $usage->customer_id, 'available');
    }

    private function markPersonalAssignment(?PosCoupon $coupon, ?int $customerId, string $status): void
    {
        if (! $coupon || $coupon->scope !== 'personal' || ! $customerId) {
            return;
        }
        CouponCustomerAssignment::where('coupon_id', $coupon->id)->where('customer_id', $customerId)
            ->update(['status' => $status, 'redeemed_at' => $status === 'redeemed' ? now() : null]);
    }
}
