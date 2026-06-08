<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Currency;

class CompanyController extends Controller
{
    public function create()
    {
        $currencies = Currency::all();
        $defaultCurrency = Currency::where('is_default', 1)->first();

        return Inertia::render('Company/Create', [
            'currencies' => $currencies,
            'defaultCurrencyId' => $defaultCurrency?->id,
        ]);
        // return Inertia::render('Company/Create');
    }
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'tax_code' => 'required',
                'phone' => 'required|regex:/^(0)[0-9]{9,10}$/|numeric',
                'email' => 'required|email',
                'address' => 'required',
                'currency_id' => 'required|exists:currencies,id',
            ],
            [
                'name.required' => 'Tên công ty không được để trống',
                'tax_code.required' => 'Mã số thuế không được để trống',
                'email.required' => 'Email không được để trống',
                'email.email' => 'Email không hợp lệ',
                'phone.required' => 'Số điện thoại không được để trống',
                'phone.regex' => 'Số điện thoại không hợp lệ',
                'phone.numeric' => 'Số điện thoại phải là số',
                'address.required' => 'Địa chỉ không được để trống',
                'currency_id.required' => 'Vui lòng chọn loại tiền tệ',
            ]
        );

        $company = Company::create([
            'name' => $request->name,
            'tax_code' => $request->tax_code,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'owner_id' => auth()->id(),
            'currency_id' => $request->currency_id,
        ]);

        $user = auth()->user();
        $user->companies()->attach($company->id);

        // cập nhật công ty hiện tại
        $user->update([
            'company_id' => $company->id,
            'currency_id' => $request->currency_id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // gán quyền admin công ty
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo công ty thành công',
        ]);
    }
}
