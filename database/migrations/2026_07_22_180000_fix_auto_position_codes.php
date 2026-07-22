<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('positions')) return;

        $companyIds = DB::table('positions')
            ->where('description', 'Chức vụ trưởng phòng được tạo tự động.')
            ->distinct()
            ->pluck('company_id');

        foreach ($companyIds as $companyId) {
            DB::transaction(function () use ($companyId) {
                $autoPositions = DB::table('positions')
                    ->where('company_id', $companyId)
                    ->where('description', 'Chức vụ trưởng phòng được tạo tự động.')
                    ->orderBy('id')
                    ->get(['id']);

                foreach ($autoPositions as $position) {
                    DB::table('positions')->where('id', $position->id)
                        ->update(['code' => 'TMP-CV-'.$position->id]);
                }

                $used = [];
                foreach (DB::table('positions')->where('company_id', $companyId)->pluck('code') as $code) {
                    if (preg_match('/^CV-(\d+)$/', (string) $code, $matches)) {
                        $used[(int) $matches[1]] = true;
                    }
                }

                $number = 1;
                foreach ($autoPositions as $position) {
                    while (isset($used[$number])) $number++;
                    DB::table('positions')->where('id', $position->id)->update([
                        'code' => 'CV-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                        'updated_at' => now(),
                    ]);
                    $used[$number] = true;
                    $number++;
                }
            });
        }
    }

    public function down(): void
    {
        // Không khôi phục mã sai phụ thuộc ID toàn hệ thống.
    }
};
