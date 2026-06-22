<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\User;
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
        $defaultCurrency = Currency::where('is_default', 1)->first();

        return Inertia::render('Company/Create', [
            'currencies' => $currencies,
            'defaultCurrencyId' => $defaultCurrency?->id,
        ]);
        // return Inertia::render('Company/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'tax_code' => 'required',
            'phone' => 'required|regex:/^(0)[0-9]{9,10}$/|numeric',
            'email' => 'required|email',
            'currency_id' => 'required|exists:currencies,id',
            'address_detail' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'ward_id' => 'required|exists:wards,id',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

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
                    'name' => 'Chuyển khoản',
                    'type' => 'transfer',
                ],
            ]);

            // 2. attach currency
            $company->currencies()->attach($request->currency_id, [
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. attach user
            $user = auth()->user();

            $user->companies()->attach($company->id);

            $user->update([
                'company_id' => $company->id,
                'currency_id' => $request->currency_id,
                'status' => User::STATUS_ACTIVE,
            ]);

            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }

            return response()->json([
                'success' => true,
                'message' => 'Tạo công ty thành công',
            ]);
        });
    }
}
