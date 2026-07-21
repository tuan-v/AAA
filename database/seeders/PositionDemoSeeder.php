<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_code', '0101234567')->firstOrFail();

        $items = [
            ['department' => 'PB-001', 'code' => 'CV-001', 'name' => 'Giám đốc', 'email' => 'admin@demo.vn'],
            ['department' => 'PB-002', 'code' => 'CV-002', 'name' => 'Trưởng phòng Nhân sự', 'email' => 'hr@demo.vn'],
            ['department' => 'PB-003', 'code' => 'CV-003', 'name' => 'Trưởng phòng Mua hàng', 'email' => 'purchase@demo.vn'],
            ['department' => 'PB-004', 'code' => 'CV-004', 'name' => 'Quản lý Kho', 'email' => 'warehouse@demo.vn'],
            ['department' => 'PB-005', 'code' => 'CV-005', 'name' => 'Trưởng phòng Kinh doanh', 'email' => 'sales@demo.vn'],
            ['department' => 'PB-006', 'code' => 'CV-006', 'name' => 'Kế toán trưởng', 'email' => 'accountant@demo.vn'],
        ];

        DB::transaction(function () use ($company, $items) {
            foreach ($items as $item) {
                $department = Department::where('company_id', $company->id)
                    ->where('code', $item['department'])->firstOrFail();

                $position = Position::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $item['code']],
                    [
                        'department_id' => $department->id,
                        'name' => $item['name'],
                        'description' => 'Chức vụ mẫu phục vụ kiểm thử luồng phòng ban – chức vụ.',
                        'status' => 'active',
                    ]
                );

                User::where('company_id', $company->id)
                    ->where('email', $item['email'])
                    ->update(['department_id' => $department->id, 'position_id' => $position->id]);
            }
        });
    }
}
