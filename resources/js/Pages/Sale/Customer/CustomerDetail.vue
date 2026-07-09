<template>
    <div
        class="fixed inset-0 bg-slate-100 overflow-y-auto z-999"
        v-if="!loading"
    >
        <div class="max-w-6xl mx-auto px-4 py-6 relative">
            <!-- Close -->
            <button
                @click="$emit('close')"
                class="absolute right-6 top-6 z-50 w-9 h-9 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 flex items-center justify-center transition"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

            <!-- Header -->
            <div
                class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 flex items-center justify-between gap-4 mb-4"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-white/20 text-white flex items-center justify-center text-2xl font-semibold flex-shrink-0 ring-2 ring-white/30"
                    >
                        {{ customer.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">
                            {{ customer.name }}
                        </h1>
                        <p class="text-blue-200 text-sm mt-0.5">
                            {{ customer.code }}
                        </p>
                        <div class="flex gap-5 mt-2 flex-wrap">
                            <span
                                class="flex items-center gap-1.5 text-sm text-white/80"
                            >
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"
                                    />
                                </svg>
                                {{ customer.phone || "Chưa có SĐT" }}
                            </span>
                            <span
                                class="flex items-center gap-1.5 text-sm text-white/80"
                            >
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                                    />
                                </svg>
                                {{ customer.email || "Chưa có Email" }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 items-center flex-shrink-0">
                    <button
                        @click="createQuickOrder"
                        class="flex items-center gap-2 bg-white text-blue-700 font-semibold rounded-xl px-5 py-2.5 text-sm hover:bg-blue-50 transition shadow"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4.5v15m7.5-7.5h-15"
                            />
                        </svg>
                        Tạo đơn hàng
                    </button>
                    <button
                        @click="editCustomer"
                        class="flex items-center gap-2 border border-white/30 text-white rounded-xl px-5 py-2.5 text-sm hover:bg-white/10 transition"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"
                            />
                        </svg>
                        Chỉnh sửa
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
                <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                    <p
                        class="text-xs text-red-400 mb-1 font-medium uppercase tracking-wide"
                    >
                        Công nợ hiện tại
                    </p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ formatCurrency(debtSummary.remaining_debt) }}
                    </p>
                </div>
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <p
                        class="text-xs text-blue-400 mb-1 font-medium uppercase tracking-wide"
                    >
                        Tổng phát sinh
                    </p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ formatCurrency(debtSummary.total_receivable) }}
                    </p>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                    <p
                        class="text-xs text-green-500 mb-1 font-medium uppercase tracking-wide"
                    >
                        Đã thanh toán
                    </p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ formatCurrency(debtSummary.total_paid) }}
                    </p>
                </div>
                <div
                    class="bg-indigo-50 border border-indigo-100 rounded-xl p-4"
                >
                    <p
                        class="text-xs text-indigo-400 mb-1 font-medium uppercase tracking-wide"
                    >
                        Số đơn hàng
                    </p>
                    <p class="text-2xl font-bold text-indigo-600">
                        {{ recentOrders.length }}
                    </p>
                </div>
            </div>

            <!-- Body -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Left -->
                <div class="col-span-12 xl:col-span-8 space-y-4">
                    <!-- Thông tin khách hàng -->
                    <div
                        class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6"
                    >
                        <h2
                            class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2"
                        >
                            <span
                                class="w-1 h-5 bg-blue-500 rounded-full inline-block"
                            ></span>
                            Thông tin khách hàng
                        </h2>
                        <div class="grid md:grid-cols-2 gap-3 mb-3">
                            <div
                                class="bg-gray-50 rounded-xl p-4 border border-gray-100"
                            >
                                <p
                                    class="text-[11px] uppercase tracking-wide text-gray-400 mb-1.5"
                                >
                                    Số điện thoại
                                </p>
                                <p class="text-base font-medium text-gray-800">
                                    {{ customer.phone || "Chưa cập nhật" }}
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 rounded-xl p-4 border border-gray-100"
                            >
                                <p
                                    class="text-[11px] uppercase tracking-wide text-gray-400 mb-1.5"
                                >
                                    Email
                                </p>
                                <p class="text-base font-medium text-gray-800">
                                    {{ customer.email || "Chưa cập nhật" }}
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 rounded-xl p-4 border border-gray-100"
                            >
                                <p
                                    class="text-[11px] uppercase tracking-wide text-gray-400 mb-1.5"
                                >
                                    Tiền tệ
                                </p>
                                <p class="text-base font-medium text-gray-800">
                                    {{ customer.currency?.name || "VND" }}
                                </p>
                            </div>
                            <div
                                class="bg-orange-50 rounded-xl p-4 border border-orange-100"
                            >
                                <p
                                    class="text-[11px] uppercase tracking-wide text-orange-400 mb-1.5"
                                >
                                    Công nợ đầu kỳ
                                </p>
                                <p
                                    class="text-base font-semibold text-orange-600"
                                >
                                    {{ formatCurrency(customer.opening_debt) }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 rounded-xl p-4 border border-gray-100"
                        >
                            <p
                                class="text-[11px] uppercase tracking-wide text-gray-400 mb-1.5"
                            >
                                Địa chỉ
                            </p>
                            <p class="text-base text-gray-800 leading-relaxed">
                                {{ fullAddress }}
                            </p>
                        </div>
                    </div>

                    <!-- Đơn hàng gần đây -->
                    <div
                        class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6"
                    >
                        <h2
                            class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2"
                        >
                            <span
                                class="w-1 h-5 bg-indigo-500 rounded-full inline-block"
                            ></span>
                            Đơn hàng gần đây
                        </h2>
                        <div
                            v-if="recentOrders.length"
                            class="overflow-hidden rounded-xl border border-gray-100"
                        >
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="text-left p-2">Mã đơn</th>
                                        <th class="text-left p-2">Ngày</th>
                                        <th class="text-left p-2">Tổng tiền</th>
                                        <th class="text-left p-2">
                                            Trạng thái
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="order in recentOrders"
                                        :key="order.id"
                                        class="border-t"
                                    >
                                        <td
                                            class="p-2 font-medium text-gray-700"
                                        >
                                            {{ order.code || "-" }}
                                        </td>
                                        <td class="p-2">
                                            {{
                                                order.order_date
                                                    ? new Date(
                                                          order.order_date,
                                                      ).toLocaleDateString(
                                                          "vi-VN",
                                                      )
                                                    : "-"
                                            }}
                                        </td>
                                        <td class="p-2">
                                            {{
                                                formatCurrency(
                                                    order.total_amount || 0,
                                                )
                                            }}
                                        </td>
                                        <td class="p-2">
                                            {{ order.status || "-" }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            v-else
                            class="text-gray-400 text-center py-6 text-sm"
                        >
                            Chưa có đơn hàng nào
                        </div>
                    </div>
                </div>

                <!-- Right -->
                <div class="col-span-12 xl:col-span-4 space-y-4">
                    <!-- Tổng quan công nợ -->
                    <div
                        class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6"
                    >
                        <h2
                            class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2"
                        >
                            <span
                                class="w-1 h-5 bg-red-400 rounded-full inline-block"
                            ></span>
                            Tổng quan công nợ
                        </h2>

                        <div
                            class="mb-5"
                            v-if="(debtSummary.total_receivable || 0) > 0"
                        >
                            <div class="flex justify-between text-xs mb-2">
                                <span class="text-green-500 font-medium"
                                    >Đã thanh toán {{ paidPercent }}%</span
                                >
                                <span class="text-red-400 font-medium"
                                    >Còn nợ {{ 100 - paidPercent }}%</span
                                >
                            </div>
                            <div
                                class="flex h-2.5 rounded-full overflow-hidden gap-0.5"
                            >
                                <div
                                    class="bg-green-400 rounded-l-full"
                                    :style="{ flex: paidPercent }"
                                ></div>
                                <div
                                    class="bg-red-400 rounded-r-full"
                                    :style="{ flex: 100 - paidPercent }"
                                ></div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500"
                                    >Công nợ đầu kỳ</span
                                >
                                <span class="font-medium text-gray-700">{{
                                    formatCurrency(customer.opening_debt || 0)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500"
                                    >Tổng phát sinh</span
                                >
                                <span class="font-medium text-blue-600">{{
                                    formatCurrency(
                                        debtSummary.total_receivable || 0,
                                    )
                                }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Đã thanh toán</span>
                                <span class="font-medium text-green-600">{{
                                    formatCurrency(debtSummary.total_paid || 0)
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between text-sm border-t pt-2 mt-2"
                            >
                                <span class="text-gray-700 font-semibold"
                                    >Còn lại</span
                                >
                                <span class="font-semibold text-red-600">{{
                                    formatCurrency(
                                        debtSummary.remaining_debt || 0,
                                    )
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lịch sử thanh toán -->
                    <div
                        class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6"
                    >
                        <h2
                            class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2"
                        >
                            <span
                                class="w-1 h-5 bg-green-400 rounded-full inline-block"
                            ></span>
                            Lịch sử thanh toán
                        </h2>
                        <div v-if="debtHistory.length" class="space-y-2">
                            <div
                                v-for="item in debtHistory.slice(0, 8)"
                                :key="item.id"
                                class="rounded-xl border border-gray-100 p-3 bg-gray-50"
                            >
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >{{ item.note || item.type }}</span
                                    >
                                    <span
                                        class="text-sm font-semibold"
                                        :class="
                                            item.amount >= 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{ formatCurrency(item.amount) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{
                                        item.created_at
                                            ? new Date(
                                                  item.created_at,
                                              ).toLocaleString()
                                            : "-"
                                    }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-else
                            class="text-gray-400 text-center py-10 text-sm"
                        >
                            Chưa có dữ liệu
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";

const customer = ref({});
const debtSummary = ref({});
const recentOrders = ref([]);
const debtHistory = ref([]);

const props = defineProps({
    customerId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(["saved", "close", "create-order"]);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await axios.get(
            `/api/sale/customers/${props.customerId}/detail`,
        );

        customer.value = res.data.customer;
        debtSummary.value = res.data.debt_summary;
        recentOrders.value = res.data.recent_orders;
        debtHistory.value = res.data.debt_history || [];
    } finally {
        loading.value = false;
    }
});

const fullAddress = computed(() => {
    const parts = [
        customer.value.address_detail,
        customer.value.ward?.name,
        customer.value.province?.name,
    ].filter(Boolean);
    return parts.join(", ") || "Chưa có địa chỉ";
});

const paidPercent = computed(() => {
    const total = debtSummary.value.total_receivable || 0;
    const paid = debtSummary.value.total_paid || 0;
    if (total === 0) return 0;
    return Math.round((paid / total) * 100);
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount || 0);
};

const createQuickOrder = () => {
    emit("create-order", customer.value.id);
};

const editCustomer = () => {
    // Điều hướng đến trang edit
};
</script>
