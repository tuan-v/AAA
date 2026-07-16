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
                :customers="customers"
                :suppliers="suppliers"
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
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import TransactionForm from "./TransactionForm.vue";
import TransactionDetail from "./TransactionDetail.vue";
import CheckIcon from "@/icons/CheckIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import { usePermission } from "@/composables/usePermission";

const { can } = usePermission();
const showConfirm = ref(false);
const pendingItem = ref(null);

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
const customers = ref([]);
const suppliers = ref([]);

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
        label: "Đơn hàng",
        render: (row) =>
            h("span", row.sales_order?.code || row.purchase_order?.code || "-"),
    },
    {
        label: "Số tiền",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                { class: "font-semibold" },
                ` ${Number(row.amount ?? 0).toLocaleString("vi-VN")} ${row.currency?.symbol ?? ""}`.trim(),
            ),
    },
    // {
    //     label: "Tiền tệ",
    //     render: (row) => h("span", row.currency?.code ?? "-"),
    // },
    {
        label: "Tài khoản",
        render: (row) => {
            let accountName = "-";

            if (row.type === "receipt") {
                accountName = row.to_account?.name ?? "-";
            }

            if (row.type === "payment") {
                accountName = row.from_account?.name ?? "-";
            }

            if (row.type === "transfer") {
                accountName = `${row.from_account?.name ?? "-"} → ${row.to_account?.name ?? "-"}`;
            }

            return h("span", {}, accountName);
        },
    },
    {
        label: "Ngày",
        render: (row) =>
            h("span", new Date(row.transaction_date).toLocaleString()),
    },
    {
        label: "Trạng thái",
        render: (row) => {
            const config = {
                pending: {
                    text: "Chờ duyệt",
                    class: "bg-yellow-100 text-yellow-700",
                },
                approve: {
                    text: "Đã duyệt",
                    class: "bg-green-100 text-green-700",
                },
                reject: { text: "Từ chối", class: "bg-red-100 text-red-700" },
            };
            const status = config[row.status] ?? config.pending;
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

/* ================= ACTIONS ================= */

const actions = [
    {
        icon: CheckIcon,
        title: "Duyệt",
        hidden: (row) =>
            !can("transaction.approve") || row.status !== "pending",
        onClick: (item) => {
            pendingItem.value = item;
            showConfirm.value = true;
        },
    },
    {
        icon: DeleteIcon,
        title: "Từ chối",
        hidden: (row) => !can("transaction.reject") || row.status !== "pending",
        onClick: async (item) => {
            try {
                await axios.post(
                    `/api/accountant/transactions/${item.id}/reject`,
                );
                toast.success("Từ chối giao dịch thành công");
                getData(transactions.value.current_page);
            } catch (err) {
                toast.error(err.response?.data?.message ?? "Từ chối thất bại");
            }
        },
    },
    {
        icon: DetailButtonIcon,
        type: "view",
        onClick: (item) => openDetail(item),
    },
];

async function confirmApprove() {
    if (!pendingItem.value) return;
    try {
        await axios.post(
            `/api/accountant/transactions/${pendingItem.value.id}/approve`,
        );
        toast.success("Duyệt giao dịch thành công");
        showConfirm.value = false;
        getData(transactions.value.current_page);
    } catch (err) {
        toast.error(err.response?.data?.message ?? "Duyệt thất bại");
    }
}

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
    const [accRes, catRes, curRes, customerRes, supplierRes] =
        await Promise.all([
            axios.get("/api/accountant/accounts/all"),
            axios.get("/api/accountant/transaction-categories"),
            axios.get("/api/accountant/currencies"),
            axios.get("/api/sale/customers/all"),
            axios.get("/api/purchase/suppliers/all"),
        ]);

    accounts.value = accRes.data;
    categories.value = catRes.data.data || [];
    customers.value = customerRes.data || [];
    suppliers.value = supplierRes.data || [];

    // inject category options into filter
    filters[2].options = (catRes.data.data || []).map((c) => ({
        value: c.id,
        label: c.name,
    }));

    currencies.value = curRes.data;

    getData(1);
});
</script>
