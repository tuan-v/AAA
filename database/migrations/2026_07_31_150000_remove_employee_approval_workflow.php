<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('type', 'user')
            ->whereIn('status', ['pending', 'pending_edit'])
            ->update(['status' => 'active']);

        DB::table('users')->where('type', 'user')
            ->whereIn('status', ['rejected_final', 'expired'])
            ->update(['status' => 'blocked']);

        DB::table('users')->where('type', 'user')->update([
            'rejection_reason' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_count' => 0,
            'rejection_type' => null,
            'resubmit_expires_at' => null,
            'last_resubmitted_by' => null,
            'last_resubmitted_at' => null,
        ]);

        DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', ['nhan_su.duyet', 'nhan_su.tu_choi'])
            ->delete();
    }

    public function down(): void
    {
        // Luồng duyệt cũ không được tự động khôi phục để tránh đưa tài khoản
        // đang hoạt động quay lại trạng thái chờ duyệt.
    }
};
