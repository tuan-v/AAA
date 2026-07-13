```vue
<template>
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-6xl p-6 relative z-50"
    >
        <!-- HEADER -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">
                    Đơn mua hàng #{{ order?.code }}
                </h2>

                <p class="text-sm text-gray-500">
                    Ngày tạo:
                    {{ formatDate(order?.created_at) }}

                    • Trạng thái:

                    <span
                        :class="statusBadgeClass(order?.status)"
                        class="px-3 py-1 rounded-full text-sm font-medium"
                    >
                        {{ getStatusText(order?.status) }}
                    </span>
                </p>
            </div>

            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-red-500 text-3xl leading-none"
            >
                ✕
            </button>
        </div>

        <!-- THÔNG TIN -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- LEFT -->
            <div class="lg:col-span-7 bg-gray-50 rounded-xl p-6">
                <h3 class="font-semibold text-lg mb-4">
                    Thông tin đơn mua hàng
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                        >
                            Nhà cung cấp
                        </label>

                        <p class="font-medium">
                            {{ order?.supplier?.name }}
                            ({{ order?.supplier?.code }})
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                        >
                            Tiền tệ
                        </label>

                        <p class="font-medium">
                            {{ order?.currency?.code }}
                            -
                            {{ order?.currency?.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                        >
                            Ngày nhận dự kiến
                        </label>

                        <p class="font-medium">
                            {{
                                order?.expected_received_date
                                    ? formatDate(order.expected_received_date)
                                    : "Chưa có"
                            }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                        >
                            Người tạo
                        </label>

                        <p class="font-medium">
                            {{ order?.created_by?.name || "-" }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                        >
                            Người duyệt
                        </label>

                        <p class="font-medium">
                            {{ order?.approved_by?.name || "-" }}
                        </p>
                    </div>

                    <!-- <div>
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                        >
                            Tỷ giá
                        </label>

                        <p class="font-medium">
                            {{ order?.exchange_rate || 1 }}
                        </p>
                    </div> -->

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                        >
                            Ghi chú
                        </label>

                        <p class="text-gray-700 whitespace-pre-line">
                            {{ order?.note || "Không có ghi chú" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="lg:col-span-5 bg-white border rounded-xl p-6">
                <h3 class="font-semibold text-lg mb-4">
                    Tổng giá trị đơn hàng
                </h3>

                <div
                    class="flex justify-between border-t pt-4 text-2xl font-bold text-blue-700"
                >
                    <span>Tổng tiền</span>

                    <span>
                        {{
                            formatMoney(
                                order?.total_amount || 0,
                                order?.currency,
                            )
                        }}
                    </span>
                </div>

                <div class="mt-6">
                    <label class="block text-sm text-gray-500 mb-2">
                        Tiến độ nhập kho
                    </label>

                    <div class="bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div
                            class="bg-green-500 h-3"
                            :style="{
                                width: progressPercent + '%',
                            }"
                        />
                    </div>

                    <p class="text-sm text-gray-500 mt-2">
                        {{ progressPercent }}% hoàn thành
                    </p>
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

                            <th class="border p-3 text-center">Đã nhập</th>

                            <th class="border p-3 text-right">Đơn giá</th>

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
                                {{ item.quantity }}
                            </td>

                            <td
                                class="border p-3 text-center text-green-600 font-semibold"
                            >
                                {{ item.received_quantity || 0 }}
                                /
                                {{ item.quantity }}
                            </td>

                            <td class="border p-3 text-right">
                                {{ formatMoney(item.price, order?.currency) }}
                            </td>

                            <td class="border p-3 text-right font-semibold">
                                {{ formatMoney(item.amount, order?.currency) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { formatMoney } from "@/config/helpers";

defineEmits(["close"]);

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const getStatusText = (status) => {
    const map = {
        pending: "Chờ xử lý",
        approved: "Đã duyệt",
        partial: "Đã duyệt",
        completed: "Đã duyệt",
        cancelled: "Đã hủy",
    };

    return map[status] || status;
};

const statusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-700",
        approved: "bg-blue-100 text-blue-700",
        partial: "bg-blue-100 text-blue-700",
        completed: "bg-blue-100 text-blue-700",
        cancelled: "bg-red-100 text-red-700",
    };

    return classes[status] || "bg-gray-100 text-gray-700";
};

const formatDate = (date) => {
    if (!date) return "-";

    return new Intl.DateTimeFormat("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }).format(new Date(date));
};

const progressPercent = computed(() => {
    if (!props.order?.items?.length) return 0;

    const totalQty = props.order.items.reduce(
        (sum, item) => sum + Number(item.quantity || 0),
        0,
    );

    const receivedQty = props.order.items.reduce(
        (sum, item) => sum + Number(item.received_quantity || 0),
        0,
    );

    if (!totalQty) return 0;

    return Math.round((receivedQty / totalQty) * 100);
});
</script>
```
