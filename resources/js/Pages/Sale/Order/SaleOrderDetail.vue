<template>
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-6xl p-6 relative z-50"
    >
        <!-- HEADER -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">
                    Đơn bán hàng #{{ order?.code }}
                </h2>
                <p class="text-sm text-gray-500">
                    Ngày tạo: {{ formatDate(order?.created_at) }}
                    • Trạng thái:
                    <span
                        :class="statusBadgeClass(order?.status)"
                        class="px-3 py-1 rounded-full text-sm font-medium"
                    >
                        {{ getStatusText(order?.status) }}
                    </span>
                </p>
            </div>
            <div class="flex gap-3">
                <button
                    v-if="order?.status !== 'cancelled'"
                    @click="duplicateOrder"
                    class="px-5 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 flex items-center gap-2"
                >
                    📋 Tạo đơn mới từ đơn này
                </button>
                <button
                    @click="$emit('close')"
                    class="text-gray-500 hover:text-red-500 text-3xl leading-none"
                >
                    ✕
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- THÔNG TIN ĐƠN HÀNG -->
            <div class="lg:col-span-7 bg-gray-50 rounded-xl p-6">
                <h3 class="font-semibold text-lg mb-4">
                    Thông tin đơn bán hàng
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Khách hàng</label
                        >
                        <p class="font-medium">
                            {{
                                order?.customer?.name ||
                                order?.customer?.full_name
                            }}
                            ({{ order?.customer?.code }})
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Tiền tệ</label
                        >
                        <p class="font-medium">
                            {{ order?.currency?.code }} -
                            {{ order?.currency?.name }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Ngày giao dự kiến</label
                        >
                        <p class="font-medium">
                            {{
                                order?.expected_delivery_date
                                    ? formatDate(order?.expected_delivery_date)
                                    : "Chưa có"
                            }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Người tạo</label
                        >
                        <p class="font-medium">
                            {{ order?.created_by?.name ?? "-" }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Người duyệt</label
                        >
                        <p class="font-medium">
                            {{ order?.approved_by?.name ?? "-" }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Địa chỉ giao hàng</label
                        >
                        <p class="text-gray-700">{{ order?.address_detail }}</p>
                        <p
                            v-if="order?.ward?.name || order?.province?.name"
                            class="text-gray-600 text-sm"
                        >
                            {{ order?.ward?.name }}, {{ order?.province?.name }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Ghi chú</label
                        >
                        <p class="text-gray-700 whitespace-pre-line">
                            {{ order?.note || "Không có ghi chú" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- TÓM TẮT THANH TOÁN -->
            <div class="lg:col-span-5 bg-white border rounded-xl p-6">
                <h3 class="font-semibold text-lg mb-4">Tóm tắt thanh toán</h3>
                <div class="space-y-4 text-lg">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tạm tính</span>
                        <span>{{
                            formatMoney(order?.subtotal || 0, order?.currency)
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">VAT</span>
                        <span>{{
                            formatMoney(order?.vat_amount || 0, order?.currency)
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t pt-4 text-2xl font-bold text-blue-700"
                    >
                        <span>Tổng tiền</span>
                        <span>{{
                            formatMoney(
                                order?.total_amount || 0,
                                order?.currency,
                            )
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DANH SÁCH SẢN PHẨM -->
        <div class="mt-8 bg-white border rounded-xl p-6">
            <h3 class="font-semibold text-lg mb-4">
                Danh sách sản phẩm ({{ order?.items?.length || 0 }})
            </h3>

            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full table-auto min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-3 text-left">Sản phẩm</th>
                            <th class="border p-3 text-center">SL Đặt</th>
                            <th class="border p-3 text-center">Đã xuất</th>
                            <th class="border p-3 text-right">Đơn giá</th>
                            <th class="border p-3 text-center">VAT %</th>
                            <th class="border p-3 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in order?.items"
                            :key="item.id"
                            class="hover:bg-gray-50 border-b"
                        >
                            <td class="border p-3 font-medium">
                                {{ item.product?.name }}
                            </td>
                            <td class="border p-3 text-center">
                                {{ formatQuantity(item.quantity) }}
                            </td>
                            <td class="border p-3 text-center text-green-600">
                                {{ formatQuantity(item.exported_quantity) }} /
                                {{ formatQuantity(item.quantity) }}
                            </td>
                            <td class="border p-3 text-right">
                                {{
                                    formatMoney(
                                        item.unit_price,
                                        order?.currency,
                                    )
                                }}
                            </td>
                            <td class="border p-3 text-center">
                                {{ item.vat_percent }}%
                            </td>
                            <td class="border p-3 text-right font-semibold">
                                {{
                                    formatMoney(
                                        item.total_amount ?? 0,
                                        order?.currency,
                                    )
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- LỊCH SỬ CÔNG NỢ (nếu có) -->
        <div
            v-if="order?.debtHistory && order?.debtHistory.length > 0"
            class="mt-8 bg-white border rounded-xl p-6"
        >
            <h3 class="font-semibold text-lg mb-4">Lịch sử ghi nhận công nợ</h3>
            <div class="space-y-4">
                <div
                    v-for="(debt, i) in order.debtHistory"
                    :key="i"
                    class="flex justify-between items-center p-4 bg-gray-50 rounded-lg"
                >
                    <div>
                        <p class="text-sm text-gray-500">
                            {{ formatDate(debt.created_at) }}
                        </p>
                        <p class="font-medium">{{ debt.description }}</p>
                    </div>
                    <p class="font-semibold text-red-600">
                        {{ formatMoney(debt.amount, order.currency) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { formatMoney, formatQuantity } from "@/config/helpers";

const emit = defineEmits(["close", "duplicate"]);

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

// Alias để template dùng ngắn gọn
const order = computed(() => props.order);

const getStatusText = (status) => {
    const map = {
        pending: "Chờ xử lý",
        approved: "Đã duyệt",
        partial: "Đã xuất một phần",
        completed: "Hoàn thành",
        cancelled: "Đã hủy",
    };

    return map[status] || status;
};

const statusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-700",
        approved: "bg-blue-100 text-blue-700",
        partial: "bg-purple-100 text-purple-700",
        completed: "bg-green-100 text-green-700",
        cancelled: "bg-red-100 text-red-700",
    };

    return classes[status] || "bg-gray-100 text-gray-700";
};

const formatDate = (date) => {
    if (!date) return "";

    return new Intl.DateTimeFormat("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }).format(new Date(date));
};

const duplicateOrder = () => {
    if (!order.value) return;

    emit("duplicate", order.value);
};
</script>
