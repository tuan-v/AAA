<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Services\CodeGeneratorService;
use App\Services\CurrencyService;
use App\Services\CompanyCurrencyService;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function __construct(
        protected CurrencyService $currencyService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    private function companyId(): int
    {
        $companyId = auth()->user()->company_id
            ?? auth()->user()->companies()->value('companies.id');
        abort_unless($companyId, 403, 'Tài khoản chưa thuộc công ty nào.');
        return (int) $companyId;
    }
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
        $perPage = min((int) $request->input('per_page', 10), 100);
        return $query
            ->latest()
            ->paginate($perPage)
            ->through(function ($supplier) {

                $debtEntries = $supplier->debts()->latest()->get();
                $totalReceivable = (float) abs($debtEntries->whereIn('type', ['invoice', 'adjustment'])->sum('amount'));
                $totalPaid = (float) abs($debtEntries->where('type', 'payment')->sum('amount'));
                $currentDebt = (float) $supplier->opening_debt_base
                    + $totalReceivable - $totalPaid;
                $companyCurrency = $this->currencyService
                    ->getCompanyCurrency(auth()->user()->company);

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

                    'opening_debt' => $supplier->opening_debt,
                    'opening_debt_base' => $supplier->opening_debt_base,
                    'current_debt' => $currentDebt,
                    'total_advance' => $supplier->total_advance,
                    'opening_advance' => $supplier->opening_advance,
                    'opening_advance_base' => $supplier->opening_advance_base,
                    'note' => $supplier->note,
                    'status' => $supplier->status,
                    'created_at' => $supplier->created_at,
                    'company_currency' => [
                        'id' => optional($companyCurrency)->id,
                        'code' => optional($companyCurrency)->code,
                        'name' => optional($companyCurrency)->name,
                        'symbol' => optional($companyCurrency)->symbol,
                    ],
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
            'email' => [
                'required',
                'email',
                Rule::unique('suppliers', 'email')->where(
                    fn ($query) => $query->where('company_id', $this->companyId())
                ),
            ],

            'currency_id' => 'required|exists:currencies,id',

            'province_code' => 'required',
            'province_name' => 'required|string|max:255',
            'ward_code' => 'required',
            'ward_name' => 'required|string|max:255',

            'address_detail' => 'required|string|max:500',

            'opening_debt' => 'nullable|numeric|min:0',
            'opening_advance' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:2000',
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

            'province_code.required' => 'Vui lòng chọn tỉnh',
            'ward_code.required' => 'Vui lòng chọn xã/phường.',

            'address_detail.max' => 'Địa chỉ chi tiết tối đa 500 ký tự.',
            'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống',

            'opening_debt.numeric' => 'Công nợ phải là số.',
            'opening_debt.min' => 'Công nợ phải lớn hơn hoặc bằng 0.',

            'opening_advance.numeric' => 'Tiền ứng trước phải là số.',
            'openings_advance.min' => 'Tiền ứng trước phải lớn hơn hoặc bằng 0.',
        ]);


        $this->ensureCompanyCurrency((int) $validated['currency_id']);
        $validated = $this->withOpeningBalanceSnapshots($validated);

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
                $query->latest();
            },
        ])->findOrFail($id);

        $openingDebt = (float) ($supplier->opening_debt_base ?? 0);

        $debtEntries = $supplier->debts;

        // Tổng phát sinh phải trả
        $totalPayable = (float) abs(
            $debtEntries
                ->whereIn('type', ['invoice', 'adjustment'])
                ->sum('amount')
        );

        // Đã thanh toán
        $totalPaid = (float) abs(
            $debtEntries
                ->where('type', 'payment')
                ->sum('amount')
        );

        $remainingDebt = $openingDebt + $totalPayable - $totalPaid;
        $companyCurrency = $this->currencyService
            ->getCompanyCurrency(auth()->user()->company);

        return response()->json([
            'company_currency' => $companyCurrency,

            'supplier' => [

                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,

                'currency' => [
                    'id' => optional($supplier->currency)->id,
                    'code' => optional($supplier->currency)->code,
                    'name' => optional($supplier->currency)->name,
                    'symbol' => optional($supplier->currency)->symbol,
                ],

                'opening_debt' => $openingDebt,
                'opening_debt_base' => $openingDebt,
                'opening_debt_original' => (float) $supplier->opening_debt,

                'address_detail' => $supplier->address_detail,

                'province' => [
                    'code' => $supplier->province_code,
                    'name' => $supplier->province_name,
                ],

                'ward' => [
                    'code' => $supplier->ward_code,
                    'name' => $supplier->ward_name,
                ],

                'status' => $supplier->status,
            ],

            'debt_summary' => [

                'opening_debt' => $openingDebt,

                // tên này đúng với SupplierDetail.vue
                'total_payable' => $totalPayable,

                'total_paid' => $totalPaid,

                'remaining_debt' => $remainingDebt,
            ],

            'recent_orders' => $supplier->purchaseOrders->map(function ($order) {

                return [
                    'id' => $order->id,
                    'code' => $order->code,
                    'order_date' => $order->created_at?->toIso8601String(),
                    'created_at' => $order->created_at?->toIso8601String(),
                    'total_amount' => $order->total_amount,
                    'total_amount_base' => round((float) $order->total_amount * (float) ($order->exchange_rate ?: 1), 2),
                    'exchange_rate' => $order->exchange_rate,

                    'currency' => [
                        'code' => optional($order->currency)->code,
                        'symbol' => optional($order->currency)->symbol,
                    ],

                    'status' => $order->status,
                ];
            }),

            'debt_history' => $debtEntries
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($item) {

                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'note' => $item->note,
                        'amount' => $item->amount,
                        'created_at' => $item->created_at,
                    ];
                }),
        ]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => [
                'required',
                'regex:/^(0|\+84)[0-9]{9,10}$/'
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('suppliers', 'email')
                    ->ignore($supplier->id)
                    ->where(fn ($query) => $query->where('company_id', $this->companyId())),
            ],

            'currency_id' => 'required|exists:currencies,id',

            'province_code' => 'required',
            'province_name' => 'required|string|max:255',

            'ward_code' => 'required',
            'ward_name' => 'required|string|max:255',

            'address_detail' => 'required|string|max:500',

            'opening_debt' => 'nullable|numeric|min:0',
            'opening_advance' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:2000',

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

            'opening_debt.numeric' => 'Công nợ phải là số.',
            'opening_debt.min' => 'Công nợ phải lớn hơn hoặc bằng 0.',

            'opening_advance.numeric' => 'Tiền ứng trước phải là số.',
            'opening_advance.min' => 'Tiền ứng trước phải lớn hơn hoặc bằng 0.',

        ]);

        $this->ensureCompanyCurrency((int) $validated['currency_id']);
        $validated = $this->withOpeningBalanceSnapshots($validated);
        $supplier->update($validated);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function destroy($id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())->findOrFail($id);

        if ($supplier->purchaseOrders()->exists()) {
            return response()->json([
                'message' => 'Nhà cung cấp đã phát sinh đơn mua, không thể xóa. Bạn có thể chuyển sang trạng thái khóa.',
            ], 422);
        }

        $supplier->delete();

        return response()->json(['message' => 'Xóa nhà cung cấp thành công.']);
    }

    public function toggleStatus($id)
    {
        $supplier = Supplier::where('company_id', $this->companyId())->findOrFail($id);

        $supplier->status =
            $supplier->status === 'active'
            ? 'inactive'
            : 'active';

        $supplier->save();

        return response()->json([
            'status' => $supplier->status
        ]);
    }
    private function ensureCompanyCurrency(int $currencyId): void
    {
        $company = auth()->user()->company;
        abort_unless($company, 403, 'Tài khoản chưa thuộc công ty nào.');

        if (! $company->currencies()->whereKey($currencyId)->exists()) {
            $company->currencies()->attach($currencyId, ['is_default' => false]);
        }
    }

    private function withOpeningBalanceSnapshots(array $data): array
    {
        $rate = app(CompanyCurrencyService::class)->rate(
            $this->companyId(),
            (int) $data['currency_id'],
            now()
        );
        $data['opening_debt_exchange_rate'] = $rate;
        $data['opening_debt_base'] = round((float) ($data['opening_debt'] ?? 0) * $rate, 2);
        $data['opening_advance_exchange_rate'] = $rate;
        $data['opening_advance_base'] = round((float) ($data['opening_advance'] ?? 0) * $rate, 2);

        return $data;
    }
}
