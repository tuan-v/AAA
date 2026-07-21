<template>
    <Head title="Sổ biến động tồn" />
    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Sổ biến động tồn', link: null }]" />
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Sổ biến động tồn</h2>
                <p class="mt-1 text-sm text-gray-500">Truy vết toàn bộ nhập, xuất và chuyển kho.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <select v-model="filters.warehouse_id" class="rounded-lg border px-3 py-2" @change="load(1)">
                    <option value="">Tất cả kho</option>
                    <option v-for="item in warehouses" :key="item.id" :value="item.id">{{ item.name }}</option>
                </select>
                <select v-model="filters.type" class="rounded-lg border px-3 py-2" @change="load(1)">
                    <option value="">Tất cả biến động</option>
                    <option value="import">Nhập kho</option>
                    <option value="export">Xuất kho</option>
                    <option value="transfer_out">Chuyển ra</option>
                    <option value="transfer_in">Chuyển vào</option>
                </select>
                <input v-model="filters.date_from" type="date" class="rounded-lg border px-3 py-2" @change="load(1)" />
                <input v-model="filters.date_to" type="date" class="rounded-lg border px-3 py-2" @change="load(1)" />
            </div>
        </div>

        <DataTable :columns="columns" :data="movements.data" :showIndex="true"
            :indexOffset="(movements.current_page - 1) * movements.per_page" emptyMessage="Chưa có biến động tồn" />
        <Pagination :totalItems="movements.total" :itemsPerPage="movements.per_page"
            :currentPage="movements.current_page" :doingShow="movements.data.length" @page-change="load" />
    </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { h, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import DataTable from '@/components/DataTable.vue';
import Pagination from '@/components/Pagination.vue';

const warehouses = ref([]);
const filters = reactive({ warehouse_id: '', type: '', date_from: '', date_to: '' });
const movements = ref({ data: [], total: 0, per_page: 20, current_page: 1 });
const labels = { import: 'Nhập kho', export: 'Xuất kho', transfer_out: 'Chuyển ra', transfer_in: 'Chuyển vào' };
const number = (value, digits = 2) => Number(value || 0).toLocaleString('vi-VN', { maximumFractionDigits: digits });
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
    const { data } = await axios.get('/api/warehouse/inventory-movements', { params: { ...filters, page } });
    movements.value = data;
}
onMounted(async () => {
    const { data } = await axios.get('/api/warehouses/all');
    warehouses.value = data.data || data;
    await load();
});
</script>
