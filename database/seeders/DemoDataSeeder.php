<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\Department;
use App\Models\Product;
use App\Models\Province;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\TransactionCategory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Ward;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use App\Models\WarehouseSlip;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $vnd = Currency::where('code', 'VND')->firstOrFail();
            $usd = Currency::where('code', 'USD')->firstOrFail();

            $department = Department::firstOrCreate(['name' => 'Phòng Điều hành']);
            $owner = User::updateOrCreate(['email' => 'admin@demo.vn'], [
                'name' => 'Quản trị Demo', 'username' => 'admin_demo', 'phone' => '0901000001',
                'password' => Hash::make('12345678'), 'type' => 'user', 'status' => 'active',
                'department_id' => $department->id, 'email_verified_at' => now(),
            ]);

            $company = Company::updateOrCreate(['tax_code' => '0101234567'], [
                'name' => 'Công ty TNHH Demo Việt', 'phone' => '0901000000', 'email' => 'contact@demo.vn',
                'address' => '1 Tràng Tiền, Hà Nội', 'owner_id' => $owner->id,
            ]);
            $department->update([
                'company_id' => $company->id,
                'code' => 'PB-001',
                'status' => 'active',
                'manager_id' => $owner->id,
            ]);
            $owner->update(['company_id' => $company->id]);
            $owner->companies()->syncWithoutDetaching([$company->id]);
            $owner->syncRoles(['Giám đốc']);
            $company->currencies()->syncWithoutDetaching([
                $vnd->id => ['is_default' => true], $usd->id => ['is_default' => false],
            ]);

            $moduleRoles = [
                'Quản lý nhân sự' => ['nhan_su', 'vai_tro', 'quyen', 'nhat_ky'],
                'Quản lý mua hàng' => ['nha_cung_cap', 'danh_muc_mua_hang', 'don_vi_mua_hang', 'san_pham_mua_hang', 'don_mua'],
                'Quản lý kho' => ['kho', 'danh_muc_kho', 'don_vi_kho', 'san_pham_kho', 'phieu_kho', 'chuyen_kho'],
                'Quản lý bán hàng' => ['khach_hang', 'don_ban'],
                'Quản lý kế toán' => ['tien_te', 'ngan_hang', 'tai_khoan', 'loai_giao_dich', 'giao_dich', 'cong_no_khach_hang', 'cong_no_nha_cung_cap'],
            ];

            foreach ($moduleRoles as $roleName => $modules) {
                $role = Role::updateOrCreate(
                    ['name' => $roleName, 'guard_name' => 'web'],
                    [
                        'description' => $roleName.' hệ thống',
                        'company_id' => null,
                        'type' => 'system',
                        'hierarchy_level' => 50,
                        'is_protected' => false,
                    ]
                );
                $role->syncPermissions(
                    DB::table('permissions')
                        ->where(function ($query) use ($modules) {
                            foreach ($modules as $module) {
                                $query->orWhere('name', 'like', $module.'.%');
                            }
                        })
                        ->pluck('name')
                        ->all()
                );
            }

            foreach ([
                ['Nhân sự Demo', 'hr@demo.vn', 'nhansu_demo', 'Quản lý nhân sự'],
                ['Mua hàng Demo', 'purchase@demo.vn', 'muahang_demo', 'Quản lý mua hàng'],
                ['Thủ kho Demo', 'warehouse@demo.vn', 'thukho_demo', 'Quản lý kho'],
                ['Kinh doanh Demo', 'sales@demo.vn', 'kinhdoanh_demo', 'Quản lý bán hàng'],
                ['Kế toán Demo', 'accountant@demo.vn', 'ketoan_demo', 'Quản lý kế toán'],
            ] as [$name, $email, $username, $role]) {
                $user = User::updateOrCreate(['email' => $email], [
                    'name' => $name, 'username' => $username, 'password' => Hash::make('12345678'),
                    'type' => 'user', 'status' => 'active', 'company_id' => $company->id,
                    'department_id' => $department->id, 'email_verified_at' => now(),
                ]);
                $user->companies()->syncWithoutDetaching([$company->id]);
                $user->syncRoles([$role]);
            }

            $province = Province::firstOrCreate(['code' => '01'], ['name' => 'Thành phố Hà Nội']);
            $ward = Ward::firstOrCreate(['code' => '00001'], ['province_id' => $province->id, 'name' => 'Phường Hoàn Kiếm']);
            $addressId = DB::table('addresses')->updateOrInsert(
                ['province_id' => $province->id, 'ward_id' => $ward->id, 'address_detail' => 'Kho số 1'],
                ['updated_at' => now(), 'created_at' => now()]
            );
            $address = DB::table('addresses')->where('province_id', $province->id)->where('ward_id', $ward->id)->first();

            $parent = Category::updateOrCreate(['company_id' => $company->id, 'code' => 'DM-DIEN-TU'], [
                'name' => 'Điện tử', 'description' => 'Nhóm hàng điện tử', 'status' => 'active',
            ]);
            $category = Category::updateOrCreate(['company_id' => $company->id, 'code' => 'DM-PHU-KIEN'], [
                'parent_id' => $parent->id, 'name' => 'Phụ kiện', 'status' => 'active',
            ]);
            $unit = Unit::updateOrCreate(['company_id' => $company->id, 'symbol' => 'cái'], [
                'name' => 'Cái', 'allow_decimal' => false, 'status' => 'active',
            ]);

            $products = collect([
                ['DEMO-SP-001', 'Chuột không dây', 180000, 260000],
                ['DEMO-SP-002', 'Bàn phím cơ', 650000, 890000],
                ['DEMO-SP-003', 'Tai nghe văn phòng', 320000, 450000],
            ])->map(fn ($row) => Product::updateOrCreate(['sku' => $row[0]], [
                'company_id' => $company->id, 'name' => $row[1], 'category_id' => $category->id,
                'unit_id' => $unit->id, 'type' => 'hang_hoa', 'color' => 'Tiêu chuẩn',
                'purchase_price' => $row[2], 'sell_price' => $row[3], 'quantity' => 0, 'status' => 'active',
            ]));

            $warehouse = Warehouse::updateOrCreate(['company_id' => $company->id, 'code' => 'KHO-DEMO'], [
                'name' => 'Kho trung tâm', 'address_id' => $address->id, 'address_detail' => 'Kho số 1',
                'province_code' => $province->code, 'ward_code' => $ward->code,
                'total_inventory_value' => 12250000, 'status' => 'active',
            ]);
            foreach ($products as $index => $product) {
                $quantity = [25, 8, 15][$index];
                WarehouseProductStock::updateOrCreate(
                    ['company_id' => $company->id, 'warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                    ['quantity' => $quantity, 'stock_value' => $quantity * (float) $product->purchase_price]
                );
            }

            $supplier = Supplier::updateOrCreate(['company_id' => $company->id, 'code' => 'NCC-DEMO'], [
                'name' => 'Công ty Cung ứng Việt', 'phone' => '0912000001', 'email' => 'ncc@demo.vn',
                'currency_id' => $vnd->id, 'province_code' => $province->code, 'province_name' => $province->name,
                'ward_code' => $ward->code, 'ward_name' => $ward->name, 'address_detail' => '10 Trần Hưng Đạo',
                'opening_debt' => 1000000, 'total_debts' => 1000000, 'status' => 'active',
            ]);
            $customer = Customer::updateOrCreate(['company_id' => $company->id, 'code' => 'KH-DEMO'], [
                'name' => 'Cửa hàng Minh Anh', 'phone' => '0913000001', 'email' => 'khachhang@demo.vn',
                'currency_id' => $vnd->id, 'province_id' => $province->id, 'ward_id' => $ward->id,
                'address_detail' => '20 Hai Bà Trưng', 'opening_debt' => 500000, 'status' => 'active',
            ]);

            $po = PurchaseOrder::updateOrCreate(['company_id' => $company->id, 'code' => 'PO-DEMO-001'], [
                'supplier_id' => $supplier->id, 'currency_id' => $vnd->id, 'exchange_rate' => 1,
                'expected_received_date' => now()->addDays(3), 'status' => 'completed', 'created_by' => $owner->id,
                'approved_by' => $owner->id, 'approved_at' => now(), 'subtotal' => 4900000,
                'vat_amount' => 490000, 'total_amount' => 5390000, 'note' => 'Đơn mua demo đã nhập kho',
            ]);
            $poItem = $po->items()->updateOrCreate(['product_id' => $products[0]->id], [
                'quantity' => 20, 'received_quantity' => 20, 'price' => 245000, 'company_price' => 245000,
                'amount' => 4900000, 'vat_percent' => 10,
            ]);
            $so = SalesOrder::updateOrCreate(['company_id' => $company->id, 'code' => 'SO-DEMO-001'], [
                'customer_id' => $customer->id, 'currency_id' => $vnd->id, 'exchange_rate' => 1,
                'expected_delivery_date' => now()->addDay(), 'status' => 'completed', 'created_by' => $owner->id,
                'approved_by' => $owner->id, 'approved_at' => now(), 'subtotal' => 1780000,
                'vat_amount' => 178000, 'total_amount' => 1958000, 'note' => 'Đơn bán demo đã xuất kho',
            ]);
            $so->items()->updateOrCreate(['product_id' => $products[1]->id], [
                'quantity' => 2, 'unit_price' => 890000, 'company_unit_price' => 890000,
                'amount' => 1780000, 'company_amount' => 1780000, 'vat_percent' => 10,
            ]);

            $import = WarehouseSlip::updateOrCreate(['company_id' => $company->id, 'code' => 'PN-DEMO-001'], [
                'type' => 'import', 'purchase_order_id' => $po->id, 'warehouse_id' => $warehouse->id,
                'created_by' => $owner->id, 'approved_by' => $owner->id, 'approved_at' => now(),
                'status' => 'approved', 'note' => 'Phiếu nhập demo',
            ]);
            $import->items()->updateOrCreate(['product_id' => $products[0]->id], [
                'purchase_order_item_id' => $poItem->id, 'quantity' => 20, 'price' => 245000,
                'company_price' => 245000, 'cost_price' => 245000, 'cost_amount' => 4900000,
                'total_value' => 5390000, 'vat_percent' => 10,
            ]);
            $export = WarehouseSlip::updateOrCreate(['company_id' => $company->id, 'code' => 'PX-DEMO-001'], [
                'type' => 'export', 'sales_order_id' => $so->id, 'warehouse_id' => $warehouse->id,
                'created_by' => $owner->id, 'approved_by' => $owner->id, 'approved_at' => now(),
                'status' => 'approved', 'note' => 'Phiếu xuất demo',
            ]);
            $export->items()->updateOrCreate(['product_id' => $products[1]->id], [
                'quantity' => 2, 'price' => 890000, 'company_price' => 0, 'cost_price' => 650000,
                'cost_amount' => 1300000, 'total_value' => 1780000, 'vat_percent' => 10,
            ]);

            CustomerDebt::updateOrCreate(['customer_id' => $customer->id, 'reference_type' => WarehouseSlip::class, 'reference_id' => $export->id], [
                'type' => 'sale', 'amount' => 1958000, 'note' => 'Công nợ từ phiếu xuất demo',
            ]);
            SupplierDebt::updateOrCreate(['supplier_id' => $supplier->id, 'reference_type' => WarehouseSlip::class, 'reference_id' => $import->id], [
                'type' => 'invoice', 'amount' => 5390000, 'note' => 'Công nợ từ phiếu nhập demo',
            ]);

            $bank = Bank::updateOrCreate(['code' => 'VCB'], ['name' => 'Ngân hàng TMCP Ngoại thương Việt Nam', 'short_name' => 'Vietcombank', 'status' => 1]);
            $cash = Account::updateOrCreate(['company_id' => $company->id, 'code' => 'TM-DEMO'], [
                'name' => 'Quỹ tiền mặt', 'type' => 'cash', 'currency_id' => $vnd->id,
                'opening_balance' => 10000000, 'current_balance' => 12500000, 'is_active' => true,
            ]);
            $bankAccount = Account::updateOrCreate(['company_id' => $company->id, 'code' => 'NH-DEMO'], [
                'name' => 'Tài khoản Vietcombank', 'type' => 'bank', 'currency_id' => $vnd->id,
                'opening_balance' => 50000000, 'current_balance' => 50000000, 'bank_id' => $bank->id,
                'bank_account_no' => '0011001234567', 'is_active' => true,
            ]);
            foreach ([
                ['THU_KH', 'Thu tiền khách hàng', 'income'], ['THU_KHAC', 'Thu khác', 'income'],
                ['CHI_NCC', 'Thanh toán nhà cung cấp', 'expense'], ['CHI_KHAC', 'Chi khác', 'expense'],
                ['CHUYEN_KHOAN', 'Chuyển tiền nội bộ', 'transfer'],
            ] as [$code, $name, $type]) {
                TransactionCategory::updateOrCreate(['company_id' => $company->id, 'code' => $code], [
                    'name' => $name, 'type' => $type, 'status' => 1,
                ]);
            }

            Auth::login($owner);
            $service = app(TransactionService::class);
            if (! DB::table('transactions')->where('company_id', $company->id)->where('description', 'Khách hàng thanh toán demo')->exists()) {
                $receipt = $service->create([
                    'type' => 'receipt', 'amount' => 500000, 'currency_id' => $vnd->id,
                    'category_id' => TransactionCategory::where('company_id', $company->id)->where('code', 'THU_KH')->value('id'),
                    'to_account_id' => $cash->id, 'customer_id' => $customer->id, 'sales_order_id' => $so->id,
                    'transaction_date' => now()->toDateString(), 'description' => 'Khách hàng thanh toán demo',
                ]);
                $service->approve($receipt->id);
            }
            if (! DB::table('transactions')->where('company_id', $company->id)->where('description', 'Thanh toán nhà cung cấp demo')->exists()) {
                $payment = $service->create([
                    'type' => 'payment', 'amount' => 1000000, 'currency_id' => $vnd->id,
                    'category_id' => TransactionCategory::where('company_id', $company->id)->where('code', 'CHI_NCC')->value('id'),
                    'from_account_id' => $bankAccount->id, 'supplier_id' => $supplier->id, 'purchase_order_id' => $po->id,
                    'transaction_date' => now()->toDateString(), 'description' => 'Thanh toán nhà cung cấp demo',
                ]);
                $service->approve($payment->id);
            }
            Auth::logout();
        });
    }
}
