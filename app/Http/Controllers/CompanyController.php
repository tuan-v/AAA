<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Inertia\Inertia;
use App\Models\Currency;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function create()
    {
        $currencies = Currency::all();
        // is_default thuộc bảng pivot companies_has_currencies, không thuộc currencies.
        // Khi chưa tạo công ty, ưu tiên VND làm lựa chọn ban đầu.
        $defaultCurrency = Currency::where('code', 'VND')->first()
            ?? $currencies->first();

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
                'currency_id' => 'required|exists:currencies,id',
                'address_detail' => 'required|string|max:255',
                'province_id' => 'required|exists:provinces,id',
                'ward_id' => 'required|exists:wards,id',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ],
            [
                'name.required' => 'Tên công ty không được để trống',
                'tax_code.required' => 'Mã số thuế không được để trống',
                'phone.required' => 'Số điện thoại không được để trống',
                'phone.regex' => 'Số điện thoại không hợp lệ',
                'phone.numeric' => 'Số điện thoại không hợp lệ',
                'email.required' => 'Email không được để trống',
                'email.email' => 'Email không hợp lệ',
                'currency_id.required' => 'Đơn vị tiền tệ không được để trống',
                'currency_id.exists' => 'Đơn vị tiền tệ không hợp lệ',
                'address_detail.required' => 'Địa chỉ không được để trống',
                'address_detail.string' => 'Địa chỉ không hợp lệ',
                'address_detail.max' => 'Địa chỉ không được vượt quá 255 ký tự',
                'province_id.required' => 'Tỉnh/Thành phố không được để trống',
                'province_id.exists' => 'Tỉnh/Thành phố không hợp lệ',
                'ward_id.required' => 'Phường/Xã không được để trống',
                'ward_id.exists' => 'Phường/Xã không hợp lệ',
                'logo.image' => 'Ảnh đại diện phải là định dạng ảnh',
                'logo.mimes' => 'Ảnh đại diện phải có định dạng jpg, jpeg, hoặc png',
                'logo.max' => 'Ảnh đại diện không được vượt quá 2MB',
            ]
        );

        return DB::transaction(function () use ($request) {

            $logoPath = null;

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('companies', 'public');
            }

            $province = Province::find($request->province_id);
            $ward = Ward::find($request->ward_id);

            $company = Company::create([
                'name' => $request->name,
                'tax_code' => $request->tax_code,
                'phone' => $request->phone,
                'email' => $request->email,
                'currency_id' => $request->currency_id,
                'owner_id' => auth()->id(),
                'logo' => $logoPath,
                'address' => $request->address_detail . ', ' . $ward?->name . ', ' . $province?->name,
            ]);

            // 1. seed transaction categories (FIX ĐÚNG NGHIỆP VỤ)
            $company->transactionCategories()->createMany([
                [
                    'code' => 'THU_KH',
                    'name' => 'Thu tiền khách hàng',
                    'type' => 'income',
                ],
                [
                    'code' => 'THU_KHAC',
                    'name' => 'Thu khác',
                    'type' => 'income',
                ],
                [
                    'code' => 'CHI_NCC',
                    'name' => 'Thanh toán nhà cung cấp',
                    'type' => 'expense',
                ],
                [
                    'code' => 'CHI_KHAC',
                    'name' => 'Chi khác',
                    'type' => 'expense',
                ],
                [
                    'code' => 'CHUYEN_KHOAN',
                    'name' => 'Chuyển tiền nội bộ',
                    'type' => 'transfer',
                ],
                [
                    'code' => 'TAM_UNG_NCC',
                    'name' => 'Tạm ứng nhà cung cấp',
                    'type' => 'expense'
                ],
                [
                    'code' => 'HOAN_TAM_UNG_NCC',
                    'name' => 'Nhà cung cấp hoàn tạm ứng',
                    'type' => 'income'
                ]
            ]);

            // 2. attach currency
            $company->currencies()->attach($request->currency_id, [
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. attach user
            $user = auth()->user();

            $directorRole = Role::updateOrCreate(
                ['name' => 'Giám đốc', 'guard_name' => 'web'],
                [
                    'company_id' => null,
                    'type' => 'system',
                    'hierarchy_level' => 90,
                    'is_protected' => false,
                    'description' => 'Giám đốc - toàn quyền quản trị doanh nghiệp',
                ]
            );

            $user->companies()->attach($company->id);

            $user->update([
                'company_id' => $company->id,
                'currency_id' => $request->currency_id,
                'status' => User::STATUS_ACTIVE,
            ]);

            if (!$user->hasRole($directorRole)) {
                $user->assignRole($directorRole);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tạo công ty thành công',
            ]);
        });
    }
}
