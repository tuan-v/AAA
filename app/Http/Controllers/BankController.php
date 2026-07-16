<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Bank::query();

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where(
                    'code',
                    'like',
                    '%' . $request->search . '%'
                )
                    ->orWhere(
                        'name',
                        'like',
                        '%' . $request->search . '%'
                    )
                    ->orWhere(
                        'short_name',
                        'like',
                        '%' . $request->search . '%'
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }
        $perPage = min((int) $request->input('per_page', 10), 100);
        return $query
            ->latest()
            ->paginate($perPage)
            ->through(function ($bank) {

                $bank->is_used =
                    $bank->isUsed();

                return $bank;
            });
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required',
            'short_name' => 'nullable',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        return DB::transaction(function () use ($request, $validated) {

            $lastBank = Bank::orderBy('id', 'desc')->first();

            $number = $lastBank
                ? ((int) str_replace('NH', '', $lastBank->code) + 1)
                : 1;

            $validated['code'] = 'NH' . str_pad($number, 3, '0', STR_PAD_LEFT);

            $validated['status'] = 1;

            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('banks', 'public');
            }

            $bank = Bank::create($validated);

            return response()->json([
                'message' => 'Tạo ngân hàng thành công',
                'data' => $bank
            ]);
        });
    }

    /**
     * Display the specified resource.
     */
    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required',
            'short_name' => 'nullable',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('logo')) {

            if ($bank->logo) {
                Storage::disk('public')->delete($bank->logo);
            }

            $validated['logo'] = $request->file('logo')->store('banks', 'public');
        }

        $bank->update($validated);

        return response()->json([
            'message' => 'Cập nhật ngân hàng thành công'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function toggleStatus(
        Bank $bank
    ) {

        $bank->update([
            'status' =>
            !$bank->status
        ]);

        return response()->json([
            'message' =>
            'Cập nhật trạng thái thành công'
        ]);
    }
}
