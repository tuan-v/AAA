<?php

namespace App\Http\Controllers;

use App\Helpers\InvoiceHelper;
use App\Helpers\MoneyHelper;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected int|null $companyId = null;
    protected int|null $currentUserId = null;
    protected \Illuminate\Support\Carbon $currentDateTime;

    public function __construct()
    {
        $currentUser = Auth::user();

        if ($currentUser) {
            $this->companyId = $currentUser->company_id;
            $this->currentUserId = $currentUser->id;
        }

        $this->currentDateTime = now('Asia/Ho_Chi_Minh');
    }

    /**
     * Trả về JSON response chuẩn.
     */
    protected function sendResponse(mixed $data, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json($data, $status);
    }

    /**
     * Parse date range từ string "date1,date2" hoặc array [date1, date2].
     *
     * @return array{0: string|null, 1: string|null}
     */
    protected function parseDateRange(string|array|null $dateRange): array
    {
        if (is_array($dateRange)) {
            return [$dateRange[0] ?? null, $dateRange[1] ?? null];
        }

        if (is_string($dateRange)) {
            $parts = explode(',', $dateRange);
            return [$parts[0] ?? null, $parts[1] ?? null];
        }

        return [null, null];
    }

    // ─── Delegates giữ backward-compatibility ─────────────────────────────────

    /**
     * @deprecated Dùng MoneyHelper::strip() trực tiếp.
     */
    protected function removeMoneyFormat(mixed $value): string
    {
        return MoneyHelper::strip($value);
    }

    /**
     * @deprecated Dùng InvoiceHelper::generateUniqueNumber() trực tiếp.
     */
    protected function generateUniqueInvoiceNumber(): string
    {
        return InvoiceHelper::generateUniqueNumber();
    }

    /**
     * @deprecated Dùng MoneyHelper::calculateAmount() trực tiếp.
     */
    protected function calculatorAmount(
        float|int $price,
        float|int $quantity,
        float|int $vat,
        float|int $discount
    ): float|int {
        return MoneyHelper::calculateAmount($price, $quantity, $vat, $discount)['amount'];
    }
}
