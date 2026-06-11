<template>
    <Head title="Đơn hàng" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Đơn hàng', link: null }]" />

        <!-- HEADER -->

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Quản lý đơn hàng</h2>
        </div>
        <div
            class="flex flex-wrap items-center justify-between gap-3 mb-5 bg-white p-3 rounded-xl shadow-sm"
        >
            <!-- SEARCH -->
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
            >
                <SearchPage :filters="filters" @filter="handleFilter" />
            </div>
        </div>
        <!-- TABS -->
        <div class="flex border-b mb-4">
            <button
                @click="changeTab('purchase')"
                class="px-5 py-3 font-medium border-b-2 transition"
                :class="
                    activeTab === 'purchase'
                        ? 'border-green-600 text-green-600'
                        : 'border-transparent text-gray-500'
                "
            >
                Đơn mua
            </button>

            <button
                @click="changeTab('sale')"
                class="px-5 py-3 font-medium border-b-2 transition"
                :class="
                    activeTab === 'sale'
                        ? 'border-blue-600 text-blue-600'
                        : 'border-transparent text-gray-500'
                "
            >
                Đơn bán
            </button>
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow-sm">
            <DataTable
                :columns="columns"
                :data="orders.data"
                :showIndex="true"
                :actions="actions"
                :indexOffset="(orders.current_page - 1) * orders.per_page"
                emptyMessage="Không có đơn hàng"
            />
        </div>

        <!-- PAGINATION -->
        <Pagination
            :totalItems="orders.total"
            :itemsPerPage="orders.per_page"
            :currentPage="orders.current_page"
            :doingShow="orders.data.length"
            @page-change="handlePageChange"
            class="mt-4"
        />
    </AdminLayout>
</template>

<script setup>
import { h, ref, onMounted, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import { formatMoney, removeMoneyFormat } from "@/config/helpers";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import WarehouseIcon from "../../../icons/WarehouseIcon.vue";
import SearchPage from "@/components/SearchPage.vue";
const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Tìm mã đơn hoặc NCC...",
    },

    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            {
                value: "approved",
                label: "Đang chờ nhập kho",
            },
            {
                value: "partial",
                label: "Nhập một phần",
            },
            {
                value: "completed",
                label: "Hoàn thành",
            },
            {
                value: "cancelled",
                label: "Đã hủy",
            },
        ],
    },
];
const statusConfig = {
    approved: {
        text: "Đang chờ nhập kho",
        class: "bg-blue-100 text-blue-700",
    },

    partial: {
        text: "Nhập một phần",
        class: "bg-orange-100 text-orange-700",
    },

    completed: {
        text: "Nhập đầy đủ",
        class: "bg-green-100 text-green-700",
    },

    cancelled: {
        text: "Đã hủy",
        class: "bg-red-100 text-red-700",
    },
};
function handleFilter(params) {
    search.value = params.search || "";
    statusFilter.value = params.status || "";

    getData();
}
const activeTab = ref("purchase");

const orders = ref({
    data: [],
});

const suppliers = ref([]);
const products = ref([]);
const currencies = ref([]);

const search = ref("");
const statusFilter = ref("");

const showModal = ref(false);

const selectedOrder = ref(null);

const columns = [
    {
        key: "code",
        label: "Mã đơn",
    },

    {
        label: "Nhà cung cấp",
        render: (row) => {
            return h(
                "span",
                { class: "text-gray-700" },
                row.supplier?.name ?? "—",
            );
        },
    },

    {
        label: "Tiền tệ",
        render: (row) => {
            return h(
                "span",
                { class: "text-gray-700" },
                row.currency?.code ?? "—",
            );
        },
    },

    {
        key: "expected_received_date",
        label: "Dự kiến nhận",
        render: (row) => {
            return h(
                "span",
                { class: "text-gray-700" },
                row.expected_received_date ?? "—",
            );
        },
    },

    {
        label: "Tổng tiền",
        render: (row) => {
            const value = new Intl.NumberFormat("vi-VN").format(
                row.total_amount ?? 0,
            );

            const symbol = row.currency?.symbol ?? "";

            return h(
                "span",
                { class: "font-medium text-gray-900" },
                `${value} ${symbol}`,
            );
        },
    },

    {
        label: "Trạng thái",

        render: (row) => {
            const status = statusConfig[row.status] ?? statusConfig.approved;

            return h(
                "span",
                {
                    class: `${status.class} px-3 py-1 rounded-full text-xs font-medium`,
                },
                status.text,
            );
        },
    },
];

const actions = [
    {
        icon: WarehouseIcon, // icon nhập kho (bạn có thể thay)
        visible: (row) => row.status === "approved",
        onClick: (item) => openStockIn(item),
        title: "Nhập kho",
    },
];
function openStockIn(item) {
    window.location.href = `/warehouse/slips/create?order_id=${item.purchase_order_id}`;
}
async function getData(page = 1) {
    const res = await axios.get("/api/purchase/orders", {
        params: {
            page,
            search: search.value,
            type: activeTab.value,
            // status: statusFilter.value,
            status: "approved",
        },
    });

    orders.value = res.data;
}

async function fetchSuppliers() {
    const res = await axios.get("/api/purchase/suppliers");

    suppliers.value = res.data.data ?? res.data;
}

async function fetchProducts() {
    const res = await axios.get("/api/warehouse/products");

    products.value = res.data.data;
}

async function fetchCurrencies() {
    const res = await axios.get("/api/currencies");

    currencies.value = res.data.data ?? res.data;
}

function reloadData() {
    showModal.value = false;
    getData();
}

function handlePageChange(page) {
    getData(page);
}
function changeTab(tab) {
    activeTab.value = tab;
    getData(1);
}

/* ================= WATCH ================= */

onMounted(() => {
    getData();
    fetchSuppliers();
    fetchProducts();
    fetchCurrencies();
});
</script>
