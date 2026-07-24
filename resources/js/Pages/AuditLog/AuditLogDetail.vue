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
                            {{ log?.model_label || modelLabel }} · Mã bản ghi #{{ log?.model_id || "-" }}
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
                    <span class="block text-xs text-gray-500">Địa chỉ IP</span>
                    <span class="font-medium">{{ log?.ip_address || "Không ghi nhận" }}</span>
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
                {{ log?.description || `${actionLabel} ${modelLabel.toLowerCase()}` }}
            </p>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                <h4 class="font-semibold">Dữ liệu thay đổi</h4>
                <p class="mt-0.5 text-xs text-gray-500">Chỉ hiển thị những trường thực sự thay đổi.</p>
            </div>

            <div v-if="fieldDiffs.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                <div v-for="field in fieldDiffs" :key="field.key" class="grid gap-3 p-4 md:grid-cols-[180px_1fr_32px_1fr] md:items-center">
                    <div class="font-medium text-gray-700 dark:text-gray-200">{{ field.label }}</div>
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 break-words dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">
                        <span class="mb-1 block text-[11px] font-semibold uppercase opacity-70">Trước thay đổi</span>
                        {{ formatValue(field.oldVal, field.key, "Chưa có") }}
                    </div>
                    <div class="hidden text-center text-gray-400 md:block">→</div>
                    <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800 break-words dark:border-green-900 dark:bg-green-950/30 dark:text-green-200">
                        <span class="mb-1 block text-[11px] font-semibold uppercase opacity-70">Sau thay đổi</span>
                        {{ formatValue(field.newVal, field.key, "Đã xóa") }}
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
    tax_code: "Mã số thuế",
    company_name: "Tên công ty",
    contact_name: "Người liên hệ",
    opening_debt: "Công nợ đầu kỳ",
    opening_advance: "Ứng trước đầu kỳ",
    opening_balance: "Số dư đầu kỳ",
    current_balance: "Số dư hiện tại",
    current_debt: "Công nợ hiện tại",
    currency_id: "Tiền tệ",
    supplier_id: "Nhà cung cấp",
    customer_id: "Khách hàng",
    warehouse_id: "Kho hàng",
    category_id: "Danh mục",
    unit_id: "Đơn vị tính",
    product_id: "Sản phẩm",
    department_id: "Phòng ban",
    position_id: "Chức vụ",
    role_id: "Vai trò",
    parent_id: "Danh mục cha",
    bank_id: "Ngân hàng",
    from_account_id: "Tài khoản nguồn",
    to_account_id: "Tài khoản nhận",
    purchase_order_id: "Đơn mua hàng",
    sales_order_id: "Đơn bán hàng",
    account_number: "Số tài khoản",
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
    discount: "Chiết khấu",
    vat_percent: "VAT (%)",
    exchange_rate: "Tỷ giá",
    purchase_price: "Giá mua",
    sell_price: "Giá bán",
    note: "Ghi chú",
    description: "Mô tả",
    rejection_reason: "Lý do từ chối",
    status: "Trạng thái",
    approved_at: "Thời gian duyệt",
    approved_by: "Người duyệt",
    rejected_at: "Thời gian từ chối",
    rejected_by: "Người từ chối",
    created_by: "Người tạo",
    expected_date: "Ngày dự kiến",
    transaction_date: "Ngày giao dịch",
    created_at: "Ngày tạo",
    updated_at: "Ngày cập nhật",
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

const moneyFields = new Set([
    "opening_debt", "opening_advance", "opening_balance", "current_balance",
    "current_debt", "price", "unit_price", "company_price", "amount",
    "amount_base", "subtotal", "vat_amount", "total_amount", "total_value",
]);
const dateFields = new Set([
    "approved_at", "rejected_at", "created_at", "updated_at",
    "expected_date", "transaction_date",
]);
const hiddenFields = new Set(["items", "stock_impact", "password", "remember_token"]);

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
    return key.replace(/_id$/, "").replaceAll("_", " ").replace(/^./, (char) => char.toUpperCase());
}

function formatValue(value, key, emptyLabel) {
    if (value === null || value === undefined || value === "") return emptyLabel;
    if (key === "status") return statusLabels[value] || String(value);
    if (moneyFields.has(key) && !Number.isNaN(Number(value))) return formatMoney(Number(value));
    if (dateFields.has(key)) return dayjs(value).isValid() ? dayjs(value).format("DD/MM/YYYY HH:mm:ss") : String(value);
    if (typeof value === "boolean") return value ? "Có" : "Không";
    if (Array.isArray(value)) return `${value.length} mục`;
    if (typeof value === "object") return JSON.stringify(value);
    if (typeof value === "number") return value.toLocaleString("vi-VN");
    return String(value);
}
</script>
