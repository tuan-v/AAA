<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_code', '0101234567')->firstOrFail();

        $departments = [
            ['code' => 'PB-001', 'name' => 'Ban điều hành', 'description' => 'Điều hành chiến lược và phê duyệt các hoạt động chung.', 'email' => 'admin@demo.vn'],
            ['code' => 'PB-002', 'name' => 'Hành chính - Nhân sự', 'description' => 'Quản lý nhân sự, tuyển dụng, chính sách và hành chính nội bộ.', 'email' => 'hr@demo.vn'],
            ['code' => 'PB-003', 'name' => 'Mua hàng', 'description' => 'Quản lý nhà cung cấp, đơn mua và kế hoạch nhập hàng.', 'email' => 'purchase@demo.vn'],
            ['code' => 'PB-004', 'name' => 'Kho vận', 'description' => 'Quản lý tồn kho, nhập xuất và điều chuyển hàng hóa.', 'email' => 'warehouse@demo.vn'],
            ['code' => 'PB-005', 'name' => 'Kinh doanh', 'description' => 'Quản lý khách hàng, báo giá, đơn bán và doanh số.', 'email' => 'sales@demo.vn'],
            ['code' => 'PB-006', 'name' => 'Tài chính - Kế toán', 'description' => 'Quản lý thu chi, công nợ, tài khoản và báo cáo tài chính.', 'email' => 'accountant@demo.vn'],
        ];

        DB::transaction(function () use ($company, $departments) {
            foreach ($departments as $item) {
                $manager = User::where('email', $item['email'])
                    ->where('company_id', $company->id)
                    ->firstOrFail();

                $department = Department::updateOrCreate(
                    ['company_id' => $company->id, 'code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'status' => 'active',
                        'manager_id' => $manager->id,
                    ]
                );

                $manager->update(['department_id' => $department->id]);
            }

            Department::where('company_id', $company->id)
                ->whereNotIn('code', collect($departments)->pluck('code'))
                ->whereDoesntHave('users')
                ->delete();
        });
    }
}
