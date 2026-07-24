<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $duplicate = DB::table('provinces')->where('code', '01')->first();

            if (! $duplicate) {
                return;
            }

            $canonical = DB::table('provinces')->where('code', '1')->first();

            if (! $canonical) {
                DB::table('provinces')->where('id', $duplicate->id)->update([
                    'code' => '1',
                    'updated_at' => now(),
                ]);

                return;
            }

            DB::table('wards')
                ->where('province_id', $duplicate->id)
                ->get(['id', 'code'])
                ->each(function ($ward): void {
                    DB::table('warehouses')
                        ->where('ward_code', $ward->code)
                        ->update(['ward_code' => (string) $ward->id, 'updated_at' => now()]);
                });

            DB::table('wards')
                ->where('province_id', $duplicate->id)
                ->update(['province_id' => $canonical->id, 'updated_at' => now()]);

            DB::table('addresses')
                ->where('province_id', $duplicate->id)
                ->update(['province_id' => $canonical->id, 'updated_at' => now()]);

            DB::table('customers')
                ->where('province_id', $duplicate->id)
                ->update(['province_id' => $canonical->id, 'updated_at' => now()]);

            DB::table('suppliers')
                ->where('province_code', '01')
                ->update(['province_code' => '1', 'updated_at' => now()]);

            DB::table('warehouses')
                ->whereIn('province_code', ['01', (string) $duplicate->id])
                ->update(['province_code' => (string) $canonical->id, 'updated_at' => now()]);

            DB::table('provinces')->where('id', $duplicate->id)->delete();
        });
    }

    public function down(): void
    {
        // Không tách lại dữ liệu đã hợp nhất để tránh tái tạo tỉnh trùng.
    }
};
