<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('currency');

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }
        $perPage = min((int) $request->input('per_page', 5), 100);
        return $query
            ->latest()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function ($customer) {

                $debtEntries = $customer->debts()->latest()->get();
                $totalReceivable = (float) abs($debtEntries->whereIn('type', ['sale', 'refund'])->sum('amount'));
                $totalPaid = (float) abs($debtEntries->where('type', 'payment')->sum('amount'));
                $currentDebt = (float) $customer->opening_debt + $totalReceivable - $totalPaid;

                return [
                    'id' => $customer->id,

                    'code' => $customer->code,

                    'name' => $customer->name,

                    'phone' => $customer->phone,

                    'email' => $customer->email,

                    'currency_id' => $customer->currency_id,

                    'currency' => $customer->currency,

                    'province_id' => $customer->province_id,

                    'ward_id' => $customer->ward_id,

                    'address_detail' => $customer->address_detail,

                    'opening_debt' => $customer->opening_debt,
                    'current_debt' => $currentDebt,

                    'status' => $customer->status,

                    'created_at' => $customer->created_at,
                ];
            });
    }
    public function all()
    {
        return response()->json(

            Customer::select(
                'id',
                'code',
                'name',
                'currency_id',
                'province_id',
                'ward_id',
                'address_detail',
            )
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
        );
    }
    public function store(Request $request)
    {
        $validated = $request->validate(
            [

                'name' => 'required|max:255',

                'phone' => [
                    'required',
                    'regex:/^(0|\+84)[0-9]{9,10}$/'
                ],

                'email' => 'required|email',

                'currency_id' => 'required|exists:currencies,id',

                'province_id' => 'required',

                'ward_id' => 'required',

                'address_detail' => 'required|max:500',

                'opening_debt' => 'nullable|numeric|min:0',
            ],
            [
                'name.required' => 'Tên khách hàng không được để trống.',
                'name.max' => 'Tên tối đa 255 ký tự.',

                'phone.regex' => 'Số điện thoại không đúng định dạng.',
                'phone.required' => 'Số điện thoại không được bỏ trống',

                'email.required' => "Email không được bỏ trống",
                'email.email' => 'Email không đúng định dạng.',

                'currency_id.required' => 'Vui lòng chọn tiền tệ.',
                'currency_id.exists' => 'Tiền tệ không tồn tại.',

                'province_id.required' => 'Vui lòng chọn tỉnh',
                'ward_id.required' => 'Vui lòng chọn xã/phường.',

                'address_detail.max' => 'Địa chỉ chi tiết tối đa 500 ký tự.',
                'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống',

                'total_debts.numeric' => 'Công nợ phải là số.',
                'total_debts.min' => 'Công nợ phải lớn hơn hoặc bằng 0.',

                'total_advance.numeric' => 'Tiền ứng trước phải là số.',
                'total_advance.min' => 'Tiền ứng trước phải lớn hơn hoặc bằng 0.',

                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

        return Customer::create($validated);
    }
    public function update(
        Request $request,
        $id
    ) {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate(
            [

                'name' => 'required|max:255',

                'phone' => [
                    'required',
                    'regex:/^(0|\+84)[0-9]{9,10}$/'
                ],

                'email' => 'required|email',

                'currency_id' => 'required|exists:currencies,id',

                'province_id' => 'required',

                'ward_id' => 'required',

                'address_detail' => 'required|max:500',

                'opening_debt' => 'nullable|numeric|min:0',

                'status' => 'required|in:active,inactive',

            ],
            [
                'name.required' => 'Tên khách hàng không được để trống.',
                'name.max' => 'Tên tối đa 255 ký tự.',

                'phone.regex' => 'Số điện thoại không đúng định dạng.',
                'phone.required' => 'Số điện thoại không được bỏ trống',

                'email.required' => "Email không được bỏ trống",
                'email.email' => 'Email không đúng định dạng.',

                'currency_id.required' => 'Vui lòng chọn tiền tệ.',
                'currency_id.exists' => 'Tiền tệ không tồn tại.',

                'province_id.required' => 'Vui lòng chọn tỉnh',
                'ward_id.required' => 'Vui lòng chọn xã/phường.',

                'address_detail.max' => 'Địa chỉ chi tiết tối đa 500 ký tự.',
                'adress_detail.required' => 'Địa chỉ chi tiết không được bỏ trống',

                'total_debts.numeric' => 'Công nợ phải là số.',
                'total_debts.min' => 'Công nợ phải lớn hơn hoặc bằng 0.',

                'total_advance.numeric' => 'Tiền ứng trước phải là số.',
                'total_advance.min' => 'Tiền ứng trước phải lớn hơn hoặc bằng 0.',

                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

        $customer->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }
    public function show($id)
    {
        $customer = Customer::with(['orders' => function ($q) {
            $q->latest()->limit(10);
        }, 'debts' => function ($q) {
            $q->latest()->limit(15);
        }])
            ->findOrFail($id);

        // Tính toán công nợ
        $totalDebt = $customer->debts->sum('amount'); // tùy theo logic của bạn
        $paidAmount = $customer->debts->where('type', 'payment')->sum('amount');
        $remaining = $customer->opening_balance + $totalDebt - $paidAmount;

        return response()->json([
            'customer' => $customer,
            'debt_summary' => [
                'total_debt' => $totalDebt,
                'paid' => $paidAmount,
                'remaining' => $remaining,
                'opening_balance' => $customer->opening_balance,
            ],
            'recent_orders' => $customer->orders->map(fn ($order) => [
                'id' => $order->id,
                'code' => $order->code,
                'order_date' => $order->created_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
                'total_amount' => $order->total_amount,
                'status' => $order->status,
            ]),
            'debt_history' => $customer->debts,
        ]);
    }
    public function detail($id)
    {
        $customer = Customer::with([
            'currency',
            'province',
            'ward',
            'orders' => function ($query) {
                $query->latest()->limit(8);
            },
            'debts' => function ($query) {
                $query->latest()->limit(10);
            },
            'payments' => function ($query) {
                $query->latest()->limit(10);
            }
        ])->findOrFail($id);

        $openingDebt = (float) $customer->opening_debt;
        $debtEntries = $customer->debts()->latest()->get();

        $totalReceivable = (float) abs($debtEntries
            ->whereIn('type', ['sale', 'refund'])
            ->sum('amount'));
        $totalPaid = (float) abs($debtEntries
            ->where('type', 'payment')
            ->sum('amount'));
        $remainingDebt = $openingDebt + $totalReceivable - $totalPaid;

        return response()->json([
            'customer' => $customer,
            'debt_summary' => [
                'opening_debt'     => $openingDebt,
                'total_receivable' => abs($totalReceivable),
                'total_paid'       => abs($totalPaid),
                'remaining_debt'   => $remainingDebt,
            ],
            'recent_orders' => $customer->orders->map(fn ($order) => [
                'id' => $order->id,
                'code' => $order->code,
                'order_date' => $order->created_at?->toIso8601String(),
                'created_at' => $order->created_at?->toIso8601String(),
                'total_amount' => $order->total_amount,
                'status' => $order->status,
            ]),
            'debt_history'  => $debtEntries,
            'payments'      => $customer->payments,
        ]);
    }
    public function createQuickOrder(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $order = SalesOrder::create([
            'company_id'   => $customer->company_id,
            'customer_id'  => $customer->id,
            'code'         => 'SO' . date('YmdHi') . rand(10, 99),
            'order_date'   => now(),
            'status'       => 'draft',
            'total_amount' => 0,
            // thêm các field khác nếu cần
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đơn hàng mới đã được tạo thành công!',
            'order_id' => $order->id,
            'redirect_url' => "/sale/orders/{$order->id}/edit"   // điều chỉnh theo route Vue của bạn
        ]);
    }
    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);

        $customer->status =
            $customer->status === 'active'
            ? 'inactive'
            : 'active';

        $customer->save();

        return response()->json([
            'status' => $customer->status
        ]);
    }
}
