<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CodeGeneratorService
{
    public function generate(string $modelClass, string $prefix, int $padding = 4): string
    {
        $user = auth()->user();
        $company = $user->company ?? $user->companies()->first();

        if (! $company) {
            throw new \RuntimeException('Không tìm thấy công ty.');
        }

        $number = DB::transaction(function () use ($modelClass, $prefix, $company) {
            DB::table('company_code_sequences')->insertOrIgnore([
                'company_id' => $company->id,
                'model_type' => $modelClass,
                'prefix' => $prefix,
                'current_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('company_code_sequences')
                ->where('company_id', $company->id)
                ->where('model_type', $modelClass)
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->firstOrFail();

            $current = (int) $sequence->current_number;
            if ($current === 0) {
                $lastCode = $modelClass::where('company_id', $company->id)
                    ->where('code', 'like', $prefix.'%')
                    ->orderByDesc('code')
                    ->value('code');
                $current = $lastCode ? (int) preg_replace('/\D/', '', $lastCode) : 0;
            }

            $next = $current + 1;
            DB::table('company_code_sequences')->where('id', $sequence->id)->update([
                'current_number' => $next,
                'updated_at' => now(),
            ]);

            return $next;
        }, 3);

        return $prefix.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
    }
}
