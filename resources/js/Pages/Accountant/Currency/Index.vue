<template>
    <Head title="Tiền tệ" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[
                {
                    text: 'Tiền tệ',
                    link: null,
                },
            ]"
        />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách tiền tệ</h2>

            <button
                @click="openCreate"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
                + Tiền tệ
            </button>
        </div>

        <!-- FILTER -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
        >
            <div class="mb-3">
                <p class="text-sm text-gray-500">Tìm kiếm và lọc tiền tệ</p>
            </div>

            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="currencies.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(currencies.current_page - 1) * currencies.per_page"
            emptyMessage="Không có dữ liệu tiền tệ"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="currencies.total"
            :itemsPerPage="currencies.per_page"
            :currentPage="currencies.current_page"
            :doingShow="currencies.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <!-- FORM -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <CurrencyForm
                :currency="selectedCurrency"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, h } from "vue";
import axios from "axios";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import SearchPage from "@/components/SearchPage.vue";

import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import CurrencyForm from "./CurrencyForm.vue";

/* ================= STATE ================= */
const perPage = ref(10);
const filterParams = ref({});

const currencies = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const showModal = ref(false);

const selectedCurrency = ref(null);

/* ================= FILTER ================= */

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Mã hoặc tên tiền tệ",
    },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            {
                value: 1,
                label: "Hoạt động",
            },
            {
                value: 0,
                label: "Đã khóa",
            },
        ],
    },
];

function handleFilter(params) {
    filterParams.value = params;
    getData(1);
}

/* ================= TABLE ================= */

const columns = [
    {
        key: "code",
        label: "Mã",
        align: "text-start",
    },

    {
        key: "name",
        label: "Tên tiền tệ",
        align: "text-start",
    },

    {
        key: "symbol",
        label: "Ký hiệu",
        align: "text-right",
    },

    {
        label: "Tỷ giá",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                {
                    class: "font-semibold",
                },
                Number(row.exchange_rate || 0).toLocaleString("vi-VN"),
            ),
    },
    {
        label: "Sử dụng",
        render: (row) =>
            h(
                "span",
                {
                    class: row.is_used
                        ? "bg-red-100 text-red-700 px-2 py-1 rounded"
                        : "bg-green-100 text-green-700 px-2 py-1 rounded",
                },
                row.is_used ? "Đã sử dụng" : "Chưa sử dụng",
            ),
    },

    {
        label: "Trạng thái",
        align: "text-start",
        render: (row) =>
            h(
                "span",
                {
                    class: row.is_active
                        ? "bg-green-100 text-green-700 px-2 py-1 rounded text-xs"
                        : "bg-red-100 text-red-700 px-2 py-1 rounded text-xs",
                },
                row.is_active ? "Hoạt động" : "Đã khóa",
            ),
    },
];

/* ================= ACTION ================= */

const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        hidden: (row) => row.is_used,
        onClick: (item) => openEdit(item),
    },

    {
        icon: Unlock,
        type: "status",
        hidden: (row) => row.is_used,
        onClick: (item) => toggleStatus(item),
    },
];

/* ================= METHODS ================= */

function openCreate() {
    selectedCurrency.value = null;
    showModal.value = true;
}

function openEdit(item) {
    if (item.is_used) {
        toast.error("Đơn vị tiền tệ đã được sử dụng, không thể chỉnh sửa.");

        return;
    }
    selectedCurrency.value = item;
    showModal.value = true;
}

function handlePageChange(page) {
    getData(page);
}

function debounce(fn, delay = 300) {
    let timeout;

    return (...args) => {
        clearTimeout(timeout);

        timeout = setTimeout(() => fn(...args), delay);
    };
}

/* ================= API ================= */

const fetchData = async (page = 1) => {
    const res = await axios.get("/api/accountant/currencies", {
        params: {
            page,
            per_page: perPage.value,
            ...filterParams.value,
        },
    });

    const data = res.data;

    currencies.value = {
        data: data.data ?? [],
        total: data.total ?? 0,
        per_page: data.per_page ?? 10,
        current_page: data.current_page ?? 1,
        last_page: data.last_page ?? 1,
    };
};
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};
const getData = debounce((page = 1) => {
    fetchData(page);
}, 300);

async function toggleStatus(item) {
    await axios.patch(`/api/accountant/currencies/${item.id}/toggle-status`);

    getData(currencies.value.current_page);
}

function reloadData() {
    showModal.value = false;

    getData(currencies.value.current_page);
}

/* ================= INIT ================= */

onMounted(() => {
    getData(1);
});
</script>
