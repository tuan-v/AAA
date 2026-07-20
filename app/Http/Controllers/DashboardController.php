<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * GET /api/dashboard/overview
     * Trả về toàn bộ dữ liệu tổng hợp cho trang Dashboard.
     */
    public function overview(Request $request)
    {
        $companyId = $request->user()->company_id;

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa thuộc công ty nào',
                'data' => null,
                'meta' => null,
            ], 422);
        }

        $data = $this->dashboardService->getOverview($companyId);

        return response()->json([
            'success' => true,
            'message' => 'Lấy dữ liệu dashboard thành công',
            'data' => $data,
            'meta' => null,
        ]);
    }

    public function module(Request $request, string $module)
    {
        if (!in_array($module, ['purchase', 'sale', 'warehouse', 'accountant'], true)) {
            return response()->json(['message' => 'Phân hệ dashboard không hợp lệ.'], 404);
        }

        $companyId = $request->user()->company_id
            ?? $request->user()->companies()->value('companies.id');

        if (!$companyId) {
            return response()->json(['message' => 'Tài khoản chưa thuộc công ty nào.'], 422);
        }

        $data = $this->dashboardService->getModuleOverview((int) $companyId, $module);
        $company = $request->user()->company ?? $request->user()->companies()->first();
        $currency = $company?->default_currency;
        $data['currency'] = $currency ? [
            'code' => $currency->code,
            'symbol' => $currency->symbol,
        ] : ['code' => 'VND', 'symbol' => '₫'];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
