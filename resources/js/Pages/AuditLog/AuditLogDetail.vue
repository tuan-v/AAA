<template>
    <div class="asfy-modal-scroll max-h-[78vh] w-full max-w-3xl space-y-4 overflow-y-auto rounded-2xl bg-white p-4 text-gray-800 shadow-2xl dark:bg-gray-900 dark:text-gray-100 sm:p-5">
        <section class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/60">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-700">
                        {{ actorInitial }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold">{{ log?.user?.name || "Hệ thống" }}</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="actionBadgeClass">
                                {{ log?.action_label || actionLabel }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ log?.model_label || modelLabel }} · {{ log?.record_reference || `#${log?.model_id || "-"}` }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-200 dark:hover:bg-gray-700"
                    aria-label="Đóng"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </div>

            <div class="mt-4 grid gap-3 border-t border-gray-200 pt-4 text-sm sm:grid-cols-3 dark:border-gray-700">
                <div>
                    <span class="block text-xs text-gray-500">Thời gian thực hiện</span>
                    <span class="font-medium">{{ displayedTime }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500">Đối tượng</span>
                    <span class="font-medium">{{ log?.model_label || modelLabel }} {{ log?.record_reference || `#${log?.model_id || "-"}` }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500">Kết quả</span>
                    <span class="font-medium text-green-600">Thành công</span>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 dark:border-blue-900 dark:bg-blue-950/30">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Nội dung hành động</p>
            <p class="mt-1 font-medium text-blue-900 dark:text-blue-100">
                {{ log?.summary || log?.description || `${actionLabel} ${modelLabel.toLowerCase()}` }}
            </p>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                <h4 class="font-semibold">{{ changeSectionTitle }}</h4>
                <p class="mt-0.5 text-xs text-gray-500">{{ changeSectionHint }}</p>
            </div>

            <div v-if="fieldDiffs.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                <div
                    v-for="field in fieldDiffs"
                    :key="field.key"
                    class="grid gap-3 p-4 md:items-center"
                    :class="isCreate || isDelete ? 'md:grid-cols-[180px_1fr]' : 'md:grid-cols-[180px_1fr_32px_1fr]'"
                >
                    <div class="font-medium text-gray-700 dark:text-gray-200">{{ field.label }}</div>
                    <div
                        v-if="!isCreate"
                        class="rounded-lg border px-3 py-2 text-sm break-words"
                        :class="isDelete ? 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200'"
                    >
                        <span class="mb-1 block text-[11px] font-semibold uppercase opacity-70">Trước thay đổi</span>
                        {{ formatValue(field.oldVal, field.key, "Chưa có") }}
                    </div>
                    <div v-if="!isCreate && !isDelete" class="hidden text-center text-gray-400 md:block">→</div>
                    <div v-if="!isDelete" class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800 break-words dark:border-green-900 dark:bg-green-950/30 dark:text-green-200">
                        <span class="mb-1 block text-[11px] font-semibold uppercase opacity-70">{{ isCreate ? 'Giá trị đã tạo' : 'Sau thay đổi' }}</span>
                        {{ formatValue(field.newVal, field.key, isCreate ? "Chưa thiết lập" : "Đã bỏ giá trị") }}
                    </div>
                </div>
            </div>

            <div v-else class="px-5 py-10 text-center text-sm text-gray-500">
                Hành động đã được ghi nhận nhưng không có dữ liệu trước–sau để so sánh.
            </div>
        </section>

        <section v-if="itemsList.length" class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <button type="button" class="flex w-full items-center justify-between bg-gray-50 px-4 py-3 text-left dark:bg-gray-800" @click="showItems = !showItems">
                <span class="font-semibold">Danh sách sản phẩm</span>
                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ itemsList.length }} sản phẩm</span>
            </button>
            <div v-show="showItems" class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-sm">
                    <thead class="border-t border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-center">STT</th>
                            <th class="px-4 py-3 text-left">Sản phẩm</th>
                            <th class="px-4 py-3 text-right">Số lượng</th>
                            <th class="px-4 py-3 text-right">Đơn giá</th>
                            <th class="px-4 py-3 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-for="(item, index) in itemsList" :key="item.id || index">
                            <td class="px-4 py-3 text-center">{{ index + 1 }}</td>
                            <td class="px-4 py-3">{{ item.product_name || item.name || `Sản phẩm #${item.product_id || "-"}` }}</td>
                            <td class="px-4 py-3 text-right">{{ formatQuantity(item.quantity || 0) }}</td>
                            <td class="px-4 py-3 text-right">{{ formatMoney(item.price || item.unit_price || 0) }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ formatMoney(item.total_amount || Number(item.quantity || 0) * Number(item.price || item.unit_price || 0)) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <details class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800/60">
            <summary class="cursor-pointer font-medium text-gray-600 dark:text-gray-300">Thông tin truy vết kỹ thuật</summary>
            <div class="mt-3 grid gap-3 border-t border-gray-200 pt-3 text-xs sm:grid-cols-2 dark:border-gray-700">
                <div><span class="text-gray-500">Địa chỉ IP:</span> <strong>{{ log?.ip_address || "Không ghi nhận" }}</strong></div>
                <div><span class="text-gray-500">ID bản ghi:</span> <strong>#{{ log?.model_id || "-" }}</strong></div>
            </div>
        </details>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import dayjs from "dayjs";
import { formatMoney, formatQuantity } from "@/config/helpers";

const props = defineProps({
    log: { type: Object, default: null },
});
const emit = defineEmits(["close"]);
const showItems = ref(true);

const actionLabels = {
    create: "Thêm mới",
    update: "Cập nhật",
    approve: "Duyệt",
    reject: "Từ chối",
    cancel: "Hủy",
    delete: "Xóa",
    lock: "Khóa",
    unlock: "Mở khóa",
};

const modelLabels = {
    User: "Nhân sự",
    Role: "Vai trò",
    Permission: "Quyền hạn",
    Company: "Công ty",
    Warehouse: "Kho hàng",
    WarehouseSlip: "Phiếu kho",
    WarehouseTransfer: "Phiếu chuyển kho",
    Product: "Sản phẩm",
    Category: "Danh mục",
    Unit: "Đơn vị tính",
    Supplier: "Nhà cung cấp",
    PurchaseOrder: "Đơn mua hàng",
    Customer: "Khách hàng",
    SalesOrder: "Đơn bán hàng",
    Currency: "Tiền tệ",
    Bank: "Ngân hàng",
    Account: "Tài khoản/quỹ",
    TransactionCategory: "Loại giao dịch",
    Transaction: "Giao dịch",
    Department: "Phòng ban",
    Position: "Chức vụ",
};

const fieldLabels = {
    code: "Mã",
    name: "Tên",
    username: "Tên đăng nhập",
    phone: "Số điện thoại",
    email: "Email",
    address: "Địa chỉ",
    address_detail: "Địa chỉ chi tiết",
    province_code: "Tỉnh/thành phố",
    ward_code: "Phường/xã",
    province_name: "Tên tỉnh/thành phố",
    ward_name: "Tên phường/xã",
    address_id: "Địa chỉ",
    tax_code: "Mã số thuế",
    company_name: "Tên công ty",
    contact_name: "Người liên hệ",
    opening_debt: "Công nợ đầu kỳ",
    opening_advance: "Ứng trước đầu kỳ",
    opening_balance: "Số dư đầu kỳ",
    current_balance: "Số dư hiện tại",
    current_debt: "Công nợ hiện tại",
    opening_debt_exchange_rate: "Tỷ giá công nợ đầu kỳ",
    opening_debt_base: "Công nợ đầu kỳ quy đổi",
    opening_advance_exchange_rate: "Tỷ giá ứng trước đầu kỳ",
    opening_advance_base: "Ứng trước đầu kỳ quy đổi",
    total_debts: "Tổng công nợ",
    total_advance: "Tổng tiền tạm ứng",
    total_inventory_value: "Tổng giá trị tồn kho",
    currency_id: "Tiền tệ",
    supplier_id: "Nhà cung cấp",
    customer_id: "Khách hàng",
    warehouse_id: "Kho hàng",
    from_warehouse_id: "Kho xuất",
    to_warehouse_id: "Kho nhận",
    category_id: "Danh mục",
    unit_id: "Đơn vị tính",
    product_id: "Sản phẩm",
    department_id: "Phòng ban",
    position_id: "Chức vụ",
    role_id: "Vai trò",
    manager_id: "Người quản lý",
    creater_id: "Người tạo",
    last_resubmitted_by: "Người gửi lại gần nhất",
    parent_id: "Danh mục cha",
    bank_id: "Ngân hàng",
    from_account_id: "Tài khoản nguồn",
    to_account_id: "Tài khoản nhận",
    purchase_order_id: "Đơn mua hàng",
    sales_order_id: "Đơn bán hàng",
    account_number: "Số tài khoản",
    bank_account_no: "Số tài khoản ngân hàng",
    short_name: "Tên viết tắt",
    symbol: "Ký hiệu tiền tệ",
    type: "Loại",
    payment_method: "Phương thức thanh toán",
    allow_decimal: "Cho phép số lượng lẻ",
    quantity: "Số lượng",
    received_quantity: "Số lượng đã nhận",
    price: "Đơn giá",
    unit_price: "Đơn giá",
    company_price: "Đơn giá quy đổi",
    amount: "Số tiền",
    amount_base: "Số tiền quy đổi",
    subtotal: "Tiền hàng",
    vat_amount: "Tiền VAT",
    total_amount: "Tổng tiền",
    total_value: "Tổng giá trị",
    debt_amount: "Số tiền công nợ",
    paid_amount: "Số tiền đã thanh toán",
    discount_amount: "Số tiền giảm giá",
    tendered_amount: "Số tiền khách đưa",
    change_amount: "Tiền thừa trả khách",
    advance_applied_amount: "Tiền tạm ứng đã cấn trừ",
    advance_applied_base: "Tiền tạm ứng cấn trừ quy đổi",
    cod_amount: "Số tiền thu hộ COD",
    shipping_fee: "Phí giao hàng thu khách",
    carrier_shipping_fee: "Phí trả đơn vị vận chuyển",
    carrier_service_fee: "Phí dịch vụ vận chuyển",
    carrier_insurance_fee: "Phí bảo hiểm hàng hóa",
    discount: "Chiết khấu",
    vat_percent: "VAT (%)",
    exchange_rate: "Tỷ giá",
    purchase_price: "Giá mua",
    sell_price: "Giá bán",
    promotional_price: "Giá khuyến mãi",
    promotion_starts_at: "Bắt đầu khuyến mãi",
    promotion_ends_at: "Kết thúc khuyến mãi",
    sku: "Mã SKU",
    barcode: "Mã vạch",
    image: "Hình ảnh",
    avatar: "Ảnh đại diện",
    logo: "Biểu trưng",
    note: "Ghi chú",
    description: "Mô tả",
    rejection_reason: "Lý do từ chối",
    status: "Trạng thái",
    approved_at: "Thời gian duyệt",
    approved_by: "Người duyệt",
    rejected_at: "Thời gian từ chối",
    rejected_by: "Người từ chối",
    created_by: "Người tạo",
    user_id: "Nhân sự",
    submitted_to_accountant_by: "Người gửi kế toán",
    return_requested_by: "Người yêu cầu trả hàng",
    return_received_by: "Người nhận hàng trả",
    return_approved_by: "Người duyệt trả hàng",
    expected_date: "Ngày dự kiến",
    transaction_date: "Ngày giao dịch",
    expected_delivery_date: "Ngày giao dự kiến",
    expected_received_date: "Ngày nhận dự kiến",
    submitted_at: "Thời gian gửi duyệt",
    shipping_started_at: "Thời gian bắt đầu giao",
    completed_at: "Thời gian hoàn thành",
    cod_collected_at: "Thời gian thu COD",
    cod_reconciled_at: "Thời gian đối soát COD",
    returned_at: "Thời gian hoàn trả",
    return_requested_at: "Thời gian yêu cầu trả hàng",
    return_received_at: "Thời gian nhận hàng trả",
    return_approved_at: "Thời gian duyệt trả hàng",
    submitted_to_accountant_at: "Thời gian gửi kế toán",
    resubmit_expires_at: "Hạn gửi lại hồ sơ",
    last_resubmitted_at: "Thời gian gửi lại gần nhất",
    last_login_at: "Lần đăng nhập gần nhất",
    last_login_ip: "Địa chỉ IP đăng nhập gần nhất",
    zalo_verified: "Đã xác thực Zalo",
    zalo_verified_at: "Thời gian xác thực Zalo",
    zalo_user_id: "Tài khoản Zalo",
    is_employee: "Là nhân viên",
    is_active: "Đang hoạt động",
    storefront_visible: "Hiển thị trên cửa hàng",
    rejection_count: "Số lần từ chối",
    rejection_type: "Hình thức từ chối",
    return_reason: "Lý do trả hàng",
    created_at: "Ngày tạo",
    updated_at: "Ngày cập nhật",
    coupon_code: "Mã giảm giá",
    coupon_code_snapshot: "Mã giảm giá",
    coupon_name_snapshot: "Tên chương trình giảm giá",
    coupon_type_snapshot: "Loại giảm giá",
    coupon_value_snapshot: "Giá trị giảm giá",
    invoice_type: "Loại hóa đơn",
    sales_channel: "Kênh bán hàng",
    payment_status: "Trạng thái thanh toán",
    cod_status: "Trạng thái COD",
    return_status: "Trạng thái trả hàng",
    shipping_method: "Phương thức giao hàng",
    shipping_partner_id: "Đơn vị vận chuyển",
    tracking_code: "Mã vận đơn",
    shipping_note: "Ghi chú giao hàng",
    cancellation_reason: "Lý do hủy",
    recipient_name: "Người nhận hàng",
    recipient_phone: "Số điện thoại người nhận",
    recipient_email: "Email người nhận",
    customer_account_id: "Tài khoản khách hàng",
    province_id: "Tỉnh/thành phố",
    ward_id: "Phường/xã",
    pos_warehouse_id: "Kho bán hàng",
    pos_coupon_id: "Chương trình giảm giá",
    payment_currency_id: "Tiền tệ thanh toán",
    payment_exchange_rate: "Tỷ giá thanh toán",
    payment_tendered_amount: "Số tiền thanh toán nguyên tệ",
    payment_reference: "Mã tham chiếu thanh toán",
    reference_type: "Loại chứng từ tham chiếu",
    reference_id: "Chứng từ tham chiếu",
    purpose: "Nghiệp vụ",
    effective_status: "Trạng thái hiện tại",
    currency: "Tiền tệ",
    payment_currency: "Tiền tệ thanh toán",
    customer: "Khách hàng",
    supplier: "Nhà cung cấp",
    warehouse: "Kho hàng",
    category: "Danh mục",
    transaction_category: "Loại giao dịch",
    unit: "Đơn vị tính",
    product: "Sản phẩm",
    department: "Phòng ban",
    position: "Chức vụ",
    bank: "Ngân hàng",
    from_account: "Tài khoản nguồn",
    to_account: "Tài khoản nhận",
    sales_order: "Đơn bán hàng",
    purchase_order: "Đơn mua hàng",
    shipping_partner: "Đơn vị vận chuyển",
    province: "Tỉnh/thành phố",
    ward: "Phường/xã",
    creator: "Người tạo",
    approver: "Người duyệt",
    rejecter: "Người từ chối",
};

const statusLabels = {
    pending: "Chờ xử lý",
    approved: "Đã duyệt",
    rejected: "Đã từ chối",
    cancelled: "Đã hủy",
    partial: "Thực hiện một phần",
    completed: "Đã hoàn thành",
    active: "Đang hoạt động",
    inactive: "Ngừng hoạt động",
    locked: "Đã khóa",
};
const purposeLabels = {
    customer_receipt: "Thu tiền khách hàng",
    opening_customer_receipt: "Thu công nợ đầu kỳ",
    customer_advance: "Khách hàng tạm ứng",
    customer_advance_refund: "Hoàn tạm ứng khách hàng",
    supplier_payment: "Thanh toán nhà cung cấp",
    opening_supplier_payment: "Thanh toán công nợ đầu kỳ",
    supplier_advance: "Tạm ứng nhà cung cấp",
    supplier_advance_refund: "Hoàn tạm ứng nhà cung cấp",
    internal_transfer: "Chuyển tiền nội bộ",
    cod_receipt: "Thu đối soát COD",
};
const paymentMethodLabels = {
    cash: "Tiền mặt",
    bank_transfer: "Chuyển khoản",
    cod: "Thu hộ COD",
    momo: "Ví MoMo",
};
const typeLabels = {
    income: "Thu tiền",
    expense: "Chi tiền",
    receipt: "Thu tiền",
    payment: "Chi tiền",
    transfer: "Chuyển quỹ",
    import: "Phiếu nhập kho",
    export: "Phiếu xuất kho",
    sale: "Bán hàng",
    purchase: "Mua hàng",
    cash: "Tiền mặt",
    bank: "Tài khoản ngân hàng",
    product: "Hàng hóa",
    service: "Dịch vụ",
};
const valueLabelsByField = {
    invoice_type: { vat: "Hóa đơn VAT", retail: "Hóa đơn bán lẻ" },
    sales_channel: { pos: "Bán tại quầy", storefront: "Bán hàng trực tuyến", internal: "Nội bộ" },
    payment_status: { unpaid: "Chưa thanh toán", partial: "Thanh toán một phần", paid: "Đã thanh toán", refunded: "Đã hoàn tiền" },
    cod_status: { pending: "Chờ thu hộ", shipping: "Đang giao hàng", collected: "Đã thu hộ", reconciled: "Đã đối soát", failed: "Thu hộ thất bại" },
    return_status: { none: "Không có trả hàng", pending: "Chờ xử lý trả hàng", partial: "Trả một phần", returned: "Đã trả hàng", completed: "Đã hoàn tất trả hàng" },
    shipping_method: { standard: "Giao hàng tiêu chuẩn", express: "Giao hàng nhanh", pickup: "Nhận tại cửa hàng" },
    coupon_type_snapshot: { percent: "Giảm theo phần trăm", fixed: "Giảm số tiền cố định" },
};
const emptyValueLabels = {
    completed_at: "Chưa hoàn thành",
    approved_at: "Chưa duyệt",
    rejected_at: "Chưa từ chối",
    submitted_at: "Chưa gửi duyệt",
    shipping_started_at: "Chưa bắt đầu giao hàng",
    cod_collected_at: "Chưa thu COD",
    cod_reconciled_at: "Chưa đối soát COD",
    returned_at: "Chưa hoàn trả",
    cancellation_reason: "Không có lý do hủy",
    payment_reference: "Không có mã tham chiếu",
    tracking_code: "Chưa có mã vận đơn",
};

const moneyFields = new Set([
    "opening_debt", "opening_advance", "opening_balance", "current_balance",
    "current_debt", "price", "unit_price", "company_price", "amount",
    "amount_base", "subtotal", "vat_amount", "total_amount", "total_value",
    "debt_amount", "paid_amount", "discount_amount", "tendered_amount", "change_amount",
    "advance_applied_amount", "advance_applied_base", "cod_amount", "shipping_fee",
    "carrier_shipping_fee", "carrier_service_fee", "carrier_insurance_fee",
    "payment_tendered_amount", "coupon_value_snapshot",
]);
const dateFields = new Set([
    "approved_at", "rejected_at", "created_at", "updated_at",
    "expected_date", "transaction_date", "expected_delivery_date", "expected_received_date",
    "submitted_at", "shipping_started_at", "completed_at", "cod_collected_at",
    "cod_reconciled_at", "returned_at", "return_requested_at", "return_received_at",
    "return_approved_at", "submitted_to_accountant_at", "promotion_starts_at",
    "promotion_ends_at", "resubmit_expires_at", "last_resubmitted_at",
    "last_login_at", "zalo_verified_at",
]);
const booleanFields = new Set([
    "allow_decimal", "is_active", "is_employee", "storefront_visible", "zalo_verified",
]);
const hiddenFields = new Set([
    "id", "company_id", "items", "stock_impact", "password", "remember_token",
    "created_at", "updated_at", "deleted_at",
    "reference_type", "effective_status",
]);

const actorInitial = computed(() => (props.log?.user?.name || "H").charAt(0).toUpperCase());
const actionKey = computed(() => props.log?.action_key || props.log?.action || "");
const actionLabel = computed(() => actionLabels[actionKey.value] || "Hành động nghiệp vụ");
const modelName = computed(() => (props.log?.model_type || "").split("\\").pop());
const modelLabel = computed(() => modelLabels[modelName.value] || "Dữ liệu hệ thống");
const displayedTime = computed(() => props.log?.created_at_formatted || (props.log?.created_at ? dayjs(props.log.created_at).format("DD/MM/YYYY HH:mm:ss") : "Không ghi nhận"));
const actionBadgeClass = computed(() => ({
    create: "bg-blue-100 text-blue-700",
    update: "bg-amber-100 text-amber-700",
    approve: "bg-green-100 text-green-700",
    reject: "bg-red-100 text-red-700",
    cancel: "bg-orange-100 text-orange-700",
    delete: "bg-red-100 text-red-700",
    lock: "bg-slate-200 text-slate-700",
    unlock: "bg-sky-100 text-sky-700",
}[actionKey.value] || "bg-gray-100 text-gray-700"));
const isCreate = computed(() => actionKey.value === "create");
const isDelete = computed(() => actionKey.value === "delete");
const changeSectionTitle = computed(() => {
    if (isCreate.value) return "Thông tin đã tạo";
    if (isDelete.value) return "Thông tin trước khi xóa";
    return "Nội dung thay đổi";
});
const changeSectionHint = computed(() => {
    if (isCreate.value) return "Các thông tin chính được ghi nhận khi tạo mới.";
    if (isDelete.value) return "Dữ liệu cuối cùng được ghi nhận trước khi xóa.";
    return "Chỉ hiển thị những trường có giá trị thay đổi.";
});

const fieldDiffs = computed(() => {
    const oldValues = props.log?.old_values || {};
    const newValues = props.log?.new_values || {};
    const keys = new Set([...Object.keys(oldValues), ...Object.keys(newValues)]);

    return [...keys]
        .filter((key) => !hiddenFields.has(key))
        .map((key) => ({
            key,
            label: fieldLabels[key] || humanizeField(key),
            oldVal: oldValues[key],
            newVal: newValues[key],
        }))
        .filter((field) => JSON.stringify(field.oldVal) !== JSON.stringify(field.newVal));
});

const itemsList = computed(() => props.log?.new_values?.items || props.log?.old_values?.items || []);

function humanizeField(key) {
    const wordLabels = {
        account: "tài khoản", active: "hoạt động", address: "địa chỉ", advance: "tạm ứng",
        amount: "số tiền", approved: "duyệt", balance: "số dư", base: "quy đổi",
        category: "danh mục", code: "mã", company: "công ty", count: "số lần",
        created: "tạo", customer: "khách hàng", date: "ngày", debt: "công nợ",
        delivery: "giao hàng", department: "phòng ban", description: "mô tả",
        discount: "giảm giá", email: "email", expected: "dự kiến", exchange: "tỷ giá",
        fee: "phí", from: "nguồn", image: "hình ảnh", invoice: "hóa đơn",
        manager: "quản lý", method: "phương thức", name: "tên", note: "ghi chú",
        opening: "đầu kỳ", order: "đơn hàng", paid: "đã thanh toán", payment: "thanh toán",
        phone: "số điện thoại", position: "chức vụ", price: "đơn giá", product: "sản phẩm",
        purchase: "mua hàng", quantity: "số lượng", rate: "tỷ giá", reason: "lý do",
        recipient: "người nhận", reference: "tham chiếu", rejected: "từ chối",
        return: "trả hàng", role: "vai trò", sale: "bán hàng", sales: "bán hàng",
        shipping: "giao hàng", status: "trạng thái", supplier: "nhà cung cấp",
        symbol: "ký hiệu", to: "nhận", total: "tổng", transaction: "giao dịch",
        type: "loại", unit: "đơn vị", updated: "cập nhật", user: "nhân sự",
        value: "giá trị", warehouse: "kho hàng",
    };
    const words = key.replace(/_id$/, "").split("_").map((word) => wordLabels[word] || word);
    const label = words.join(" ");
    return label.charAt(0).toUpperCase() + label.slice(1);
}

function formatValue(value, key, emptyLabel) {
    if (value === null || value === undefined || value === "") return emptyValueLabels[key] || emptyLabel;
    const relationLabel = props.log?.relation_labels?.[key]?.[String(value)];
    if (relationLabel) return relationLabel;
    if (key === "status") {
        if ([0, "0"].includes(value)) return "Ngừng hoạt động";
        if ([1, "1"].includes(value)) return "Đang hoạt động";
        return statusLabels[value] || String(value);
    }
    if (key === "type") return typeLabels[value] || String(value);
    if (key === "purpose") return purposeLabels[value] || String(value);
    if (key === "payment_method") return paymentMethodLabels[value] || String(value);
    if (valueLabelsByField[key]?.[value]) return valueLabelsByField[key][value];
    if (booleanFields.has(key) && [0, 1, "0", "1"].includes(value)) return Number(value) === 1 ? "Có" : "Không";
    if (key.endsWith("_id") && !Number.isNaN(Number(value))) return `#${value}`;
    if (moneyFields.has(key) && !Number.isNaN(Number(value))) return formatMoney(Number(value));
    if (dateFields.has(key)) return dayjs(value).isValid() ? dayjs(value).format("DD/MM/YYYY HH:mm:ss") : String(value);
    if (typeof value === "boolean") return value ? "Có" : "Không";
    if (Array.isArray(value)) return `${value.length} mục`;
    if (typeof value === "object") return formatObjectValue(value);
    if (typeof value === "number") return value.toLocaleString("vi-VN");
    return String(value);
}

function formatObjectValue(value) {
    if (!value || typeof value !== "object") return "Chưa có thông tin";

    const name = value.name || value.full_name || value.title;
    const code = value.code || value.username || value.email;
    if (name && code) return `${name} (${code})`;
    if (name) return String(name);
    if (value.code && value.symbol) return `${value.code} (${value.symbol})`;
    if (value.code) return String(value.code);

    const readableParts = Object.entries(value)
        .filter(([key, item]) => key !== "id" && item !== null && item !== undefined && typeof item !== "object")
        .map(([key, item]) => `${fieldLabels[key] || humanizeField(key)}: ${formatValue(item, key, "Chưa có")}`);

    if (readableParts.length) return readableParts.join(" · ");
    if (value.id) return `Bản ghi #${value.id}`;
    return "Thông tin liên quan";
}
</script>
