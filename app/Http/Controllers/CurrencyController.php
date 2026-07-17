<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\CurrencyRate;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $query = Currency::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }
        $perPage = min((int) $request->input('per_page', 10), 100);
        return $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function ($currency) {
                $currency->is_used = $currency->isUsed();
                return $currency;
            });
    }
    // public function all()
    // {
    //     return response()->json(
    //         Currency::select('id', 'code', 'name', 'symbol', 'exchange_rate')
    //             ->where('status', 'active') // hoặc 1, tùy kiểu dữ liệu cột status
    //             ->orderBy('code')
    //             ->get()
    //     );
    // }

    public function store(Request $request)
    {
        $company = auth()->user()->companies()->first();

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:20',
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        $validated['company_id'] = $company->id;

        $currency = Currency::create($validated);

        return response()->json([
            'message' => 'Thêm tiền tệ thành công',
            'data' => $currency
        ]);
    }

    public function show(Currency $currency)
    {
        $currency->is_used = $currency->isUsed();

        return response()->json($currency);
    }

    public function update(Request $request, Currency $currency)
    {
        if ($currency->isUsed()) {
            return response()->json([
                'message' => 'Đơn vị tiền tệ đã được sử dụng, không thể chỉnh sửa.'
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:20',
            'exchange_rate' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $currency->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function destroy(Currency $currency)
    {
        if ($currency->isUsed()) {
            return response()->json([
                'message' => 'Đơn vị tiền tệ đã được sử dụng, không thể xóa.'
            ], 422);
        }

        $currency->delete();

        return response()->json([
            'message' => 'Xóa thành công'
        ]);
    }

    public function rates(Currency $currency)
    {
        return $currency
            ->rates()
            ->with('creator')
            ->orderByDesc('effective_date')
            ->get();
    }

    public function storeRate(Request $request, Currency $currency)
    {
        if ($currency->isUsed()) {
            return response()->json([
                'message' => 'Đơn vị tiền tệ đã được sử dụng, không thể thay đổi tỷ giá.'
            ], 422);
        }

        $validated = $request->validate([
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ]);

        $rate = CurrencyRate::updateOrCreate(
            [
                'currency_id' => $currency->id,
                'effective_date' => $validated['effective_date'],
            ],
            [
                'rate' => $validated['rate'],
                'created_by' => auth()->id(),
            ]
        );

        $currency->update([
            'exchange_rate' => $validated['rate']
        ]);

        return response()->json([
            'message' => 'Cập nhật tỷ giá thành công',
            'data' => $rate
        ]);
    }

    public function toggleStatus(Currency $currency)
    {
        $currency->update([
            'is_active' => !$currency->is_active
        ]);

        return response()->json([
            'message' => $currency->is_active
                ? 'Đã kích hoạt tiền tệ'
                : 'Đã khóa tiền tệ'
        ]);
    }
}
