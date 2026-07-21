<template>
    <div class="bg-white rounded-xl">
        <!-- ================= HEADER ================= -->
        <div class="flex items-start justify-between border-b pb-4 mb-5">
            <div class="flex gap-4">
                <div
                    class="w-12 h-12 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg"
                >
                    {{
                        log?.user?.name
                            ? log.user.name.charAt(0).toUpperCase()
                            : "S"
                    }}
                </div>

                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ log?.user?.name ?? "Hệ thống" }}
                        </h3>

                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-semibold"
                            :class="actionBadgeClass(log?.action)"
                        >
                            {{ actionLabel(log?.action) }}
                        </span>
                    </div>

                    <p class="text-gray-500 mt-1">
                        {{ shortModelName(log?.model_type) }}
                        #{{ log?.model_id }}
                    </p>

                    <div
                        class="flex items-center gap-5 mt-2 text-xs text-gray-400"
                    >
                        <span> 🕒 {{ formatDateTime(log?.created_at) }} </span>

                        <span>🌐 {{ log?.ip_address ?? "-" }}</span>
                    </div>
                </div>
            </div>

            <button
                @click="emit('close')"
                class="w-9 h-9 rounded-lg hover:bg-gray-100 transition flex items-center justify-center"
            >
                ✕
            </button>
        </div>

        <!-- ================= VIEW ================= -->

        <div
            v-if="log?.action === 'view'"
            class="rounded-xl border border-blue-200 bg-blue-50 p-6 text-center"
        >
            <div class="text-4xl mb-2">👁️</div>

            <p class="font-medium text-blue-700">Người dùng chỉ xem dữ liệu.</p>

            <p class="text-sm text-gray-500 mt-1">
                Không có thay đổi nào được thực hiện.
            </p>
        </div>

        <!-- ================= CHANGE ================= -->

        <div v-else class="border rounded-xl overflow-hidden border-gray-200">
            <div
                class="grid grid-cols-12 bg-gray-50 border-b text-xs font-semibold uppercase tracking-wide text-gray-600"
            >
                <div class="col-span-3 px-4 py-3">Trường dữ liệu</div>

                <div class="col-span-4 px-4 py-3">Giá trị cũ</div>

                <div class="col-span-1"></div>

                <div class="col-span-4 px-4 py-3">Giá trị mới</div>
            </div>

            <div
                v-for="field in fieldDiffs"
                :key="field.key"
                class="grid grid-cols-12 border-b last:border-0 hover:bg-gray-50 transition"
            >
                <div
                    class="col-span-3 px-4 py-3 font-medium text-gray-700 bg-gray-50"
                >
                    {{ field.label }}
                </div>

                <div class="col-span-4 px-4 py-3">
                    <div
                        class="rounded-lg border border-red-200 bg-red-50 p-2 text-red-700 break-all"
                    >
                        {{ formatValue(field.oldVal, field.key) }}
                    </div>
                </div>

                <div
                    class="col-span-1 flex items-center justify-center text-gray-400"
                >
                    →
                </div>

                <div class="col-span-4 px-4 py-3">
                    <div
                        class="rounded-lg border border-green-200 bg-green-50 p-2 text-green-700 break-all"
                    >
                        {{ formatValue(field.newVal, field.key) }}
                    </div>
                </div>
            </div>

            <div
                v-if="!fieldDiffs.length"
                class="py-10 text-center text-gray-400"
            >
                Không có thay đổi dữ liệu.
            </div>
        </div>

        <!-- ================= ITEMS ================= -->

        <div
            v-if="itemsList.length"
            class="mt-6 rounded-xl border border-gray-200 overflow-hidden"
        >
            <button
                class="w-full flex justify-between items-center px-4 py-3 bg-gray-50 hover:bg-gray-100 transition"
                @click="showItems = !showItems"
            >
                <span class="font-semibold"> Danh sách mặt hàng </span>

                <span
                    class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs"
                >
                    {{ itemsList.length }}
                </span>
            </button>

            <div v-show="showItems">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 w-14 text-center">STT</th>

                            <th class="text-left">Sản phẩm</th>

                            <th class="text-right">SL</th>

                            <th class="text-right">Đơn giá</th>

                            <th class="text-right pr-5">Thành tiền</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(item, index) in itemsList"
                            :key="index"
                            class="border-t hover:bg-gray-50"
                        >
                            <td class="text-center py-3">
                                {{ index + 1 }}
                            </td>

                            <td>
                                {{ item.product_name ?? item.name ?? "-" }}
                            </td>

                            <td class="text-right">
                                {{ formatQuantity(item.quantity) }}
                            </td>

                            <td class="text-right">
                                {{ formatMoney(item.price ?? 0) }}
                            </td>

                            <td class="text-right pr-5 font-semibold">
                                {{
                                    formatMoney(
                                        (item.quantity ?? 0) *
                                            (item.price ?? 0),
                                    )
                                }}
                            </td>
                        </tr>
                    </tbody>

                    <tfoot class="bg-gray-50 border-t">
                        <tr>
                            <td
                                colspan="4"
                                class="text-right py-3 font-semibold"
                            >
                                Tổng cộng
                            </td>

                            <td class="text-right pr-5 font-bold text-blue-700">
                                {{ formatMoney(totalAmount) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import dayjs from "dayjs";
import { formatMoney, formatQuantity } from "@/config/helpers";

const props = defineProps({
    log: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close"]);

/*
|--------------------------------------------------------------------------
| UI STATE
|--------------------------------------------------------------------------
*/

const showItems = ref(true);

/*
|--------------------------------------------------------------------------
| LABELS
|--------------------------------------------------------------------------
*/

const actionLabels = {
    create: "Tạo mới",
    update: "Cập nhật",
    approve: "Duyệt",
    reject: "Từ chối",
    delete: "Xóa",
    view: "Xem",
    lock: "Khóa",
    unlock: "Mở khóa",
};

const fieldLabels = {
    code: "Mã",

    name: "Tên",

    phone: "Điện thoại",

    email: "Email",

    address: "Địa chỉ",

    tax_code: "Mã số thuế",

    company_name: "Tên công ty",

    contact_name: "Người liên hệ",

    opening_debt: "Công nợ đầu kỳ",

    current_debt: "Công nợ hiện tại",

    currency_id: "Tiền tệ",

    supplier_id: "Nhà cung cấp",

    customer_id: "Khách hàng",

    warehouse_id: "Kho",

    category_id: "Danh mục",

    unit_id: "Đơn vị",

    product_id: "Sản phẩm",

    quantity: "Số lượng",

    price: "Đơn giá",

    amount: "Thành tiền",

    total_amount: "Tổng tiền",

    discount: "Chiết khấu",

    vat_percent: "VAT (%)",

    note: "Ghi chú",

    status: "Trạng thái",

    approved_at: "Ngày duyệt",

    approved_by: "Người duyệt",

    created_at: "Ngày tạo",

    updated_at: "Ngày cập nhật",
};

/*
|--------------------------------------------------------------------------
| BADGE
|--------------------------------------------------------------------------
*/

function actionBadgeClass(action) {
    switch (action) {
        case "create":
            return "bg-green-100 text-green-700";

        case "update":
            return "bg-blue-100 text-blue-700";

        case "approve":
            return "bg-emerald-100 text-emerald-700";

        case "reject":
            return "bg-red-100 text-red-700";

        case "delete":
            return "bg-red-100 text-red-700";

        case "lock":
            return "bg-orange-100 text-orange-700";

        case "unlock":
            return "bg-sky-100 text-sky-700";

        default:
            return "bg-gray-100 text-gray-700";
    }
}

function actionLabel(action) {
    return actionLabels[action] ?? action ?? "-";
}

function shortModelName(model) {
    if (!model) return "-";

    return model.split("\\").pop();
}

/*
|--------------------------------------------------------------------------
| FORMAT
|--------------------------------------------------------------------------
*/

function formatValue(value, key = "") {
    if (value === null || value === undefined || value === "") return "-";

    if (
        [
            "opening_debt",
            "current_debt",
            "price",
            "amount",
            "total_amount",
        ].includes(key)
    ) {
        return formatMoney(Number(value));
    }

    if (key === "status") {
        return value === "active" ? "🟢 Đang hoạt động" : "🔴 Ngừng hoạt động";
    }

    if (typeof value === "boolean") {
        return value ? "Có" : "Không";
    }

    if (Array.isArray(value)) {
        return `${value.length} mục`;
    }

    if (typeof value === "number") {
        return value.toLocaleString("vi-VN");
    }

    if (typeof value === "object") {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
}
function formatDateTime(value) {
    if (!value) return "-";

    // Nếu backend đã trả về created_at_formatted thì dùng luôn
    if (typeof value === "string" && value.includes("/")) {
        return value; // đã format rồi
    }

    // Format từ ISO (2026-07-16T...)
    return dayjs(value).format("DD/MM/YYYY HH:mm:ss");
}
/*
|--------------------------------------------------------------------------
| FIELD DIFFS
|--------------------------------------------------------------------------
*/

const fieldDiffs = computed(() => {
    const oldValues = props.log?.old_values ?? {};
    const newValues = props.log?.new_values ?? {};

    const keys = new Set([
        ...Object.keys(oldValues),
        ...Object.keys(newValues),
    ]);

    keys.delete("items");
    keys.delete("stock_impact");

    return [...keys]
        .map((key) => {
            const oldVal = oldValues[key];
            const newVal = newValues[key];

            return {
                key,
                label: fieldLabels[key] ?? key,
                oldVal,
                newVal,
                changed: JSON.stringify(oldVal) !== JSON.stringify(newVal),
            };
        })
        .filter((item) => item.changed);
});

/*
|--------------------------------------------------------------------------
| ITEMS
|--------------------------------------------------------------------------
*/

const itemsList = computed(() => {
    return props.log?.new_values?.items ?? props.log?.old_values?.items ?? [];
});

const totalAmount = computed(() => {
    return itemsList.value.reduce((sum, item) => {
        return sum + Number(item.quantity ?? 0) * Number(item.price ?? 0);
    }, 0);
});
</script>
