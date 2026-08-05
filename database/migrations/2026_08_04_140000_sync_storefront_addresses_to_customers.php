<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customer_accounts')->orderBy('id')->chunkById(200, function ($accounts) {
            foreach ($accounts as $account) {
                $address = DB::table('customer_addresses')
                    ->where('customer_account_id', $account->id)
                    ->orderByDesc('is_default')
                    ->orderByDesc('id')
                    ->first();

                if ($address?->province_id && $address?->ward_id) {
                    DB::table('customers')->where('id', $account->customer_id)->update([
                        'province_id' => $address->province_id,
                        'ward_id' => $address->ward_id,
                        'address_detail' => $address->address_detail,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // Đồng bộ dữ liệu một chiều; không xóa địa chỉ khách hàng khi rollback.
    }
};
