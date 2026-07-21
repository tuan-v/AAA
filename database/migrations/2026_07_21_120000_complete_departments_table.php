<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('code', 50)->nullable()->after('company_id');
            $table->text('description')->nullable()->after('name');
            $table->string('status', 20)->default('active')->after('description');
            $table->foreignId('manager_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        $fallbackCompanyId = DB::table('companies')->orderBy('id')->value('id');
        foreach (DB::table('departments')->orderBy('id')->get() as $department) {
            $companyId = DB::table('users')
                ->where('department_id', $department->id)
                ->whereNotNull('company_id')
                ->value('company_id') ?? $fallbackCompanyId;

            DB::table('departments')->where('id', $department->id)->update([
                'company_id' => $companyId,
                'code' => 'PB-'.str_pad((string) $department->id, 3, '0', STR_PAD_LEFT),
            ]);
        }

        Schema::table('departments', function (Blueprint $table) {
            $table->unique(['company_id', 'code'], 'departments_company_code_unique');
            $table->unique(['company_id', 'name'], 'departments_company_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('departments_company_code_unique');
            $table->dropUnique('departments_company_name_unique');
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'code', 'description', 'status', 'manager_id']);
        });
    }
};
