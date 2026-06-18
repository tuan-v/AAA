<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
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

        return $query
            ->latest()
            ->orderByDesc('id')
            ->paginate(5)
            ->through(function ($customer) {

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

                'email' => 'nullable|email',

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
                'adress_detail.required' => 'Địa chỉ chi tiết không được bỏ trống',

                'total_debts.numeric' => 'Công nợ phải là số.',
                'total_debts.min' => 'Công nợ phải lớn hơn hoặc bằng 0.',

                'total_advance.numeric' => 'Tiền ứng trước phải là số.',
                'total_advance.min' => 'Tiền ứng trước phải lớn hơn hoặc bằng 0.',

                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

        $last = Customer::latest('id')->first();

        $validated['code'] =
            'KH' .
            str_pad(
                ($last?->id ?? 0) + 1,
                4,
                '0',
                STR_PAD_LEFT
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

                'email' => 'nullable|email',

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
        $customer = Customer::with('currency')->findOrFail($id);

        return response()->json([
            'id' => $customer->id,
            'code' => $customer->code,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'currency_id' => $customer->currency_id,
            'province_id' => $customer->province_id,
            'ward_id' => $customer->ward_id,
            'address_detail' => $customer->address_detail,
            'opening_debt' => $customer->opening_debt,
            'status' => $customer->status,
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
    public function nextCode()
    {
        $last = Customer::orderBy('id', 'desc')->first();

        $nextId = ($last?->id ?? 0) + 1;

        $code = 'KH' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'code' => $code
        ]);
    }
}
