<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs') || Schema::hasColumn('activity_logs', 'company_id')) return;

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')
                ->constrained('companies')->nullOnDelete();
            $table->index(['company_id', 'created_at']);
        });

        DB::table('activity_logs')->whereNull('company_id')->whereNotNull('user_id')
            ->orderBy('id')->chunkById(500, function ($logs) {
                $companyIds = DB::table('users')
                    ->whereIn('id', $logs->pluck('user_id')->filter()->unique())
                    ->pluck('company_id', 'id');

                foreach ($logs as $log) {
                    $companyId = $companyIds[$log->user_id] ?? null;
                    if ($companyId) {
                        DB::table('activity_logs')->where('id', $log->id)
                            ->update(['company_id' => $companyId]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('activity_logs') || ! Schema::hasColumn('activity_logs', 'company_id')) return;

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
