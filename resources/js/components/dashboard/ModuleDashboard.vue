<template>
    <AdminLayout>
        <Head :title="config.title" />
        <PageBreadcrumb :title="config.title" :items="[{ text: config.title, link: null }]" />

        <div class="space-y-6">
            <section class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">{{ config.eyebrow }}</p>
                    <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ config.title }}</h1>
                    <p class="mt-2 text-sm text-gray-500">Dữ liệu được cập nhật trực tiếp từ chứng từ trong hệ thống.</p>
                </div>
                <Link :href="config.primaryLink" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ config.primaryAction }}
                </Link>
            </section>

            <div v-if="loading" class="rounded-xl border bg-white p-10 text-center text-gray-500">Đang tải số liệu...</div>
            <div v-else-if="error" class="rounded-xl border border-red-200 bg-red-50 p-5 text-red-700">{{ error }}</div>

            <template v-else>
                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article v-for="metric in data.metrics" :key="metric.label" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-sm text-gray-500">{{ metric.label }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ metric.type === 'money' ? formatMoney(metric.value) : formatNumber(metric.value) }}</p>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-3">
                    <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="font-semibold text-gray-900">{{ config.trendTitle }}</h2>
                            <div class="flex gap-4 text-xs text-gray-500">
                                <span><i class="mr-1 inline-block h-2.5 w-2.5 rounded-full bg-blue-600"></i>{{ config.primaryLabel }}</span>
                                <span v-if="config.secondaryLabel"><i class="mr-1 inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></i>{{ config.secondaryLabel }}</span>
                            </div>
                        </div>
                        <div class="flex h-64 items-end gap-3 border-b border-gray-200 pb-2">
                            <div v-for="row in data.trend" :key="row.label" class="flex min-w-0 flex-1 flex-col items-center justify-end gap-1">
                                <div class="flex h-52 w-full items-end justify-center gap-1">
                                    <div class="w-1/3 rounded-t bg-blue-600" :style="{ height: barHeight(row.primary) }" :title="formatNumber(row.primary)"></div>
                                    <div v-if="config.secondaryLabel" class="w-1/3 rounded-t bg-amber-500" :style="{ height: barHeight(row.secondary) }" :title="formatNumber(row.secondary)"></div>
                                </div>
                                <span class="truncate text-xs text-gray-500">{{ row.label }}</span>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 font-semibold text-gray-900">Thao tác nhanh</h2>
                        <div class="space-y-2">
                            <Link v-for="action in config.actions" :key="action.href" :href="action.href" class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                                {{ action.label }} <span>→</span>
                            </Link>
                        </div>
                    </article>
                </section>

                <section class="grid gap-6" :class="data.ranking?.length ? 'xl:grid-cols-2' : ''">
                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <h2 class="border-b px-5 py-4 font-semibold text-gray-900">{{ config.recentTitle }}</h2>
                        <div v-if="!data.recent?.length" class="p-8 text-center text-sm text-gray-500">Chưa có dữ liệu.</div>
                        <div v-else class="divide-y">
                            <div v-for="(row, index) in data.recent" :key="row.code || row.name || index" class="flex items-center justify-between gap-4 px-5 py-3 text-sm">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-800">{{ row.code || row.name }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ row.customer || row.supplier || row.warehouse || row.target || row.date || '' }}</p>
                                </div>
                                <span class="whitespace-nowrap font-semibold text-gray-700">{{ row.total != null ? formatMoney(row.total) : row.amount != null ? formatMoney(row.amount) : row.quantity != null ? formatNumber(row.quantity) : statusText(row.status) }}</span>
                            </div>
                        </div>
                    </article>

                    <article v-if="data.ranking?.length" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <h2 class="border-b px-5 py-4 font-semibold text-gray-900">{{ config.rankingTitle }}</h2>
                        <div class="divide-y">
                            <div v-for="(row, index) in data.ranking" :key="row.name" class="flex items-center gap-3 px-5 py-3 text-sm">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 font-semibold text-blue-700">{{ index + 1 }}</span>
                                <span class="min-w-0 flex-1 truncate text-gray-700">{{ row.name }}</span>
                                <span class="font-semibold">{{ formatMoney(row.value) }}</span>
                            </div>
                        </div>
                    </article>
                </section>
            </template>
        </div>
    </AdminLayout>
</template>

<script setup>
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import { formatMoney as money } from '@/config/helpers';

const props = defineProps({ module: { type: String, required: true } });
const loading = ref(true);
const error = ref('');
const data = ref({ metrics: [], trend: [], recent: [], ranking: [], currency: { code: 'VND', symbol: '₫' } });

const configs = {
    purchase: { title: 'Dashboard mua hàng', eyebrow: 'MUA HÀNG', primaryAction: 'Xem đơn mua', primaryLink: '/purchase/orders', trendTitle: 'Giá trị mua theo tháng', primaryLabel: 'Giá trị mua', secondaryLabel: '', recentTitle: 'Đơn mua gần đây', rankingTitle: 'Nhà cung cấp hàng đầu', actions: [{ label: 'Đơn mua hàng', href: '/purchase/orders' }, { label: 'Nhà cung cấp', href: '/purchase/suppliers' }, { label: 'Sản phẩm', href: '/purchase/products' }] },
    sale: { title: 'Dashboard bán hàng', eyebrow: 'BÁN HÀNG', primaryAction: 'Xem đơn bán', primaryLink: '/sale/orders', trendTitle: 'Doanh thu theo tháng', primaryLabel: 'Doanh thu', secondaryLabel: '', recentTitle: 'Đơn bán gần đây', rankingTitle: 'Khách hàng hàng đầu', actions: [{ label: 'Đơn bán hàng', href: '/sale/orders' }, { label: 'Khách hàng', href: '/sale/customers' }, { label: 'Công nợ khách hàng', href: '/accountant/customer-debts' }] },
    warehouse: { title: 'Dashboard kho hàng', eyebrow: 'KHO HÀNG', primaryAction: 'Xem phiếu kho', primaryLink: '/warehouse/slips', trendTitle: 'Nhập – xuất kho theo tháng', primaryLabel: 'Phiếu nhập', secondaryLabel: 'Phiếu xuất', recentTitle: 'Sản phẩm sắp hết', rankingTitle: '', actions: [{ label: 'Danh sách kho', href: '/warehouse/list' }, { label: 'Phiếu nhập/xuất', href: '/warehouse/slips' }, { label: 'Đơn cần xử lý', href: '/warehouse/orders' }] },
    accountant: { title: 'Dashboard kế toán', eyebrow: 'KẾ TOÁN', primaryAction: 'Xem giao dịch', primaryLink: '/accountant/transactions', trendTitle: 'Dòng tiền theo tháng', primaryLabel: 'Thu', secondaryLabel: 'Chi', recentTitle: 'Giao dịch gần đây', rankingTitle: '', actions: [{ label: 'Giao dịch', href: '/accountant/transactions' }, { label: 'Lịch sử giao dịch', href: '/accountant/account-ledgers' }, { label: 'Tài khoản và quỹ', href: '/accountant/accounts' }, { label: 'Công nợ phải thu', href: '/accountant/customer-debts' }, { label: 'Công nợ phải trả', href: '/accountant/supplier-debts' }] },
};
const config = computed(() => configs[props.module]);
const maxTrend = computed(() => Math.max(1, ...data.value.trend.flatMap((row) => [Number(row.primary || 0), Number(row.secondary || 0)])));
const barHeight = (value) => `${Math.max(Number(value) > 0 ? 4 : 0, (Number(value || 0) / maxTrend.value) * 100)}%`;
const formatMoney = (value) => money(value || 0, data.value.currency);
const formatNumber = (value) => new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 3 }).format(Number(value || 0));
const statusText = (status) => ({ pending: 'Chờ duyệt', approved: 'Đã duyệt', partial: 'Một phần', completed: 'Hoàn tất', cancelled: 'Đã hủy' }[status] || status || '');

onMounted(async () => {
    try {
        const response = await axios.get(`/api/dashboard/${props.module}`);
        data.value = response.data.data;
    } catch (e) {
        error.value = e.response?.data?.message || 'Không thể tải dữ liệu dashboard.';
    } finally {
        loading.value = false;
    }
});
</script>
