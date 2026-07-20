<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm ràng buộc unique theo [company_id, name] cho categories và products.
 *
 * Bản idempotent: kiểm tra index đã tồn tại chưa (qua information_schema,
 * không phụ thuộc doctrine/dbal) trước khi ALTER, để tránh lỗi
 * "Duplicate key name" khi migration từng chạy dở dang lần trước
 * (MySQL không rollback được DDL, nên ALTER TABLE trước đó có thể đã
 * commit thật dù migration bị đánh dấu là chưa chạy xong).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->guardAgainstDuplicateNames('categories');
        $this->guardAgainstDuplicateNames('products');

        if (! $this->indexExists('categories', 'categories_company_name_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique(['company_id', 'name'], 'categories_company_name_unique');
            });
        }

        if (! $this->indexExists('products', 'products_company_name_unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unique(['company_id', 'name'], 'products_company_name_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('products', 'products_company_name_unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_company_name_unique');
            });
        }

        if ($this->indexExists('categories', 'categories_company_name_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique('categories_company_name_unique');
            });
        }
    }

    /**
     * Kiểm tra index đã tồn tại trên bảng chưa (MySQL only, dùng information_schema
     * thay vì doctrine/dbal để không phụ thuộc package ngoài).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => $index->name === $indexName);
        }

        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            "SELECT COUNT(*) as total
             FROM information_schema.STATISTICS
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?",
            [$database, $table, $indexName]
        );

        return $result && $result->total > 0;
    }

    /**
     * Nếu bảng đang có dữ liệu trùng (company_id, name), việc thêm unique index
     * sẽ luôn lỗi "Duplicate entry". Dừng sớm với thông báo rõ ràng để người
     * dùng biết cần dọn data ở đâu, thay vì để MySQL trả lỗi khó hiểu.
     */
    private function guardAgainstDuplicateNames(string $table): void
    {
        $duplicates = DB::table($table)
            ->select('company_id', 'name', DB::raw('COUNT(*) as total'))
            ->groupBy('company_id', 'name')
            ->having('total', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $details = $duplicates
                ->map(fn($row) => "company_id={$row->company_id}, name=\"{$row->name}\" ({$row->total} bản ghi)")
                ->implode('; ');

            throw new \RuntimeException(
                "Không thể tạo unique index cho bảng `{$table}` vì dữ liệu đang trùng: {$details}. " .
                    "Hãy dọn/đổi tên các bản ghi trùng trước khi migrate lại."
            );
        }
    }
};
