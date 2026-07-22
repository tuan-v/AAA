<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('companies') || ! Schema::hasTable('users')) return;

        // Chủ công ty/Giám đốc điều hành ở cấp công ty, không thuộc một phòng ban cụ thể.
        $ownerIds = DB::table('companies')->whereNotNull('owner_id')->pluck('owner_id');
        if ($ownerIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $ownerIds)
                ->update(['department_id' => null, 'position_id' => null]);
        }

        if (! Schema::hasTable('departments') || ! Schema::hasTable('positions')) return;

        DB::table('departments')->orderBy('id')->chunkById(200, function ($departments) {
            foreach ($departments as $department) {
                $position = DB::table('positions')
                    ->where('company_id', $department->company_id)
                    ->where('department_id', $department->id)
                    ->where(function ($query) {
                        $query->where('description', 'Chức vụ trưởng phòng được tạo tự động.')
                            ->orWhere('name', 'like', 'Trưởng phòng%');
                    })
                    ->orderBy('id')
                    ->first();

                if (! $position) continue;

                $ownerId = DB::table('companies')->where('id', $department->company_id)->value('owner_id');
                $regularManagerId = $department->manager_id && (int) $department->manager_id !== (int) $ownerId
                    ? (int) $department->manager_id
                    : null;

                DB::table('users')
                    ->where('company_id', $department->company_id)
                    ->where('position_id', $position->id)
                    ->when($regularManagerId, fn ($query) => $query->where('id', '!=', $regularManagerId))
                    ->update(['department_id' => null, 'position_id' => null]);

                if ($regularManagerId) {
                    DB::table('users')->where('id', $regularManagerId)
                        ->where('company_id', $department->company_id)
                        ->update(['department_id' => $department->id, 'position_id' => $position->id]);
                }
            }
        });
    }

    public function down(): void
    {
        // Không thể khôi phục an toàn các phân công sai trước khi chuẩn hóa.
    }
};
