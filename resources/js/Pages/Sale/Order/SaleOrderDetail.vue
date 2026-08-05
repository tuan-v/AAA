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
                        :class="statusBadgeClass(order?.effective_status || order?.status, order?.return_status)"
                        class="px-3 py-1 rounded-full text-sm font-medium"
                    >
                        {{ getStatusText(order?.effective_status || order?.status, order?.return_status) }}
                    </span>
                </p>
            </div>
            <div class="flex gap-3">
                <button
                    v-if="
                        props.context !== 'warehouse' &&
                        order?.status !== 'cancelled' &&
                        can('don_ban.them') &&
                        can('don_ban.tao_tu_lich_su')
                    "
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

        <div v-if="props.context !== 'warehouse'" class="mb-6 rounded-xl border bg-gray-50 p-4">
            <div class="flex items-center gap-2 overflow-x-auto">
                <template v-for="(step, index) in workflowSteps" :key="step.status">
                    <div class="flex min-w-28 flex-col items-center text-center">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold" :class="workflowStepClass(index, 'circle')">{{ index + 1 }}</span>
                        <span class="mt-1 text-xs font-medium" :class="workflowStepClass(index, 'label')">{{ step.label }}</span>
                    </div>
                    <div v-if="index < workflowSteps.length - 1" class="h-0.5 min-w-8 flex-1" :class="index < workflowIndex ? 'bg-blue-600' : 'bg-gray-200'"></div>
                </template>
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
                    <div v-if="Number(order?.shipping_fee || 0) > 0" class="flex justify-between">
                        <span class="text-gray-600">Phí giao hàng</span>
                        <span class="font-semibold text-orange-600">{{
                            formatMoney(order.shipping_fee, order?.currency)
                        }}</span>
                    </div>
                    <div v-if="Number(order?.discount_amount || 0) > 0" class="flex justify-between">
                        <span class="text-gray-600">Giảm giá</span>
                        <span class="font-semibold text-green-600">-{{
                            formatMoney(order.discount_amount, order?.currency)
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
                                <span v-if="unitLabel(item)" class="ml-1 font-medium text-gray-500">{{ unitLabel(item) }}</span>
                            </td>
                            <td class="border p-3 text-center text-green-600">
                                {{ formatQuantity(item.exported_quantity) }}
                                <span v-if="unitLabel(item)" class="ml-1">{{ unitLabel(item) }}</span>
                                /
                                {{ formatQuantity(item.quantity) }}
                                <span v-if="unitLabel(item)" class="ml-1">{{ unitLabel(item) }}</span>
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
import { usePermission } from "@/composables/usePermission";

const { can } = usePermission();

const unitLabel = (item) =>
    item?.unit_name || item?.product?.unit?.symbol || item?.product?.unit?.name || "";

const emit = defineEmits(["close", "duplicate"]);

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    context: {
        type: String,
        default: "business",
    },
});

// Alias để template dùng ngắn gọn
const order = computed(() => props.order);
const workflowSteps = computed(() => {
    const steps = [
        { status: 'pending', label: 'Chờ xác nhận' },
        { status: 'approved', label: 'Đã xác nhận' },
        { status: 'partial', label: 'Đang giao hàng' },
        { status: 'completed', label: 'Hoàn thành' },
    ];

    if (order.value?.return_status) {
        steps[3].label = ({
            pending_warehouse: 'Chờ kho nhận hàng hoàn',
            pending_accountant: 'Chờ kế toán duyệt hoàn',
            returned: 'Đã hoàn / Hủy giao',
        })[order.value.return_status] || 'Đang xử lý hoàn';
    } else if (order.value?.status === 'cancelled') {
        steps[cancelledAtIndex.value].label = 'Đã hủy';
    }

    return steps;
});
const cancelledAtIndex = computed(() => order.value?.submitted_at ? 1 : 0);
const workflowIndex = computed(() => {
    if (order.value?.return_status) return 3;
    if (order.value?.status === 'cancelled') return cancelledAtIndex.value;
    return workflowSteps.value.findIndex((step) => step.status === order.value?.status);
});
const workflowStepClass = (index, part) => {
    const isStopped = index === workflowIndex.value && (order.value?.return_status || order.value?.status === 'cancelled');
    if (isStopped) {
        const isCancelled = order.value?.status === 'cancelled' && !order.value?.return_status;
        if (isCancelled) return part === 'circle' ? 'bg-red-500 text-white' : 'text-red-700';
        return part === 'circle' ? 'bg-orange-500 text-white' : 'text-orange-700';
    }
    if (index <= workflowIndex.value) {
        return part === 'circle' ? 'bg-blue-600 text-white' : 'text-blue-700';
    }
    return part === 'circle' ? 'bg-gray-200 text-gray-500' : 'text-gray-400';
};

const getStatusText = (status, returnStatus = null) => {
    if (returnStatus === 'returned') return 'Đã hoàn / Hủy giao';
    if (returnStatus === 'pending_warehouse') return 'Chờ kho nhận hàng hoàn';
    if (returnStatus === 'pending_accountant') return 'Chờ kế toán duyệt hoàn';
    const businessMap = {
        pending: "Chờ xác nhận",
        approved: "Đã xác nhận",
        partial: "Đang giao hàng",
        completed: "Hoàn thành",
        cancelled: "Đã hủy",
    };

    const warehouseMap = {
        pending: "Chờ duyệt",
        approved: "Đang chờ xuất kho",
        partial: "Xuất một phần",
        completed: "Xuất đầy đủ",
        cancelled: "Đã hủy",
    };

    const map = props.context === "warehouse" ? warehouseMap : businessMap;
    return map[status] || status;
};

const statusBadgeClass = (status, returnStatus = null) => {
    if (returnStatus) return "bg-orange-100 text-orange-700";
    const businessClasses = {
        pending: "bg-yellow-100 text-yellow-700",
        approved: "bg-blue-100 text-blue-700",
        partial: "bg-purple-100 text-purple-700",
        completed: "bg-green-100 text-green-700",
        cancelled: "bg-red-100 text-red-700",
    };

    const warehouseClasses = {
        pending: "bg-yellow-100 text-yellow-700",
        approved: "bg-blue-100 text-blue-700",
        partial: "bg-yellow-100 text-yellow-700",
        completed: "bg-green-100 text-green-700",
        cancelled: "bg-red-100 text-red-700",
    };

    const classes = props.context === "warehouse"
        ? warehouseClasses
        : businessClasses;
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
