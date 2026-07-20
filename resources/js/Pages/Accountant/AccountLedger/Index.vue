<template>
    <Head title="Lịch sử giao dịch" />
    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Kế toán', link: '/accountant' }, { text: 'Lịch sử giao dịch', link: null }]" />
        <div class="mb-5">
            <h2 class="text-2xl font-bold text-gray-900">Lịch sử giao dịch</h2>
            <p class="mt-1 text-sm text-gray-500">Theo dõi toàn bộ biến động thu, chi và số dư theo từng tài khoản.</p>
        </div>
        <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>
        <DataTable :columns="columns" :data="ledgers.data" :showIndex="true"
            :indexOffset="(ledgers.current_page - 1) * ledgers.per_page" emptyMessage="Chưa có lịch sử giao dịch" />
        <Pagination :totalItems="ledgers.total" :itemsPerPage="ledgers.per_page"
            :currentPage="ledgers.current_page" :doingShow="ledgers.data.length"
            @page-change="handlePageChange" @items-per-page-change="handlePerPageChange" />
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { h, onMounted, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SearchPage from '@/components/SearchPage.vue';
import DataTable from '@/components/DataTable.vue';
import Pagination from '@/components/Pagination.vue';
import { formatMoney, formatDateTime } from '@/config/helpers';

const perPage = ref(10);
const ledgers = ref({ data: [], total: 0, per_page: 10, current_page: 1, last_page: 1 });
const filterParams = ref({});
const filters = ref([
    { name: 'search', type: 'text', placeholder: 'Tìm theo mã giao dịch' },
    { name: 'account_id', type: 'select', placeholder: 'Tất cả tài khoản', options: [] },
]);
const columns = [
    { label: 'Ngày giao dịch', align: 'text-left', render: row => h('span', {}, formatDateTime(row.ledger_date)) },
    { label: 'Mã giao dịch', align: 'text-left', render: row => h('span', { class: 'font-medium text-blue-600' }, row.transaction?.code ?? '-') },
    { label: 'Tài khoản', align: 'text-left', render: row => h('div', { class: 'flex flex-col' }, [h('span', { class: 'font-medium' }, row.account?.name ?? '-'), h('span', { class: 'text-xs text-gray-500' }, row.account?.code ?? '')]) },
    { label: 'Thu', align: 'text-right', render: row => h('span', { class: 'font-semibold text-green-600' }, formatMoney(row.debit || 0, row.currency)) },
    { label: 'Chi', align: 'text-right', render: row => h('span', { class: 'font-semibold text-red-600' }, formatMoney(row.credit || 0, row.currency)) },
    { label: 'Số dư sau giao dịch', align: 'text-right', render: row => h('span', { class: 'font-bold text-blue-600' }, formatMoney(row.balance_after || 0, row.currency)) },
    { label: 'Mô tả', align: 'text-left', render: row => h('span', {}, row.description ?? '-') },
];
function debounce(fn, delay = 300) { let timeout; return (...args) => { clearTimeout(timeout); timeout = setTimeout(() => fn(...args), delay); }; }
async function fetchData(page = 1) {
    const { data } = await axios.get('/api/accountant/account-ledgers', { params: { page, per_page: perPage.value, ...filterParams.value } });
    ledgers.value = { data: data.data ?? [], total: data.total ?? 0, per_page: data.per_page ?? perPage.value, current_page: data.current_page ?? 1, last_page: data.last_page ?? 1 };
}
const getData = debounce(fetchData);
const handleFilter = params => { filterParams.value = params; getData(1); };
const handlePageChange = page => getData(page);
const handlePerPageChange = value => { perPage.value = value; getData(1); };
onMounted(async () => {
    const { data: accounts } = await axios.get('/api/accountant/accounts/all');
    filters.value[1].options = accounts.map(account => ({ value: account.id, label: `${account.code} - ${account.name}` }));
    await fetchData(1);
});
</script>
