<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CodReconciliation;
use App\Models\SalesOrder;
use App\Models\ShippingPartner;
use App\Services\CodReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CodReconciliationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $companyId = (int) auth()->user()->company_id;
        $pending = SalesOrder::with(['customer:id,name,phone', 'currency:id,code,symbol', 'shippingPartner:id,name'])
            ->where('company_id', $companyId)->where('sales_channel', 'storefront')->where('payment_method', 'cod')
            ->where('status', 'completed')->where('cod_status', 'collected')
            ->orderBy('cod_collected_at')->get()->map(fn ($order) => [
                'id' => $order->id, 'code' => $order->code, 'customer' => $order->customer?->name,
                'phone' => $order->customer?->phone, 'tracking_code' => $order->tracking_code,
                'cod_amount' => (float) $order->cod_amount,
                'customer_shipping_fee' => (float) $order->shipping_fee,
                'shipping_fee' => (float) $order->carrier_shipping_fee,
                'service_fee' => (float) $order->carrier_service_fee,
                'insurance_fee' => (float) $order->carrier_insurance_fee,
                'collected_at' => $order->cod_collected_at?->format('d/m/Y H:i'),
                'currency' => $order->currency, 'partner' => $order->shippingPartner,
            ]);
        $history = CodReconciliation::with(['partner:id,name', 'account:id,name,code', 'transaction:id,code'])
            ->where('company_id', $companyId)->latest('reconciliation_date')->latest('id')->paginate(15);

        return response()->json([
            'pending' => $pending,
            'history' => $history,
            'partners' => ShippingPartner::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'accounts' => Account::with('currency:id,code,symbol')->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'summary' => [
                'pending_orders' => $pending->count(),
                'pending_amount' => round((float) $pending->sum('cod_amount'), 2),
                'reconciled_amount' => (float) CodReconciliation::where('company_id', $companyId)->sum('received_amount'),
            ],
        ]);
    }

    public function show(CodReconciliation $reconciliation): JsonResponse
    {
        abort_unless($reconciliation->company_id === auth()->user()->company_id, 404);

        return response()->json($reconciliation->load(['partner', 'account.currency', 'transaction', 'items.order.customer']));
    }

    public function store(Request $request, CodReconciliationService $service): JsonResponse
    {
        $companyId = (int) auth()->user()->company_id;
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'distinct'],
            'shipping_partner_id' => ['required', Rule::exists('shipping_partners', 'id')->where('company_id', $companyId)],
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('company_id', $companyId)],
            'reconciliation_date' => ['required', 'date', 'before_or_equal:today'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'insurance_fee' => ['nullable', 'numeric', 'min:0'],
            'adjustment_amount' => ['nullable', 'numeric'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $item = $service->reconcile($data, $companyId, (int) auth()->id());

        return response()->json(['message' => 'Đối soát COD thành công.', 'data' => $item], 201);
    }

    public function storePartner(Request $request): JsonResponse
    {
        $companyId = (int) auth()->user()->company_id;
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('shipping_partners')->where('company_id', $companyId)],
            'name' => ['required', 'string', 'max:150'], 'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'tracking_url_template' => ['nullable', 'string', 'max:500'],
        ]);

        return response()->json(['data' => ShippingPartner::create([...$data, 'company_id' => $companyId])], 201);
    }
}
