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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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
            $company->update([
                'storefront_slug' => 'cong-ty-tnhh-demo-viet',
                'storefront_enabled' => true,
            ]);

            $department = Department::updateOrCreate(
                ['company_id' => $company->id, 'code' => 'PB-001'],
                ['name' => 'Phòng Điều hành', 'status' => 'active', 'manager_id' => $owner->id]
            );
            $owner->update(['company_id' => $company->id, 'department_id' => $department->id]);
            $owner->companies()->syncWithoutDetaching([$company->id]);
            $owner->syncRoles(['Giám đốc']);
            $company->currencies()->syncWithoutDetaching([
                $vnd->id => ['is_default' => true], $usd->id => ['is_default' => false],
            ]);

            $moduleRoles = [
                'Quản lý nhân sự' => ['nhan_su', 'vai_tro', 'quyen', 'nhat_ky'],
                'Quản lý mua hàng' => ['nha_cung_cap', 'danh_muc_mua_hang', 'don_vi_mua_hang', 'san_pham_mua_hang', 'don_mua'],
                'Quản lý kho' => ['kho', 'danh_muc_kho', 'don_vi_kho', 'san_pham_kho', 'phieu_kho', 'chuyen_kho'],
                'Quản lý bán hàng' => ['khach_hang', 'don_ban', 'phieu_giam_gia'],
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
                $rolePermissions = DB::table('permissions')
                    ->where(function ($query) use ($modules) {
                        foreach ($modules as $module) {
                            $query->orWhere('name', 'like', $module.'.%');
                        }
                    })
                    ->pluck('name')
                    ->when(
                        $roleName === 'Quản lý kế toán',
                        fn ($permissions) => $permissions->merge([
                            'phieu_kho.xem',
                            'phieu_kho.xem_chi_tiet',
                            'phieu_kho.duyet_ke_toan',
                        ])
                    )
                    ->all();
                $role->syncPermissions($rolePermissions);
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

            $province = Province::firstOrCreate(['code' => '1'], ['name' => 'Thành phố Hà Nội']);
            $ward = Ward::firstOrCreate(['code' => '70'], ['province_id' => $province->id, 'name' => 'Phường Hoàn Kiếm']);
            $addressId = DB::table('addresses')->updateOrInsert(
                ['province_id' => $province->id, 'ward_id' => $ward->id, 'address_detail' => 'Kho số 1'],
                ['updated_at' => now(), 'created_at' => now()]
            );
            $address = DB::table('addresses')->where('province_id', $province->id)->where('ward_id', $ward->id)->first();

            $parent = Category::updateOrCreate(['company_id' => $company->id, 'code' => 'DM-DIEN-TU'], [
                'name' => 'Điện tử', 'description' => 'Nhóm hàng điện tử', 'status' => 'active',
            ]);
            $category = Category::updateOrCreate(['company_id' => $company->id, 'code' => 'DM-PHU-KIEN'], [
                'parent_id' => $parent->id, 'name' => 'Phụ kiện', 'description' => 'Phụ kiện điện tử tổng hợp', 'status' => 'active',
            ]);
            $keyboardMouseCategory = Category::updateOrCreate(['company_id' => $company->id, 'code' => 'DM-PHIM-CHUOT'], [
                'parent_id' => $parent->id, 'name' => 'Phím & Chuột', 'description' => 'Thiết bị điều khiển cho làm việc và giải trí', 'status' => 'active',
            ]);
            $hikingCategory = Category::updateOrCreate(['company_id' => $company->id, 'code' => 'DM-DA-NGOAI'], [
                'parent_id' => $parent->id, 'name' => 'Âm thanh', 'description' => 'Tai nghe và thiết bị âm thanh cá nhân', 'status' => 'active',
            ]);
            $dailyCategory = Category::updateOrCreate(['company_id' => $company->id, 'code' => 'DM-HANG-NGAY'], [
                'parent_id' => $parent->id, 'name' => 'Văn phòng thông minh', 'description' => 'Thiết bị tối ưu không gian làm việc hiện đại', 'status' => 'active',
            ]);
            $phoneCategory = Category::updateOrCreate(['company_id' => $company->id, 'name' => 'Điện thoại'], [
                'code' => 'DM-DIEN-THOAI', 'parent_id' => $parent->id, 'description' => 'Điện thoại thông minh cho công việc và giải trí', 'status' => 'active',
            ]);
            $phoneAccessoryCategory = Category::updateOrCreate(['company_id' => $company->id, 'name' => 'Phụ kiện điện thoại'], [
                'code' => 'DM-PK-DIEN-THOAI', 'parent_id' => $parent->id, 'description' => 'Sạc, cáp, pin dự phòng và tai nghe không dây', 'status' => 'active',
            ]);
            $unit = Unit::updateOrCreate(['company_id' => $company->id, 'symbol' => 'cái'], [
                'name' => 'Cái', 'allow_decimal' => false, 'status' => 'active',
            ]);

            $products = collect([
                ['DEMO-SP-001', 'Bàn phím cơ Nova 75', 1050000, 1590000, $keyboardMouseCategory->id, 'storefront/keyboard-black.png', 'Bàn phím cơ layout 75%, kết nối không dây, keycap PBT và đèn nền tùy chỉnh.'],
                ['DEMO-SP-002', 'Chuột không dây Flow Pro', 620000, 990000, $keyboardMouseCategory->id, 'storefront/mouse-graphite.png', 'Chuột công thái học cảm biến chính xác, kết nối đa thiết bị và pin sử dụng dài ngày.'],
                ['DEMO-SP-003', 'Tai nghe chụp tai AirBeat ANC', 1450000, 2290000, $hikingCategory->id, 'storefront/headphones-black.png', 'Tai nghe Bluetooth chống ồn chủ động, âm thanh cân bằng và đệm tai êm cho cả ngày.'],
                ['DEMO-SP-004', 'Bàn phím cơ Nova Mini', 780000, 1290000, $keyboardMouseCategory->id, 'storefront/keyboard-black.png', 'Thiết kế nhỏ gọn tiết kiệm diện tích, switch tuyến tính và kết nối ba chế độ.'],
                ['DEMO-SP-005', 'Tai nghe Studio One', 1180000, 1890000, $hikingCategory->id, 'storefront/headphones-black.png', 'Chất âm chi tiết, micro đàm thoại rõ và thiết kế tối giản phù hợp làm việc.'],
                ['DEMO-SP-006', 'Chuột Silent Click S2', 390000, 650000, $dailyCategory->id, 'storefront/mouse-graphite.png', 'Nút bấm yên tĩnh, trọng lượng nhẹ và kết nối ổn định cho văn phòng.'],
                ['DEMO-SP-007', 'Bàn phím WorkBoard Wireless', 890000, 1390000, $dailyCategory->id, 'storefront/keyboard-black.png', 'Bàn phím không dây gọn gàng với thời lượng pin dài và phím tắt đa phương tiện.'],
                ['DEMO-SP-008', 'Tai nghe AirBeat Lite', 760000, 1190000, $hikingCategory->id, 'storefront/headphones-black.png', 'Tai nghe không dây nhẹ, micro kép và thời lượng pin phù hợp di chuyển hằng ngày.'],
                ['DEMO-SP-009', 'Chuột Precision M3', 520000, 850000, $keyboardMouseCategory->id, 'storefront/mouse-graphite.png', 'Cảm biến độ phân giải cao, sáu nút tùy chỉnh và kiểu dáng ôm tay chắc chắn.'],
                ['DEMO-SP-010', 'Bàn phím cơ Creator 98', 1320000, 1990000, $keyboardMouseCategory->id, 'storefront/keyboard-black.png', 'Layout đầy đủ cho sáng tạo nội dung, núm xoay đa chức năng và kết nối ba thiết bị.'],
                ['DEMO-SP-011', 'Chuột Office Anywhere', 450000, 720000, $dailyCategory->id, 'storefront/mouse-graphite.png', 'Chuột không dây nhỏ gọn, cuộn chính xác và chuyển đổi nhanh giữa laptop, máy tính bảng.'],
                ['DEMO-SP-012', 'Tai nghe Conference Pro', 1280000, 2090000, $hikingCategory->id, 'storefront/headphones-black.png', 'Micro lọc ồn cho họp trực tuyến, kết nối kép và đệm tai thoáng khí.'],
                ['DEMO-SP-013', 'Nova Phone X1 128GB — Graphite', 7850000, 9990000, $phoneCategory->id, 'storefront/phone-graphite.png', 'Màn hình OLED 6.5 inch, camera ba ống kính, bộ nhớ 128GB và sạc nhanh 45W.'],
                ['DEMO-SP-014', 'Nova Phone X1 Pro 256GB — Graphite', 11200000, 13990000, $phoneCategory->id, 'storefront/phone-graphite.png', 'Smartphone cao cấp với màn hình 120Hz, camera chống rung quang học và bộ nhớ 256GB.'],
                ['DEMO-SP-015', 'Pulse Phone A5 128GB — Ocean Blue', 4650000, 5990000, $phoneCategory->id, 'storefront/phone-blue.png', 'Điện thoại tầm trung pin 5000mAh, màn hình lớn, camera kép và thiết kế xanh satin.'],
                ['DEMO-SP-016', 'Củ sạc nhanh GaN 30W', 280000, 450000, $phoneAccessoryCategory->id, 'storefront/phone-accessories.png', 'Củ sạc USB-C công nghệ GaN nhỏ gọn, hỗ trợ sạc nhanh cho điện thoại và máy tính bảng.'],
                ['DEMO-SP-017', 'Cáp USB-C bện dù 100W 1.5m', 120000, 220000, $phoneAccessoryCategory->id, 'storefront/phone-accessories.png', 'Cáp sạc bền chắc, hỗ trợ công suất tối đa 100W và truyền dữ liệu ổn định.'],
                ['DEMO-SP-018', 'Pin dự phòng SlimCharge 10.000mAh', 520000, 790000, $phoneAccessoryCategory->id, 'storefront/phone-accessories.png', 'Pin dự phòng mỏng nhẹ, hai chiều USB-C và màn hình báo dung lượng pin.'],
                ['DEMO-SP-019', 'Tai nghe True Wireless Pocket Buds', 690000, 1090000, $phoneAccessoryCategory->id, 'storefront/phone-accessories.png', 'Tai nghe không dây nhỏ gọn, chống ồn cuộc gọi và hộp sạc dùng đến 28 giờ.'],
            ])->map(function ($row) use ($company, $unit) {
                $attributes = [
                    'company_id' => $company->id, 'name' => $row[1], 'category_id' => $row[4],
                    'unit_id' => $unit->id, 'type' => 'hang_hoa',
                    'purchase_price' => $row[2], 'sell_price' => $row[3], 'quantity' => 0, 'status' => 'active',
                    'description' => $row[6], 'image' => $row[5], 'storefront_visible' => true,
                ];
                if (Schema::hasColumn('products', 'color')) {
                    $attributes['color'] = 'Tiêu chuẩn';
                }

                return Product::updateOrCreate(['sku' => $row[0]], $attributes);
            });

            $warehouse = Warehouse::updateOrCreate(['company_id' => $company->id, 'code' => 'KHO-DEMO'], [
                'name' => 'Kho trung tâm', 'address_id' => $address->id, 'address_detail' => 'Kho số 1',
                'province_code' => (string) $province->id, 'ward_code' => (string) $ward->id,
                'total_inventory_value' => 12250000, 'status' => 'active',
            ]);
            foreach ($products as $index => $product) {
                $quantity = [25, 18, 15, 12, 9, 20, 7, 11, 18, 10, 24, 13, 8, 6, 14, 35, 48, 22, 30][$index];
                WarehouseProductStock::updateOrCreate(
                    ['company_id' => $company->id, 'warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                    ['quantity' => $quantity, 'stock_value' => $quantity * (float) $product->purchase_price]
                );
            }

            $supplier = Supplier::updateOrCreate(['company_id' => $company->id, 'code' => 'NCC-DEMO'], [
                'name' => 'Công ty Cung ứng Việt', 'phone' => '0912000001', 'email' => 'ncc@demo.vn',
                'currency_id' => $vnd->id, 'province_code' => $province->code, 'province_name' => $province->name,
                'ward_code' => $ward->code, 'ward_name' => $ward->name, 'address_detail' => '10 Trần Hưng Đạo',
                'opening_debt' => 1000000, 'opening_debt_exchange_rate' => 1,
                'opening_debt_base' => 1000000, 'opening_advance_exchange_rate' => 1,
                'opening_advance_base' => 0, 'total_debts' => 1000000, 'status' => 'active',
            ]);
            $customer = Customer::updateOrCreate(['company_id' => $company->id, 'code' => 'KH-DEMO'], [
                'name' => 'Cửa hàng Minh Anh', 'phone' => '0913000001', 'email' => 'khachhang@demo.vn',
                'currency_id' => $vnd->id, 'province_id' => $province->id, 'ward_id' => $ward->id,
                'address_detail' => '20 Hai Bà Trưng', 'opening_debt' => 500000,
                'opening_debt_exchange_rate' => 1, 'opening_debt_base' => 500000, 'status' => 'active',
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
                ['TAM_UNG_KH', 'Khách hàng tạm ứng', 'income'],
                ['HOAN_TAM_UNG_KH', 'Hoàn tạm ứng khách hàng', 'expense'],
                ['TAM_UNG_NCC', 'Tạm ứng nhà cung cấp', 'expense'],
                ['HOAN_TAM_UNG_NCC', 'Nhà cung cấp hoàn tạm ứng', 'income'],
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
                    'type' => 'payment', 'payment_method' => 'bank_transfer',
                    'amount' => 1000000, 'currency_id' => $vnd->id,
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
