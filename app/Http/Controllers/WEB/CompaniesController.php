<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompaniesController extends Controller
{
    public function index()
    {
        $company = Company::where('user_id', $this->currentUserId)
            ->with([
                'currency',
                'departments' => fn($q) => $q->with(['positions'])->active(),
                'departments.users' => fn($q) => $q->select('users.id', 'users.name', 'users.email', 'users.avatar', 'users.phone'),
            ])
            ->first();

        return Inertia::render('Main/Companies/Index', [
            'company' => $company,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'tax_number' => 'nullable|string|max:20|unique:companies,tax_number',
                'phone' => 'required|string|max:15',
                'email' => ['required', 'email:rfc,dns', 'regex:/^[\w\.\-]+@([\w\-]+\.)+[a-zA-Z]{2,}$/', 'max:255'],
                'address' => 'required|string|max:500',
                'currency' => 'required|array',
                'currency.code' => 'required|string',
                'currency.symbol' => 'required|string',
                'currency.name' => 'required|string',
            ],
            [

                'name.required' => 'Tên công ty là bắt buộc.',
                'name.string' => 'Tên công ty phải là chuỗi ký tự.',
                'name.max' => 'Tên công ty không được vượt quá 255 ký tự.',

                'tax_number.string' => 'Mã số thuế phải là chuỗi ký tự.',
                'tax_number.max' => 'Mã số thuế không được vượt quá 20 ký tự.',
                'tax_number.unique' => 'Mã số thuế này đã tồn tại.',

                'phone.required' => 'Số điện thoại là bắt buộc.',
                'phone.string' => 'Số điện thoại phải là chuỗi ký tự.',
                'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự.',

                'email.required' => 'Email công ty là bắt buộc.',
                'email.email' => 'Email không đúng định dạng.',
                'email.regex' => 'Email không đúng định dạng.',
                'email.max' => 'Email không được vượt quá 255 ký tự.',

                'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',

                'status.required' => 'Trạng thái là bắt buộc.',
                'status.in' => 'Trạng thái không hợp lệ.',
                'currency.required' => 'Đơn vị tiền tệ là bắt buộc.',
                'currency.object' => 'Đơn vị tiền tệ không hợp lệ.',
                'currency.code.required' => 'Mã tiền tệ là bắt buộc.',
                'currency.code.string' => 'Mã tiền tệ phải là chuỗi ký tự.',
                'currency.symbol.required' => 'Ký hiệu tiền tệ là bắt buộc.',
                'currency.symbol.string' => 'Ký hiệu tiền tệ phải là chuỗi ký tự.',
                'currency.name.required' => 'Loại tiền tệ là bắt buộc.',
                'currency.name.in' => 'Loại tiền tệ không hợp lệ.',
            ]
        );
        $data = $request->only([
            'name',
            'tax_number',
            'phone',
            'email',
            'address',
        ]);
        $currency = $request->input('currency');
        $user = Auth::user();
        $data['short_name'] = !isset($request->short_name) ? $validated['name'] : $request->short_name;
        $data['config'] = !isset($request->config) ? "{}" : $request->config;
        $data['plan_id'] = !isset($request->plan_id) ? 0 : $request->plan_id;
        $data['start_time_plan'] = !isset($request->start_time_plan) ? date('d-m-Y') : $request->start_time_plan;
        $data['end_time_plan'] = !isset($request->end_time_plan) ? date('d-m-Y') : $request->end_time_plan;
        $data['default_currency'] = $currency['code'];
        $data['user_id'] = $user->id;
        DB::beginTransaction();
        try {
            $company = Company::create($data);
            if ($company) {
                $currency['company_id'] = $company->id;
                $currency['is_default'] = 1;
                Currency::create($currency);
            }
            UserCompany::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'status' => 'active',
            ]);
            if (!$user->company_id) {
                $user->update(['company_id' => $company->id]);
            }
            DB::commit();
            return redirect()->back()->with('success', 'Thêm công ty thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Lỗi khi thêm mới công ty: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'tax_number' => 'nullable|string|max:20|unique:companies,tax_number,' . $company->id,
                'phone' => 'required|string|max:15',
                'email' => ['required', 'email:rfc,dns', 'regex:/^[\w\.\-]+@([\w\-]+\.)+[a-zA-Z]{2,}$/', 'max:255'],
                'address' => 'nullable|string|max:500',
            ],
            [
                'name.required' => 'Tên công ty là bắt buộc.',
                'name.string' => 'Tên công ty phải là chuỗi ký tự.',
                'name.max' => 'Tên công ty không được vượt quá 255 ký tự.',

                'tax_number.string' => 'Mã số thuế phải là chuỗi ký tự.',
                'tax_number.max' => 'Mã số thuế không được vượt quá 20 ký tự.',
                'tax_number.unique' => 'Mã số thuế này đã tồn tại.',

                'phone.required' => 'Số điện thoại là bắt buộc.',
                'phone.string' => 'Số điện thoại phải là chuỗi ký tự.',
                'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự.',

                'email.required' => 'Email công ty là bắt buộc.',
                'email.email' => 'Email không đúng định dạng.',
                'email.max' => 'Email không được vượt quá 255 ký tự.',
                'email.regex' => 'Email không đúng định dạng.',

                'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',

                'status.required' => 'Trạng thái là bắt buộc.',
                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

        // $validated['status'] = $validated['status'] === 'active' ? 'active' : 'inactive';
        $validated['user_id'] = $company->user_id;

        $validated['short_name'] = $request->short_name ?? $company->short_name;
        $validated['config'] = $request->config ?? $company->config;
        $validated['plan_id'] = $request->plan_id ?? $company->plan_id;
        $validated['start_time_plan'] = $request->start_time_plan ?? $company->start_time_plan;
        $validated['end_time_plan'] = $request->end_time_plan ?? $company->end_time_plan;
        $validated['user_id'] = Auth::id();

        $company->update($validated);

        return redirect()->back()->with('success', 'Cập nhật công ty thành công!');
    }
}
