```vue
<template>
    <Head title="Sổ quỹ" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Sổ quỹ', link: null }]" />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Sổ quỹ / Sổ tài khoản</h2>
        </div>

        <!-- FILTER -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
        >
            <div class="mb-3">
                <p class="text-sm text-gray-500">Tìm kiếm và lọc sổ quỹ</p>
            </div>

            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="ledgers.data"
            :showIndex="true"
            :indexOffset="(ledgers.current_page - 1) * ledgers.per_page"
            emptyMessage="Không có dữ liệu sổ quỹ"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="ledgers.total"
            :itemsPerPage="ledgers.per_page"
            :currentPage="ledgers.current_page"
            :doingShow="ledgers.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, h, onMounted } from "vue";
import axios from "axios";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import SearchPage from "@/components/SearchPage.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";

/* ================= STATE ================= */
const perPage = ref(10);
const ledgers = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const accounts = ref([]);
const filterParams = ref({});

/* ================= FILTER ================= */

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Mã giao dịch",
    },
    {
        name: "account_id",
        type: "select",
        placeholder: "Tài khoản",
        options: [],
    },
];

/* ================= COLUMNS ================= */

const columns = [
    {
        label: "Ngày",
        align: "text-left",
        render: (row) =>
            h("span", {}, new Date(row.ledger_date).toLocaleString("vi-VN")),
    },

    {
        label: "Mã GD",
        align: "text-left",
        render: (row) =>
            h(
                "span",
                {
                    class: "font-medium text-blue-600",
                },
                row.transaction?.code ?? "-",
            ),
    },

    {
        label: "Tài khoản",
        align: "text-left",
        render: (row) =>
            h(
                "div",
                {
                    class: "flex flex-col",
                },
                [
                    h(
                        "span",
                        {
                            class: "font-medium",
                        },
                        row.account?.name ?? "-",
                    ),

                    h(
                        "span",
                        {
                            class: "text-xs text-gray-500",
                        },
                        row.account?.code ?? "",
                    ),
                ],
            ),
    },

    {
        label: "Thu",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                {
                    class: "font-semibold text-green-600",
                },
                Number(row.debit || 0).toLocaleString("vi-VN"),
            ),
    },

    {
        label: "Chi",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                {
                    class: "font-semibold text-red-600",
                },
                Number(row.credit || 0).toLocaleString("vi-VN"),
            ),
    },

    {
        label: "Số dư",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                {
                    class: "font-bold text-blue-600",
                },
                Number(row.balance_after || 0).toLocaleString("vi-VN"),
            ),
    },

    {
        label: "Mô tả",
        align: "text-left",
        render: (row) => h("span", {}, row.description ?? "-"),
    },
];

/* ================= METHODS ================= */

function handleFilter(params) {
    filterParams.value = params;
    getData(1);
}

function handlePageChange(page) {
    getData(page);
}

/* debounce */

function debounce(fn, delay = 300) {
    let timeout;

    return (...args) => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            fn(...args);
        }, delay);
    };
}

/* API */

const fetchData = async (page = 1) => {
    const res = await axios.get("/api/accountant/account-ledgers", {
        params: {
            page,
            per_page: perPage.value,
            ...filterParams.value,
        },
    });

    const data = res.data;

    ledgers.value = {
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

/* ================= INIT ================= */

onMounted(async () => {
    const accRes = await axios.get("/api/accountant/accounts/all");

    accounts.value = accRes.data;

    filters[1].options = accounts.value.map((item) => ({
        value: item.id,
        label: item.name,
    }));

    getData(1);
});
</script>
```
