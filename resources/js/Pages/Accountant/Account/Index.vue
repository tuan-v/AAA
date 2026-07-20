<template>
    <Head title="Tài khoản" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[
                {
                    text: 'Tài khoản',
                    link: null,
                },
            ]"
        />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách tài khoản</h2>

            <button
                v-if="can('tai_khoan.them')"
                @click="openCreate"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
                + Tài khoản
            </button>
        </div>

        <!-- FILTER -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
        >
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="accounts.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(accounts.current_page - 1) * accounts.per_page"
            emptyMessage="Không có tài khoản"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="accounts.total"
            :itemsPerPage="accounts.per_page"
            :currentPage="accounts.current_page"
            :doingShow="accounts.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <!-- FORM -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <AccountForm
                :account="selectedAccount"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, h, onMounted } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import SearchPage from "@/components/SearchPage.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import AccountForm from "./AccountForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import { usePermission } from "@/composables/usePermission";
import { formatMoney } from "@/config/helpers";

const { can } = usePermission();
const perPage = ref(10);
const accounts = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
});

const showModal = ref(false);

const selectedAccount = ref(null);

const filterParams = ref({});

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Mã TK / Tên TK",
    },

    {
        name: "type",
        type: "select",
        placeholder: "Loại tài khoản",

        options: [
            {
                value: "cash",
                label: "Tiền mặt",
            },

            {
                value: "bank",
                label: "Ngân hàng",
            },

            {
                value: "ewallet",
                label: "Ví điện tử",
            },

            {
                value: "other",
                label: "Khác",
            },
        ],
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
                label: "Khóa",
            },
        ],
    },
];

function handleFilter(params) {
    filterParams.value = params;

    getData(1);
}

const columns = [
    {
        key: "code",
        label: "Mã TK",
    },

    {
        key: "name",
        label: "Tên tài khoản",
    },

    {
        label: "Loại",

        render: (row) =>
            h(
                "span",
                {},
                {
                    cash: "Tiền mặt",
                    bank: "Ngân hàng",
                    ewallet: "Ví điện tử",
                    other: "Khác",
                }[row.type],
            ),
    },
    {
        label: "Số dư đầu kỳ",

        align: "text-right",

        render: (row) =>
            h(
                "span",
                {},
                formatMoney(row.opening_balance ?? 0, row.currency),
            ),
    },

    {
        label: "Số dư hiện tại",

        align: "text-right",

        render: (row) =>
            h(
                "span",
                {
                    class: "font-semibold text-blue-600",
                },

                formatMoney(row.current_balance ?? 0, row.currency),
            ),
    },
    {
        label: "Tiền tệ",

        render: (row) => h("span", {}, row.currency?.code ?? "-"),
    },

    {
        label: "Ngân hàng",

        render: (row) => h("span", {}, row.bank?.name ?? "-"),
    },

    {
        label: "Số tài khoản",

        render: (row) => h("span", {}, row.bank_account_no ?? "-"),
    },

    {
        label: "Trạng thái",

        render: (row) =>
            h(
                "span",
                {
                    class: row.is_active
                        ? "bg-green-100 text-green-700 px-2 py-1 rounded"
                        : "bg-red-100 text-red-700 px-2 py-1 rounded",
                },

                row.is_active ? "Hoạt động" : "Khóa",
            ),
    },
];

const actions = [
    {
        icon: EditButtonIcon,

        hidden: (row) => row.is_used,

        onClick: (item) => openEdit(item),
    },

    {
        icon: Lock,

        onClick: (item) => toggleStatus(item),
    },
];

function openCreate() {
    selectedAccount.value = null;

    showModal.value = true;
}

function openEdit(item) {
    selectedAccount.value = item;

    showModal.value = true;
}

async function toggleStatus(item) {
    try {
        await axios.patch(`/api/accountant/accounts/${item.id}/toggle-status`);

        toast.success("Cập nhật trạng thái thành công");

        getData(accounts.value.current_page);
    } catch {
        toast.error("Có lỗi xảy ra");
    }
}

async function fetchData(page = 1) {
    const res = await axios.get("/api/accountant/accounts", {
        params: {
            page,
            per_page: perPage.value,
            ...filterParams.value,
        },
    });

    accounts.value = res.data;
}
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};
function getData(page = 1) {
    fetchData(page);
}

function handlePageChange(page) {
    getData(page);
}

function reloadData() {
    showModal.value = false;

    getData(accounts.value.current_page);
}

onMounted(() => {
    getData();
});
</script>
