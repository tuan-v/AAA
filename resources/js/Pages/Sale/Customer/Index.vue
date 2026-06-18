<template>
    <Head title="Khách hàng" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[{ text: 'Khách hàng', link: null }]"
        />
        <!-- HEADER ACTION -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách khách hàng</h2>

            <button
                @click="openCreate"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
                + khách hàng
            </button>
        </div>

        <!-- SEARCH (giống Product style) -->
        <!-- FILTER -->

        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
        >
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-sm text-gray-500">
                        Tìm kiếm và lọc khách hàng
                    </p>
                </div>
            </div>
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="customers.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(customers.current_page - 1) * customers.per_page"
            emptyMessage="Không có khách hàng"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="customers.total"
            :itemsPerPage="customers.per_page"
            :currentPage="customers.current_page"
            :doingShow="customers.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <!-- MODAL -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <customerForm
                :customer="selectedCustomer"
                :currencies="currencies"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, h, watch } from "vue";
import axios from "axios";
import { formatMoney, removeMoneyFormat } from "@/config/helpers";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import SearchPage from "../../../components/SearchPage.vue";
import CustomerForm from "./CustomerForm.vue";

/* ================= STATE ================= */
const filterParams = ref({});

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Tên KH / Mã KH / SĐT",
    },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            {
                value: "active",
                label: "Đang hoạt động",
            },
            {
                value: "inactive",
                label: "Ngừng hoạt động",
            },
        ],
    },
];
function handleFilter(params) {
    console.log("FILTER:", params);
    filterParams.value = params;
    getData(1);
}
const customers = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const currencies = ref([]);

const showModal = ref(false);
const selectedCustomer = ref(null);

/* ================= COLUMNS ================= */
const columns = [
    {
        key: "code",
        label: "Mã KH",
        align: "text-start",
    },

    {
        key: "name",
        label: "Tên KH",
        align: "text-start",
    },

    {
        key: "phone",
        label: "SĐT",
        align: "text-right",
    },

    {
        key: "email",
        label: "Email",
        align: "text-start",
    },

    {
        label: "Tiền tệ",
        align: "text-start",
        render: (row) => h("span", {}, row.currency?.code ?? "-"),
    },

    {
        label: "Công nợ đầu kỳ",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                {},
                Number(row.opening_debt ?? 0).toLocaleString("vi-VN"),
            ),
    },

    {
        label: "Trạng thái",
        align: "text-start",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        row.status === "active"
                            ? "bg-green-100 text-green-700 px-2 py-1 rounded"
                            : "bg-red-100 text-red-700 px-2 py-1 rounded",
                },

                row.status === "active" ? "Đang hoạt động" : "Ngừng hoạt động",
            ),
    },
];

/* ================= ACTIONS (FIX GIỐNG PRODUCT STYLE) ================= */
const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        onClick: (item) => openEdit(item),
    },
    {
        icon: Unlock, // FIX: KHÔNG dùng function để tránh lỗi '0'
        type: "status",
        onClick: (item) => toggleStatus(item),
    },
];

/* ================= METHODS ================= */
function openCreate() {
    selectedCustomer.value = null;
    showModal.value = true;
}

function openEdit(item) {
    selectedCustomer.value = item;
    showModal.value = true;
}

function handlePageChange(page) {
    getData(page);
}

/* debounce giống product */
function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

/* API */
const fetchData = async (page = 1) => {
    const res = await axios.get("/api/sale/customers", {
        params: {
            page,
            ...filterParams.value,
        },
    });

    const data = res.data;

    customers.value = {
        data: data?.data ?? [],
        total: data?.total ?? 0,
        per_page: data?.per_page ?? 10,
        current_page: data?.current_page ?? 1,
        last_page: data?.last_page ?? 1,
    };
};

const getData = debounce((page = 1) => {
    fetchData(page);
}, 300);

/* toggle status */
async function toggleStatus(item) {
    await axios.patch(`/api/sale/customers/${item.id}/status`);
    getData(customers.value.current_page);
}

/* reload */
function reloadData() {
    showModal.value = false;
    getData(customers.value.current_page);
}

/* init */
onMounted(async () => {
    const res = await axios.get("/api/currencies");
    currencies.value = res.data;
});
</script>
