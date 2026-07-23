<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private const OVERVIEW_PERMISSION = 'tong_quan.xem';

    private const MODULE_PERMISSIONS = [
        'purchase' => 'don_mua.xem',
        'sale' => 'don_ban.xem',
        'warehouse' => 'kho.xem',
        'accountant' => 'giao_dich.xem',
    ];

    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function landing(Request $request)
    {
        $user = $request->user();

        if ($user->can(self::OVERVIEW_PERMISSION)) {
            return Inertia::render('DashBoard');
        }

        foreach (self::MODULE_PERMISSIONS as $module => $permission) {
            if ($user->can($permission)) {
                return redirect('/'.($module === 'accountant' ? 'accountant' : $module));
            }
        }

        if ($user->can('nhan_su.xem')) {
            return redirect('/user');
        }

        abort(403, 'Tài khoản chưa được cấp quyền xem tổng quan.');
    }

    /**
     * GET /api/dashboard/overview
     * Trả về toàn bộ dữ liệu tổng hợp cho trang Dashboard.
     */
    public function overview(Request $request)
    {
        abort_unless(
            $request->user()->can(self::OVERVIEW_PERMISSION),
            403,
            'Bạn không có quyền xem tổng quan chung.'
        );

        $companyId = $request->user()->company_id;

        if (! $companyId) {
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
            'message' => 'Lấy dữ liệu tổng quan thành công',
            'data' => $data,
            'meta' => null,
        ]);
    }

    public function module(Request $request, string $module)
    {
        if (! in_array($module, ['purchase', 'sale', 'warehouse', 'accountant'], true)) {
            return response()->json(['message' => 'Phân hệ tổng quan không hợp lệ.'], 404);
        }

        abort_unless(
            $request->user()->can(self::MODULE_PERMISSIONS[$module]),
            403,
            'Bạn không có quyền xem tổng quan phân hệ này.'
        );

        $companyId = $request->user()->company_id
            ?? $request->user()->companies()->value('companies.id');

        if (! $companyId) {
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
