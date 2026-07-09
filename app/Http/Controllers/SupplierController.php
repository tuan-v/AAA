<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with('currency');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {

                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query
            ->latest()
            ->paginate(5)
            ->through(function ($supplier) {

                $debtEntries = $supplier->debts()->latest()->get();
                $totalReceivable = (float) abs($debtEntries->whereIn('type', ['invoice', 'adjustment'])->sum('amount'));
                $totalPaid = (float) abs($debtEntries->where('type', 'payment')->sum('amount'));
                $currentDebt = (float) $supplier->total_debts + $totalReceivable - $totalPaid;

                return [
                    'id' => $supplier->id,
                    'code' => $supplier->code,
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'email' => $supplier->email,

                    'currency_id' => $supplier->currency_id,
                    'currency' => $supplier->currency,

                    'province_code' => $supplier->province_code,
                    'province_name' => $supplier->province_name,

                    'ward_code' => $supplier->ward_code,
                    'ward_name' => $supplier->ward_name,

                    'address_detail' => $supplier->address_detail,

                    'full_address' => collect([
                        $supplier->address_detail,
                        $supplier->ward_name,
                        $supplier->province_name,
                    ])->filter()->implode(', '),

                    'total_debts' => $supplier->total_debts,
                    'current_debt' => $currentDebt,
                    'total_advance' => $supplier->total_advance,

                    'status' => $supplier->status,
                    'created_at' => $supplier->created_at,
                ];
            });
    }
    public function all()
    {
        return response()->json(
            Supplier::select(
                'id',
                'name',
                'currency_id',
                'code',
                'status'
            )
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
        );
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => [
                'required',

                'regex:/^(0|\+84)[0-9]{9,10}$/'
            ],
            'email' => 'required|email|unique:suppliers,email',

            'currency_id' => 'required|exists:currencies,id',

            'province_code' => 'required',
            'ward_code' => 'required',

            'address_detail' => 'required|string|max:500',

            'total_debts' => 'nullable|numeric|min:0',
            'total_advance' => 'nullable|numeric|min:0',

            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Tên nhà cung cấp không được để trống.',
            'name.max' => 'Tên nhà cung cấp tối đa 255 ký tự.',

            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'phone.required' => 'Số điện thoại không được bỏ trống',

            'email.required' => "Email không được bỏ trống",
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã bị trùng',

            'currency_id.required' => 'Vui lòng chọn tiền tệ.',
            'currency_id.exists' => 'Tiền tệ không tồn tại.',

            // 'province_code.max' => 'Mã tỉnh/thành không hợp lệ.',
            // 'province_name.max' => 'Tên tỉnh/thành tối đa 255 ký tự.',
            'province_code.required' => 'Vui lòng chọn tỉnh',
            'ward_code.required' => 'Vui lòng chọn xã/phường.',
            // 'ward_name.max' => 'Tên phường/xã tối đa 255 ký tự.',

            'address_detail.max' => 'Địa chỉ chi tiết tối đa 500 ký tự.',
            'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống',

            'total_debts.numeric' => 'Công nợ phải là số.',
            'total_debts.min' => 'Công nợ phải lớn hơn hoặc bằng 0.',

            'total_advance.numeric' => 'Tiền ứng trước phải là số.',
            'total_advance.min' => 'Tiền ứng trước phải lớn hơn hoặc bằng 0.',

            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);


        $last = Supplier::latest('id')->first();

        $validated['code'] =
            'NCC' .
            str_pad(
                ($last?->id ?? 0) + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

        return Supplier::create($validated);
    }

    public function show($id)
    {
        return Supplier::findOrFail($id);
    }

    public function detail($id)
    {
        $supplier = Supplier::with([
            'currency',
            'purchaseOrders' => function ($query) {
                $query->latest()->limit(8);
            },
            'debts' => function ($query) {
                $query->latest()->limit(10);
            },
        ])->findOrFail($id);

        $openingDebt = (float) ($supplier->total_debts ?? 0);
        $debtEntries = $supplier->debts()->latest()->get();

        $totalReceivable = (float) abs($debtEntries
            ->whereIn('type', ['invoice', 'adjustment'])
            ->sum('amount'));
        $totalPaid = (float) abs($debtEntries
            ->where('type', 'payment')
            ->sum('amount'));
        $remainingDebt = $openingDebt + $totalReceivable - $totalPaid;

        return response()->json([
            'supplier' => $supplier,
            'debt_summary' => [
                'opening_debt'     => $openingDebt,
                'total_receivable' => abs($totalReceivable),
                'total_paid'       => abs($totalPaid),
                'remaining_debt'   => $remainingDebt,
            ],
            'recent_orders' => $supplier->purchaseOrders,
            'debt_history'  => $debtEntries,
        ]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => [
                'required',
                'regex:/^(0|\+84)[0-9]{9,10}$/'
            ],
            'email' => 'required|email|unique:suppliers,email',

            'currency_id' => 'required|exists:currencies,id',

            'province_code' => 'required',
            // 'province_name' => 'required|string|max:255',

            'ward_code' => 'required',
            // 'ward_name' => 'required|string|max:255',

            'address_detail' => 'required|string|max:500',

            'total_debts' => 'nullable|numeric|min:0',
            'total_advance' => 'nullable|numeric|min:0',

            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Tên nhà cung cấp không được để trống.',
            'name.max' => 'Tên nhà cung cấp tối đa 255 ký tự.',

            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'phone.required' => 'Số điện thoại không được bỏ trống',

            'email.required' => "Email không được bỏ trống",
            'email.email' => 'Email không đúng định dạng.',

            'currency_id.required' => 'Vui lòng chọn tiền tệ.',
            'currency_id.exists' => 'Tiền tệ không tồn tại.',

            // 'province_code.max' => 'Mã tỉnh/thành không hợp lệ.',
            // 'province_name.max' => 'Tên tỉnh/thành tối đa 255 ký tự.',
            'province_code.required' => 'Vui lòng chọn tỉnh',
            'ward_code.required' => 'Vui lòng chọn xã/phường.',
            // 'ward_name.max' => 'Tên phường/xã tối đa 255 ký tự.',

            'address_detail.max' => 'Địa chỉ chi tiết tối đa 500 ký tự.',
            'adress_detail.required' => 'Địa chỉ chi tiết không được bỏ trống',

            'total_debts.numeric' => 'Công nợ phải là số.',
            'total_debts.min' => 'Công nợ phải lớn hơn hoặc bằng 0.',

            'total_advance.numeric' => 'Tiền ứng trước phải là số.',
            'total_advance.min' => 'Tiền ứng trước phải lớn hơn hoặc bằng 0.',

            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function toggleStatus($id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->status =
            $supplier->status === 'active'
            ? 'inactive'
            : 'active';

        $supplier->save();

        return response()->json([
            'status' => $supplier->status
        ]);
    }
}
