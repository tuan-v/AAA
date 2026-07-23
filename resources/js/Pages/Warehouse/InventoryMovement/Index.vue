<template>
    <Head title="Sổ biến động tồn" />
    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Sổ biến động tồn', link: null }]" />
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Sổ biến động tồn</h2>
                <p class="mt-1 text-sm text-gray-500">Truy vết toàn bộ nhập, xuất và chuyển kho.</p>
            </div>
        </div>

        <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <SearchPage :filters="filterDefinitions" @filter="handleFilter" />
        </div>

        <DataTable :columns="columns" :data="movements.data" :showIndex="true"
            :indexOffset="(movements.current_page - 1) * movements.per_page" emptyMessage="Chưa có biến động tồn" />
        <Pagination :totalItems="movements.total" :itemsPerPage="movements.per_page"
            :currentPage="movements.current_page" :doingShow="movements.data.length" @page-change="load" />
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { h, onMounted, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import DataTable from '@/components/DataTable.vue';
import Pagination from '@/components/Pagination.vue';
import SearchPage from '@/components/SearchPage.vue';
import { useRealtimeRefresh } from '@/composables/useRealtimeRefresh';
import { toast } from 'vue3-toastify';

const warehouses = ref([]);
const filterNames = ['warehouse_id', 'type', 'date_from', 'date_to'];
const urlParams = new URLSearchParams(window.location.search);
const currentFilters = ref(Object.fromEntries(
    filterNames
        .filter((name) => urlParams.has(name))
        .map((name) => [name, urlParams.get(name)]),
));
const filterDefinitions = ref([
    { name: 'warehouse_id', type: 'select', placeholder: 'Tất cả kho', options: [] },
    {
        name: 'type', type: 'select', placeholder: 'Tất cả biến động', options: [
            { value: 'import', label: 'Nhập kho' },
            { value: 'export', label: 'Xuất kho' },
            { value: 'transfer_out', label: 'Chuyển ra' },
            { value: 'transfer_in', label: 'Chuyển vào' },
        ],
    },
    { name: 'date_from', type: 'date', placeholder: 'Từ ngày' },
    {
        name: 'date_to',
        type: 'date',
        placeholder: 'Đến ngày',
        config: { maxDate: 'today' },
    },
]);
filterDefinitions.value.find((filter) => filter.name === 'date_from').config = { maxDate: 'today' };
const movements = ref({ data: [], total: 0, per_page: 20, current_page: 1 });
const labels = { import: 'Nhập kho', export: 'Xuất kho', transfer_out: 'Chuyển ra', transfer_in: 'Chuyển vào' };
const number = (value, digits = 2) => Number(value || 0).toLocaleString('vi-VN', { maximumFractionDigits: digits });
const todayDate = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};
const columns = [
    { label: 'Thời gian', render: row => h('span', new Date(row.created_at).toLocaleString('vi-VN')) },
    { label: 'Kho', render: row => h('span', row.warehouse?.name || '-') },
    { label: 'Sản phẩm', render: row => h('span', `${row.product?.sku || ''} - ${row.product?.name || '-'}`) },
    { label: 'Loại', render: row => h('span', labels[row.type] || row.type) },
    { label: 'Số lượng', render: row => h('span', number(row.quantity, 3)) },
    { label: 'Đơn giá vốn', render: row => h('span', number(row.unit_cost)) },
    { label: 'Giá trị', render: row => h('span', number(row.total_value)) },
    { label: 'Tồn trước → sau', render: row => h('span', `${number(row.quantity_before, 3)} → ${number(row.quantity_after, 3)}`) },
    { label: 'Giá trị trước → sau', render: row => h('span', `${number(row.value_before)} → ${number(row.value_after)}`) },
];
async function load(page = 1) {
    const { data } = await axios.get('/api/warehouse/inventory-movements', {
        params: { ...currentFilters.value, page },
    });
    movements.value = data;
}
function handleFilter(params) {
    if (params.date_from && params.date_from > todayDate()) {
        toast.warning('Từ ngày không được lớn hơn ngày hôm nay.');
        return;
    }
    if (params.date_from && params.date_to && params.date_to < params.date_from) {
        toast.warning('Đến ngày phải lớn hơn hoặc bằng Từ ngày.');
        return;
    }
    currentFilters.value = params;
    load(1);
}
useRealtimeRefresh(() => load(movements.value.current_page || 1));

onMounted(async () => {
    const { data } = await axios.get('/api/warehouses/all');
    warehouses.value = data.data || data;
    filterDefinitions.value[0].options = warehouses.value.map((warehouse) => ({
        value: warehouse.id,
        label: warehouse.name,
    }));
    await load();
});
</script>
