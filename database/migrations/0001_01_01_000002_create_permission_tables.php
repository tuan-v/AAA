<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nhóm 3: Permission Tables (Spatie Laravel Permission)
 *
 * Bảng:
 *   - permissions
 *   - roles            (+company_id, +type, +is_protected)
 *   - model_has_permissions
 *   - model_has_roles
 *   - role_has_permissions
 *
 * Roles có thêm:
 *   - company_id    : FK companies (nullable) — phân biệt role của từng công ty
 *   - type          : 'system' (seed sẵn) | 'user' (do admin tự tạo)
 *   - is_protected  : true = Super Admin, không được sửa/xóa/gán thêm bởi user thường
 */
return new class extends Migration
{
    public function up(): void
    {
        $teams            = config('permission.teams');
        $tableNames       = config('permission.table_names');
        $columnNames      = config('permission.column_names');
        $pivotRole        = $columnNames['role_pivot_key']       ?? 'role_id';
        $pivotPermission  = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(
            empty($tableNames),
            Exception::class,
            'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.'
        );
        throw_if(
            $teams && empty($columnNames['team_foreign_key'] ?? null),
            Exception::class,
            'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.'
        );

        // ── permissions ────────────────────────────────────────────────────
        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->text('description')->nullable()->comment('Mô tả quyền');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
            $table->comment('Danh sách quyền trên phần mềm');
        });

        // ── roles ──────────────────────────────────────────────────────────
        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id');

            // Công ty sở hữu role (nullable = role global/system)
            $table->unsignedBigInteger('company_id')->nullable()->index()->comment('Công ty sở hữu role; NULL = role hệ thống');

            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }

            $table->string('name');
            $table->string('guard_name');
            $table->text('description')->nullable()->comment('Mô tả vai trò');

            // 'system' = do hệ thống seed, không cho user tự thêm
            // 'user'   = do admin công ty tự tạo
            $table->enum('type', ['system', 'user'])->default('user')
                ->comment('system=Hệ thống seed sẵn | user=Admin công ty tự tạo');

            // Super Admin và các role quan trọng: ẩn khỏi user thường, không thể xóa
            $table->boolean('is_protected')->default(false)
                ->comment('true=Không thể sửa/xóa bởi user thường');

            $table->timestamps();

            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }

            $table->comment('Danh sách vai trò dùng để phân quyền');
        });

        // ── model_has_permissions ──────────────────────────────────────────
        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->unsignedBigInteger('company_id')->nullable()->index()->comment('Công ty sở hữu quyền này');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }

            $table->comment('Quyền trực tiếp của từng tài khoản');
        });

        // ── model_has_roles ────────────────────────────────────────────────
        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }

            $table->comment('Vai trò của từng tài khoản');
        });

        // ── role_has_permissions ───────────────────────────────────────────
        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
            $table->comment('Quyền của từng vai trò');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(
            empty($tableNames),
            Exception::class,
            'Error: config/permission.php not found. Drop the tables manually.'
        );

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
