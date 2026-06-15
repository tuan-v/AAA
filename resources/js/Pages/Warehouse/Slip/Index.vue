<template>
    <Head title="Phiếu" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Phiếu', link: null }]" />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách phiếu</h2>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <!-- TYPE FILTER -->
            <select
                v-model="warehouseFilter"
                @change="getData(1)"
                class="border px-3 py-2 rounded-lg bg-white shadow-sm"
            >
                <option value="all">Tất cả kho</option>

                <option v-for="w in warehouses" :key="w.id" :value="w.id">
                    {{ w.name }}
                </option>
            </select>

            <!-- SEARCH -->
            <input
                v-model="search"
                type="text"
                placeholder="Tìm mã phiếu..."
                class="border px-3 py-2 rounded-lg w-80 shadow-sm"
            />
        </div>
        <div class="flex items-center border-b mb-5 gap-2">
            <!-- IMPORT TAB -->
            <button
                @click="activeTab = 'import'"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    activeTab === 'import'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Phiếu nhập
            </button>

            <!-- EXPORT TAB -->
            <button
                @click="activeTab = 'export'"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    activeTab === 'export'
                        ? 'border-red-500 text-red-600'
                        : 'border-transparent text-gray-500 hover:text-red-500'
                "
            >
                Phiếu xuất
            </button>
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="slips.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(slips.current_page - 1) * slips.per_page"
            emptyMessage="Không có phiếu nhập"
        >
        </DataTable>

        <!-- PAGINATION -->
        <Pagination
            :totalItems="slips.total"
            :itemsPerPage="slips.per_page"
            :currentPage="slips.current_page"
            :doingShow="slips.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <!-- MODAL -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <WarehouseImportForm
                :order="selectedSlip"
                :warehouses="warehouses"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>
<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { ref, onMounted, h, watch } from "vue";
import axios from "axios";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";

const warehouseFilter = ref("all");
const search = ref("");
const activeTab = ref("import");
const can = (permission) => permissions.includes(permission);

const showModal = ref(false);
const selectedSlip = ref(null);
const products = ref([]);
const warehouses = ref([]);
const slips = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const columns = [
    {
        label: "Mã phiếu",
        key: "code",
        align: "text-start",
    },
    {
        label: "Mã đơn",
        key: "purchase_order_code",
        align: "text-start",
    },
    {
        label: "Người tạo",
        render: (row) => h("span", {}, row.created_by?.name ?? "-"),
    },
    {
        label: "Người duyệt",
        render: (row) => h("span", {}, row.approved_by?.name ?? "-"),
    },
    {
        label: "Kho nhập/xuất",
        align: "text-start",
        render: (row) => h("span", {}, row.warehouse?.name ?? "-"),
    },
    {
        label: "Số mặt hàng",
        key: "total_items",
        align: "text-right",
    },
    {
        label: "Ghi chú",
        key: "note",
        align: "text-start",
    },
    {
        label: "Ngày tạo",
        key: "created_at",
        align: "text-right",
    },
    {
        label: "Ngày duyệt",
        align: "text-right",
        render: (row) => h("span", {}, row.approved_at ?? "-"),
    },
];
const actions = [
    {
        type: "edit",
        icon: EditButtonIcon,
        onClick: (item) => openEdit(item),
    },
];

function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

const fetchData = async (page = 1) => {
    const url =
        activeTab.value === "import"
            ? "/api/warehouse/slips?type=import"
            : "/api/warehouse/slips?type=export";

    const res = await axios.get(url, {
        params: {
            page,
            search: search.value,
            warehouse_id: warehouseFilter.value,
        },
    });

    slips.value = res.data;
};
watch(activeTab, () => {
    getData(1);
});
watch(search, () => getData(1));
watch(warehouseFilter, () => getData(1));
function goToExport() {
    window.location.href = "/warehouse/export";
}
const fetchWarehouses = async () => {
    try {
        const res = await axios.get("/api/warehouses");

        warehouses.value = res.data.data ?? res.data;
    } catch (err) {
        console.error(err);
    }
};
const getData = debounce((page = 1) => fetchData(page), 300);

watch(search, () => getData(1));

function openCreate() {
    selectedSlip.value = null;
    showModal.value = true;
}

function openEdit(order) {
    selectedSlip.value = order;
    showModal.value = true;
}

function handlePageChange(page) {
    getData(page);
}

function reloadData() {
    getData(slips.value.current_page);
    showModal.value = false;
}

onMounted(() => {
    getData();
    fetchWarehouses();
});
</script>
