<template>
    <Head title="Đơn hàng" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Đơn hàng', link: null }]" />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách đơn hàng</h2>

            <button
                @click="openCreate"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
            >
                +
            </button>
        </div>

        <!-- FILTER -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <!-- TYPE FILTER -->
            <select
                v-model="typeFilter"
                @change="getData(1)"
                class="border px-3 py-2 rounded-lg bg-white shadow-sm"
            >
                <option value="all">Tất cả</option>
                <option value="purchase">Đơn mua</option>
                <option value="sale">Đơn bán</option>
            </select>
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
                placeholder="Tìm mã đơn..."
                class="border px-3 py-2 rounded-lg w-80 shadow-sm"
            />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="orders.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(orders.current_page - 1) * orders.per_page"
            emptyMessage="Không có đơn hàng"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="orders.total"
            :itemsPerPage="orders.per_page"
            :currentPage="orders.current_page"
            :doingShow="orders.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <!-- MODAL -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <OrderForm
                :order="selectedOrder"
                :products="products"
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
import OrderForm from "./OrderForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";

const warehouseFilter = ref("all");
const search = ref("");
const typeFilter = ref("all");

const can = (permission) => permissions.includes(permission);

const showModal = ref(false);
const selectedOrder = ref(null);
const products = ref([]);
const warehouses = ref([]);
const orders = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const columns = [
    {
        label: "Mã đơn",
        key: "code",
        align: "text-center",
    },
    {
        label: "Loại",
        key: "type",
        align: "text-center",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        row.type === "purchase"
                            ? "text-green-600 font-medium"
                            : "text-blue-600 font-medium",
                },
                row.type === "purchase" ? "Mua" : "Bán",
            ),
    },
    {
        label: "Kho",
        key: "warehouse_name",
        align: "text-center",
    },
    {
        label: "Trạng thái",
        key: "status",
        align: "text-center",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        row.status === "completed"
                            ? "bg-green-100 text-green-700 px-2 py-1 rounded"
                            : row.status === "partial"
                              ? "bg-yellow-100 text-yellow-700 px-2 py-1 rounded"
                              : "bg-gray-100 text-gray-700 px-2 py-1 rounded",
                },
                row.status === "completed"
                    ? "Đầy đủ"
                    : row.status === "partial"
                      ? "Một phần"
                      : "Nháp",
            ),
    },
    {
        label: "Ngày tạo",
        key: "created_at",
        align: "text-center",
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
    try {
        const res = await axios.get("/api/warehouse/orders", {
            params: {
                page,
                search: search.value,
                type: typeFilter.value,
                warehouse_id: warehouseFilter.value,
            },
        });

        orders.value = res.data;
    } catch (err) {
        console.error(err);
    }
};
const fetchProducts = async () => {
    try {
        const res = await axios.get("/api/warehouse/products", {
            params: { page: 1, search: "" },
        });

        products.value = res.data.data; // quan trọng
    } catch (err) {
        console.error(err);
    }
};

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
watch(typeFilter, () => getData(1));

function openCreate() {
    selectedOrder.value = null;
    showModal.value = true;
}

function openEdit(order) {
    selectedOrder.value = order;
    showModal.value = true;
}

function handlePageChange(page) {
    getData(page);
}

function reloadData() {
    getData(orders.value.current_page);
    showModal.value = false;
}

onMounted(() => {
    getData();
    fetchWarehouses();
    fetchProducts();
});
</script>
