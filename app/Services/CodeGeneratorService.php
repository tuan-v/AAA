<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CodeGeneratorService
{
    public function generate(
        string $modelClass,
        string $prefix,
        int $padding = 4,
        ?int $companyId = null
    ): string
    {
        if (! $companyId) {
            $user = auth()->user();
            $companyId = $user?->company_id
                ?? $user?->companies()->value('companies.id');
        }

        if (! $companyId) {
            throw new \RuntimeException('Không tìm thấy công ty.');
        }

        $number = DB::transaction(function () use ($modelClass, $prefix, $companyId) {
            DB::table('company_code_sequences')->insertOrIgnore([
                'company_id' => $companyId,
                'model_type' => $modelClass,
                'prefix' => $prefix,
                'current_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('company_code_sequences')
                ->where('company_id', $companyId)
                ->where('model_type', $modelClass)
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->firstOrFail();

            // Sequence có thể thấp hơn dữ liệu thật do seed/import hoặc các luồng
            // tạo mã cũ. Luôn đối chiếu với mã lớn nhất trong đúng công ty.
            $pattern = '/^'.preg_quote($prefix, '/').'(\d+)$/';
            $maxExisting = $modelClass::where('company_id', $companyId)
                ->where('code', 'like', $prefix.'%')
                ->pluck('code')
                ->reduce(function (int $max, mixed $code) use ($pattern) {
                    return preg_match($pattern, (string) $code, $matches)
                        ? max($max, (int) $matches[1])
                        : $max;
                }, 0);

            $next = max((int) $sequence->current_number, $maxExisting) + 1;
            DB::table('company_code_sequences')->where('id', $sequence->id)->update([
                'current_number' => $next,
                'updated_at' => now(),
            ]);

            return $next;
        }, 3);

        return $prefix.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
    }
}
