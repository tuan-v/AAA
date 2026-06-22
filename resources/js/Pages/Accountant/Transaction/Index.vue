<template>
    <Head title="Giao dịch" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Giao dịch', link: null }]" />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách giao dịch</h2>

            <button
                @click="openCreate"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
                + Giao dịch
            </button>
        </div>

        <!-- FILTER -->
        <div class="bg-white rounded-xl border shadow-sm p-4 mb-5">
            <p class="text-sm text-gray-500 mb-3">Tìm kiếm và lọc giao dịch</p>

            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="transactions.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="
                (transactions.current_page - 1) * transactions.per_page
            "
            emptyMessage="Không có giao dịch"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="transactions.total"
            :itemsPerPage="transactions.per_page"
            :currentPage="transactions.current_page"
            :doingShow="transactions.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <!-- MODAL CREATE -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <TransactionForm
                :accounts="accounts"
                :categories="categories"
                :currencies="currencies"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>

    <!-- DETAIL -->
    <Modal v-if="showDetail" @close="showDetail = false" size="large">
        <template #body>
            <TransactionDetail
                :transaction-id="selectedId"
                @close="showDetail = false"
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

import TransactionForm from "./TransactionForm.vue";
// import TransactionDetail from "./TransactionDetail.vue";

/* ================= STATE ================= */

const transactions = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const accounts = ref([]);
const categories = ref([]);
const currencies = ref([]);

const showModal = ref(false);
const showDetail = ref(false);
const selectedId = ref(null);

const filterParams = ref({});

/* ================= FILTER ================= */

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Mô tả / mã giao dịch",
    },
    {
        name: "type",
        type: "select",
        placeholder: "Loại giao dịch",
        options: [
            { value: "receipt", label: "Thu tiền" },
            { value: "payment", label: "Chi tiền" },
            { value: "transfer", label: "Chuyển khoản" },
        ],
    },
    {
        name: "category_id",
        type: "select",
        placeholder: "Loại thanh toán",
        options: [],
    },
];

/* ================= TABLE COLUMNS ================= */

const columns = [
    {
        key: "code",
        label: "Mã GD",
        align: "text-start",
    },
    {
        label: "Loại",
        render: (row) => {
            const map = {
                receipt: "Thu",
                payment: "Chi",
                transfer: "Chuyển",
            };

            return h(
                "span",
                {
                    class:
                        row.type === "receipt"
                            ? "text-green-600"
                            : row.type === "payment"
                              ? "text-red-600"
                              : "text-blue-600",
                },
                map[row.type],
            );
        },
    },
    {
        label: "Số tiền",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                { class: "font-semibold" },
                Number(row.amount_base ?? 0).toLocaleString("vi-VN"),
            ),
    },
    {
        label: "Tiền tệ",
        render: (row) => row.currency?.code ?? "-",
    },
    {
        label: "Tài khoản",
        render: (row) => row.fromAccount?.name || row.toAccount?.name || "-",
    },
    {
        label: "Ngày",
        render: (row) => new Date(row.transaction_date).toLocaleString(),
    },
];

/* ================= ACTIONS ================= */

const actions = [
    {
        type: "view",
        onClick: (item) => openDetail(item),
    },
];

/* ================= METHODS ================= */

function openCreate() {
    showModal.value = true;
}

function openDetail(item) {
    selectedId.value = item.id;
    showDetail.value = true;
}

function handleFilter(params) {
    filterParams.value = params;
    getData(1);
}

function handlePageChange(page) {
    getData(page);
}

/* debounce */
function debounce(fn, delay = 300) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
}

/* API */
const fetchData = async (page = 1) => {
    const res = await axios.get("/api/accountant/transactions", {
        params: {
            page,
            ...filterParams.value,
        },
    });

    const data = res.data;

    transactions.value = {
        data: data.data ?? [],
        total: data.total ?? 0,
        per_page: data.per_page ?? 10,
        current_page: data.current_page ?? 1,
        last_page: data.last_page ?? 1,
    };
};

const getData = debounce(fetchData, 300);

/* reload */
function reloadData() {
    showModal.value = false;
    getData(transactions.value.current_page);
}

/* INIT */
onMounted(async () => {
    const [accRes, catRes, curRes] = await Promise.all([
        axios.get("/api/accountant/accounts/all"),
        axios.get("/api/accountant/transaction-categories"),
        axios.get("/api/accountant/currencies"),
    ]);

    accounts.value = accRes.data;
    categories.value = catRes.data.data || [];

    // inject category options into filter
    filters[2].options = (catRes.data.data || []).map((c) => ({
        value: c.id,
        label: c.name,
    }));

    currencies.value = curRes.data;

    getData(1);
});
</script>
