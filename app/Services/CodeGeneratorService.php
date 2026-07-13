<?php

namespace App\Services;

class CodeGeneratorService
{
    public function generate(string $modelClass, string $prefix): string
    {
        $user = auth()->user();

        $company = $user->company ?? $user->companies()->first();

        if (!$company) {
            throw new \Exception('Không tìm thấy công ty.');
        }

        // THAY ĐỔI Ở ĐÂY: Thêm điều kiện where để lọc theo đúng tiền tố (Prefix)
        $last = $modelClass::where('company_id', $company->id)
            ->where('code', 'like', $prefix . '%') // Tìm những mã bắt đầu bằng PN hoặc PX
            ->orderByDesc('code')
            ->first();

        $number = 1;
        if ($last && $last->code) {
            $number = (int) preg_replace('/\D/', '', $last->code) + 1;
        }

        // Bạn đang để padding là 4 số (0001), mình giữ nguyên theo code gốc của bạn nhé
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
