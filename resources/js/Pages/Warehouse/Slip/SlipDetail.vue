<template>
    <div
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div
            class="bg-white w-full max-w-6xl rounded-xl shadow-xl p-6 relative max-h-[90vh] overflow-y-auto"
        >
            <!-- CLOSE -->
            <button
                class="absolute top-4 right-4 text-2xl text-gray-500 hover:text-red-500"
                @click="$emit('close')"
            >
                ✕
            </button>

            <!-- LOADING -->
            <div v-if="loading" class="text-center py-10">
                Đang tải dữ liệu phiếu...
            </div>

            <div v-else-if="slip">
                <!-- HEADER -->
                <div
                    class="flex justify-between items-start border-b pb-4 mb-6"
                >
                    <div>
                        <h2
                            class="text-3xl font-bold tracking-tight text-gray-900"
                        >
                            {{ slip.code }}
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            <span class="font-medium text-gray-700"
                                >Ngày tạo:</span
                            >
                            {{ formatDate(slip.created_at) }}
                            •
                            <span class="font-medium text-gray-700">Kho:</span>
                            {{ slip.warehouse?.name }}
                        </p>
                        <span
                            class="px-4 py-1.5 rounded-full text-xs font-semibold shadow-sm"
                            :class="statusClass(slip.status)"
                        >
                            {{ statusText(slip.status) }}
                        </span>
                    </div>
                </div>

                <!-- TOP INFO GRID -->
                <div
                    class="p-4 bg-white border rounded-xl shadow-sm hover:shadow transition"
                >
                    <!-- Kho -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400 uppercase">
                            Kho thực hiện
                        </p>
                        <p class="font-semibold text-gray-900 text-lg">
                            {{ slip.warehouse?.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Mã: {{ slip.warehouse?.code || "-" }}
                        </p>
                    </div>

                    <!-- Đối tác -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400 uppercase">Đối tác</p>

                        <p class="font-semibold text-gray-900 text-lg">
                            {{
                                slip.sales_order?.customer?.name ||
                                slip.purchase_order?.supplier?.name ||
                                "-"
                            }}
                        </p>

                        <p class="text-xs text-gray-500">
                            {{
                                slip.type === "export"
                                    ? "Khách hàng"
                                    : "Nhà cung cấp"
                            }}
                        </p>
                    </div>

                    <!-- Người xử lý -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400 uppercase">
                            Người xử lý
                        </p>

                        <div class="text-sm text-gray-700 space-y-1">
                            <p>
                                Tạo:
                                <b>{{ slip.created_by?.name || "-" }}</b>
                            </p>
                            <p>
                                Duyệt:
                                <b>{{ slip.approved_by?.name || "-" }}</b>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ITEMS -->
                <div class="mb-6">
                    <h3 class="font-bold mb-3">
                        Danh sách sản phẩm ({{ slip.items?.length || 0 }})
                    </h3>

                    <div
                        class="mb-6 bg-white border rounded-xl shadow-sm overflow-hidden"
                    >
                        <table class="w-full">
                            <thead
                                class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide"
                            >
                                <tr
                                    class="border-b hover:bg-gray-50 transition"
                                >
                                    <th class="p-3 text-left">Sản phẩm</th>
                                    <th class="p-3 text-center">Đơn vị</th>
                                    <th class="p-3 text-center">Số lượng</th>
                                    <th class="p-3 text-right">Đơn giá</th>
                                    <th class="p-3 text-right">Thành tiền</th>
                                    <th class="p-3 text-center">
                                        Tồn ảnh hưởng
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="i in slip.items"
                                    :key="i.id"
                                    class="border-b hover:bg-gray-50"
                                >
                                    <td class="p-3 font-medium">
                                        {{ i.product?.name }}
                                    </td>

                                    <td class="p-3 text-center text-gray-600">
                                        {{ i.product?.unit?.name || "-" }}
                                    </td>

                                    <td class="p-3 text-center">
                                        {{ i.quantity }}
                                    </td>

                                    <td
                                        class="p-3 text-right font-medium text-gray-900"
                                    >
                                        {{ formatMoney(i.price) }}
                                    </td>

                                    <td
                                        class="p-3 text-right font-bold text-emerald-600"
                                    >
                                        {{ formatMoney(i.price * i.quantity) }}
                                    </td>

                                    <td
                                        class="p-3 text-center font-semibold"
                                        :class="
                                            slip.type === 'export'
                                                ? 'text-red-500'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{ slip.type === "export" ? "-" : "+" }}
                                        {{ i.quantity }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SUMMARY -->
                <div class="flex justify-end mb-8">
                    <div
                        class="bg-white border rounded-xl px-6 py-4 shadow-sm text-right"
                    >
                        <p class="text-xs text-gray-400 uppercase">
                            Tổng số lượng
                        </p>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ totalQuantity }}
                        </p>
                    </div>
                </div>

                <!-- ERP TIMELINE -->
                <div class="bg-white border rounded-xl p-4 shadow-sm">
                    <h3 class="font-bold mb-3">ERP Timeline</h3>

                    <div class="border-l-2 pl-4 space-y-4">
                        <div
                            v-for="log in slip.logs"
                            :key="log.id"
                            class="flex gap-3 mb-4"
                        >
                            <div
                                class="w-2.5 h-2.5 mt-2 rounded-full bg-blue-500"
                            ></div>

                            <div>
                                <p class="font-semibold">
                                    {{ log.user?.name }}
                                </p>

                                <p class="text-sm">
                                    <span class="font-bold">{{
                                        log.action
                                    }}</span>
                                    - {{ log.description }}
                                </p>

                                <div v-if="log.new_values?.stock_impact">
                                    <p class="text-xs text-gray-500">
                                        Stock impact:
                                    </p>

                                    <div
                                        v-for="item in log.new_values
                                            .stock_impact"
                                        :key="item.product_id"
                                        class="text-xs"
                                    >
                                        Product #{{ item.product_id }}:
                                        <span
                                            :class="
                                                item.qty_change > 0
                                                    ? 'text-green-600'
                                                    : 'text-red-600'
                                            "
                                        >
                                            {{ item.qty_change }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-10 text-gray-500">
                Không có dữ liệu phiếu
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import axios from "axios";

const props = defineProps({
    slipId: Number,
});
function formatMoney(value, currency = "VND") {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: currency,
    }).format(value || 0);
}
const emit = defineEmits(["close"]);

const slip = ref(null);
const loading = ref(false);

const totalQuantity = computed(() =>
    (slip.value?.items || []).reduce((s, i) => s + i.quantity, 0),
);

watch(
    () => props.slipId,
    async (id) => {
        if (!id) return;

        loading.value = true;
        slip.value = null; // 👈 thêm dòng này

        try {
            const res = await axios.get(`/api/warehouse/slips/${id}`);
            slip.value = res.data; // OK vì data của bạn đúng format
        } finally {
            loading.value = false;
        }
    },
    { immediate: true, flush: "post" },
);

function formatDate(date) {
    if (!date) return "-";
    return new Date(date).toLocaleString("vi-VN");
}

function statusText(s) {
    return (
        {
            pending: "Chờ duyệt",
            approved: "Đã duyệt",
            rejected: "Từ chối",
        }[s] || "-"
    );
}

function statusClass(s) {
    return (
        {
            pending: "bg-yellow-100 text-yellow-700",
            approved: "bg-green-100 text-green-700",
            rejected: "bg-red-100 text-red-700",
        }[s] || ""
    );
}
</script>
