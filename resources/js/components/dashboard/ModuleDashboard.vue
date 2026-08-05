<template>
    <AdminLayout>
        <Head :title="config.title" />
        <PageBreadcrumb :items="[{ text: config.title, link: null }]" />

        <div class="space-y-6">
            <section
                class="flex flex-col gap-5 overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-800 p-6 text-white shadow-xl shadow-indigo-950/10 md:flex-row md:items-center md:justify-between md:p-8"
            >
                <div>
                    <p
                        class="text-xs font-bold tracking-[0.2em] text-indigo-200"
                    >
                        {{ config.eyebrow }}
                    </p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight">
                        {{ config.title }}
                    </h1>
                    <p class="mt-2 text-sm text-indigo-100/75">
                        Dữ liệu được cập nhật trực tiếp từ chứng từ trong hệ
                        thống.
                    </p>
                </div>
                <Link
                    :href="config.primaryLink"
                    class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-indigo-50"
                >
                    {{ config.primaryAction }}
                </Link>
            </section>

            <DashboardDateFilter
                v-model:date-from="dateFrom"
                v-model:date-to="dateTo"
                :loading="loading"
                @apply="loadDashboard"
            />

            <div
                v-if="loading"
                class="rounded-xl border bg-white p-10 text-center text-gray-500"
            >
                Đang tải số liệu...
            </div>
            <div
                v-else-if="error"
                class="rounded-xl border border-red-200 bg-red-50 p-5 text-red-700"
            >
                {{ error }}
            </div>

            <template v-else>
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="metric in data.metrics"
                        :key="metric.label"
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <p class="text-sm text-gray-500">{{ metric.label }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            {{ metric.type === "money" ? formatMoney(metric.value) : formatNumber(metric.value) }}
                        </p>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-3">
                    <article
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm xl:col-span-2"
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="font-semibold text-gray-900">
                                {{ config.trendTitle }}
                            </h2>
                            <div class="flex gap-4 text-xs text-gray-500">
                                <span
                                    ><i
                                        class="mr-1 inline-block h-2.5 w-2.5 rounded-full bg-blue-600"
                                    ></i
                                    >{{ config.primaryLabel }}</span
                                >
                                <span v-if="config.secondaryLabel"
                                    ><i
                                        class="mr-1 inline-block h-2.5 w-2.5 rounded-full bg-amber-500"
                                    ></i
                                    >{{ config.secondaryLabel }}</span
                                >
                            </div>
                        </div>
                        <div class="relative h-72 overflow-hidden rounded-xl bg-slate-50/70 px-4 pb-3 pt-5">
                            <div class="pointer-events-none absolute inset-x-4 bottom-9 top-5 flex flex-col justify-between">
                                <span v-for="line in 5" :key="line" class="block border-t border-dashed border-slate-200/90"></span>
                            </div>
                            <div class="relative flex h-full items-end gap-3">
                            <div
                                v-for="row in data.trend"
                                :key="row.label"
                                class="group flex min-w-0 flex-1 flex-col items-center justify-end"
                            >
                                <div
                                    class="flex h-[218px] w-full items-end justify-center gap-2"
                                >
                                    <div
                                        class="relative w-full max-w-[42px] rounded-t-lg bg-gradient-to-t from-blue-600 to-blue-400 shadow-[0_5px_16px_rgba(37,99,235,0.16)] transition duration-300 group-hover:brightness-105"
                                        :style="{
                                            height: barHeight(row.primary),
                                        }"
                                        :title="formatNumber(row.primary)"
                                    >
                                        <span v-if="Number(row.primary) > 0" class="absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] font-semibold text-blue-700">
                                            {{ formatCompact(row.primary) }}
                                        </span>
                                    </div>
                                    <div
                                        v-if="config.secondaryLabel"
                                        class="relative w-full max-w-[42px] rounded-t-lg bg-gradient-to-t from-amber-500 to-orange-300 shadow-[0_5px_16px_rgba(245,158,11,0.16)] transition duration-300 group-hover:brightness-105"
                                        :style="{
                                            height: barHeight(row.secondary),
                                        }"
                                        :title="formatNumber(row.secondary)"
                                    >
                                        <span v-if="Number(row.secondary) > 0" class="absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] font-semibold text-amber-700">
                                            {{ formatCompact(row.secondary) }}
                                        </span>
                                    </div>
                                </div>
                                <span class="mt-2 max-w-full truncate text-xs font-medium text-slate-500">{{
                                    row.label
                                }}</span>
                            </div>
                            </div>
                        </div>
                    </article>

                    <article
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <h2 class="mb-4 font-semibold text-gray-900">
                            Thao tác nhanh
                        </h2>
                        <div class="space-y-2">
                            <Link
                                v-for="action in config.actions"
                                :key="action.href"
                                :href="action.href"
                                class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                            >
                                {{ action.label }} <span>→</span>
                            </Link>
                        </div>
                    </article>
                </section>

                <section
                    class="grid gap-6"
                    :class="data.ranking?.length ? 'xl:grid-cols-2' : ''"
                >
                    <article
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div class="flex items-center justify-between border-b px-5 py-4">
                            <h2 class="font-semibold text-gray-900">
                                {{ config.recentTitle }}
                            </h2>
                            <Link
                                :href="config.recentLink"
                                class="text-sm font-medium text-indigo-600 hover:underline"
                            >
                                Xem tất cả
                            </Link>
                        </div>
                        <div
                            v-if="!data.recent?.length"
                            class="p-8 text-center text-sm text-gray-500"
                        >
                            Chưa có dữ liệu.
                        </div>
                        <div v-else class="divide-y">
                            <div
                                v-for="(row, index) in data.recent"
                                :key="row.code || row.name || index"
                                class="flex items-center justify-between gap-4 px-5 py-3 text-sm"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate font-medium text-gray-800"
                                    >
                                        {{ row.code || row.name }}
                                    </p>
                                    <p class="truncate text-xs text-gray-500">
                                        {{
                                            row.customer ||
                                            row.supplier ||
                                            row.warehouse ||
                                            row.target ||
                                            row.date ||
                                            ""
                                        }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1.5">
                                    <span
                                        v-if="row.total != null || row.amount != null || row.quantity != null"
                                        class="whitespace-nowrap font-semibold text-gray-700"
                                    >{{
                                        row.total != null
                                            ? formatMoney(row.total)
                                            : row.amount != null
                                              ? formatMoney(row.amount)
                                              : formatNumber(row.quantity)
                                    }}</span>
                                    <span
                                        v-if="row.status"
                                        class="inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="statusMeta(row.effective_status || row.status).class"
                                    >
                                        {{ statusMeta(row.effective_status || row.status).label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article
                        v-if="data.ranking?.length"
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <h2
                            class="border-b px-5 py-4 font-semibold text-gray-900"
                        >
                            {{ config.rankingTitle }}
                        </h2>
                        <div class="divide-y">
                            <div
                                v-for="(row, index) in data.ranking"
                                :key="row.name"
                                class="flex items-center gap-3 px-5 py-3 text-sm"
                            >
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 font-semibold text-blue-700"
                                    >{{ index + 1 }}</span
                                >
                                <span
                                    class="min-w-0 flex-1 truncate text-gray-700"
                                    >{{ row.name }}</span
                                >
                                <span class="font-semibold">{{
                                    formatMoney(row.value)
                                }}</span>
                            </div>
                        </div>
                    </article>
                </section>
            </template>
        </div>
    </AdminLayout>
</template>

<script setup>
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DashboardDateFilter from "@/components/dashboard/DashboardDateFilter.vue";
import { formatMoney as money } from "@/config/helpers";
import { getOrderStatusMeta } from "@/config/status";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

const props = defineProps({ module: { type: String, required: true } });
const loading = ref(true);
const error = ref("");
const today = new Date();
const toDateInput = (date) => {
    const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
};
const currentMonthStart = new Date(today.getFullYear(), today.getMonth(), 1);
const dateFrom = ref(toDateInput(currentMonthStart));
const dateTo = ref(toDateInput(today));
const data = ref({
    metrics: [],
    trend: [],
    recent: [],
    ranking: [],
    currency: { code: "VND", symbol: "₫" },
});

const configs = {
    purchase: {
        title: "Tổng quan mua hàng",
        eyebrow: "MUA HÀNG",
        primaryAction: "Xem đơn mua",
        primaryLink: "/purchase/orders",
        trendTitle: "Giá trị mua theo tháng",
        primaryLabel: "Giá trị mua",
        secondaryLabel: "",
        recentTitle: "Đơn mua gần đây",
        recentLink: "/purchase/orders",
        rankingTitle: "Top NCC theo giá trị PO đã duyệt",
        actions: [
            { label: "Đơn mua hàng", href: "/purchase/orders" },
            { label: "Nhà cung cấp", href: "/purchase/suppliers" },
            { label: "Sản phẩm", href: "/purchase/products" },
        ],
    },
    sale: {
        title: "Tổng quan bán hàng",
        eyebrow: "BÁN HÀNG",
        primaryAction: "Xem đơn bán",
        primaryLink: "/sale/orders",
        trendTitle: "Doanh thu theo tháng",
        primaryLabel: "Doanh thu",
        secondaryLabel: "",
        recentTitle: "Đơn bán gần đây",
        recentLink: "/sale/orders",
        rankingTitle: "Top khách hàng theo giá trị SO đã duyệt",
        actions: [
            { label: "Đơn bán hàng", href: "/sale/orders" },
            { label: "Khách hàng", href: "/sale/customers" },
            { label: "Công nợ khách hàng", href: "/accountant/customer-debts" },
        ],
    },
    warehouse: {
        title: "Tổng quan kho hàng",
        eyebrow: "KHO HÀNG",
        primaryAction: "Xem phiếu kho",
        primaryLink: "/warehouse/slips",
        trendTitle: "Nhập – xuất kho theo tháng",
        primaryLabel: "Phiếu nhập",
        secondaryLabel: "Phiếu xuất",
        recentTitle: "Sản phẩm sắp hết",
        recentLink: "/warehouse/list",
        rankingTitle: "",
        actions: [
            { label: "Danh sách kho", href: "/warehouse/list" },
            { label: "Phiếu nhập/xuất", href: "/warehouse/slips" },
            { label: "Đơn cần xử lý", href: "/warehouse/orders" },
        ],
    },
    accountant: {
        title: "Tổng quan kế toán",
        eyebrow: "KẾ TOÁN",
        primaryAction: "Xem giao dịch",
        primaryLink: "/accountant/transactions",
        trendTitle: "Dòng tiền theo tháng",
        primaryLabel: "Thu",
        secondaryLabel: "Chi",
        recentTitle: "Giao dịch gần đây",
        recentLink: "/accountant/transactions",
        rankingTitle: "",
        actions: [
            { label: "Giao dịch", href: "/accountant/transactions" },
            { label: "Lịch sử giao dịch", href: "/accountant/account-ledgers" },
            { label: "Tài khoản và quỹ", href: "/accountant/accounts" },
            { label: "Công nợ phải thu", href: "/accountant/customer-debts" },
            { label: "Công nợ phải trả", href: "/accountant/supplier-debts" },
        ],
    },
};
const config = computed(() => configs[props.module]);
const maxTrend = computed(() =>
    Math.max(
        1,
        ...data.value.trend.flatMap((row) => [
            Number(row.primary || 0),
            Number(row.secondary || 0),
        ]),
    ),
);
const barHeight = (value) =>
    `${Math.max(Number(value) > 0 ? 4 : 0, (Number(value || 0) / maxTrend.value) * 100)}%`;
const formatMoney = (value) => money(value || 0, data.value.currency);
const formatNumber = (value) =>
    new Intl.NumberFormat("vi-VN", { maximumFractionDigits: 3 }).format(
        Number(value || 0),
    );
const formatCompact = (value) => {
    const number = Number(value || 0);
    if (number >= 1_000_000_000) return `${formatNumber(number / 1_000_000_000)} tỷ`;
    if (number >= 1_000_000) return `${formatNumber(number / 1_000_000)} tr`;
    if (number >= 1_000) return `${formatNumber(number / 1_000)} nghìn`;
    return formatNumber(number);
};
const statusMeta = (status) => getOrderStatusMeta(status);

const loadDashboard = async () => {
    loading.value = true;
    error.value = "";
    try {
        const response = await axios.get(`/api/dashboard/${props.module}`, {
            params: { date_from: dateFrom.value, date_to: dateTo.value },
        });
        data.value = response.data.data;
    } catch (e) {
        error.value =
            e.response?.data?.message || "Không thể tải dữ liệu tổng quan.";
    } finally {
        loading.value = false;
    }
};

useRealtimeRefresh(loadDashboard);
onMounted(loadDashboard);
</script>
