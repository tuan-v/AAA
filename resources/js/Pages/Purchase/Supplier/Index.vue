<template>
    <Head title="Nhà cung cấp" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[{ text: 'Nhà cung cấp', link: null }]"
        />

        <!-- HEADER TABS (giống Product) -->
        <div class="flex items-center border-b mb-5 gap-2">
            <span
                class="px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600"
            >
                Nhà cung cấp
            </span>
        </div>

        <!-- HEADER ACTION -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách nhà cung cấp</h2>

            <button
                v-if="can('supplier.create')"
                @click="openCreate"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
                + Nhà cung cấp
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
                        Tìm kiếm và lọc nhà cung cấp
                    </p>
                </div>
            </div>
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="suppliers.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(suppliers.current_page - 1) * suppliers.per_page"
            emptyMessage="Không có nhà cung cấp"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="suppliers.total"
            :itemsPerPage="suppliers.per_page"
            :currentPage="suppliers.current_page"
            :doingShow="suppliers.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <!-- MODAL -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <SupplierForm
                :supplier="selectedSupplier"
                :currencies="currencies"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
    <!-- Modal chi tiết NCC -->
    <Modal v-if="showDebtModal" @close="showDebtModal = false">
        <template #body>
            <SupplierDetail
                :supplier-id="selectedSupplier?.id"
                @close="showDebtModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, h, watch, computed } from "vue";
import axios from "axios";
import { formatMoney, removeMoneyFormat } from "@/config/helpers";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import SupplierDetail from "./SupplierDetail.vue";
import SupplierForm from "./SupplierForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import SearchPage from "../../../components/SearchPage.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import { getStatusLabel } from "@/config/status";
import { usePermission } from "@/composables/usePermission";
/* ================= STATE ================= */
const filterParams = ref({});
const { can, canAny } = usePermission();
const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Tên NCC / Mã NCC / Số điện thoại",
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
    filterParams.value = params;
    getData(1);
}
const suppliers = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const currencies = ref([]);
const perPage = ref(10);
const showModal = ref(false);
const showDebtModal = ref(false);
const selectedSupplier = ref(null);
const debtSummary = ref({});
const debtHistory = ref([]);
const debtLoading = ref(false);

/* ================= COLUMNS ================= */
const columns = [
    { key: "code", label: "Mã NCC", align: "text-center" },
    { key: "name", label: "Tên NCC", align: "text-center" },
    { key: "phone", label: "SĐT", align: "text-center" },
    { key: "email", label: "Email", align: "text-center" },

    {
        label: "Tiền tệ",
        render: (row) =>
            h(
                "span",
                { class: "font-medium text-gray-700" },
                row.currency?.code ?? "-",
            ),
    },
    {
        label: "Công nợ đầu kỳ",
        render: (row) =>
            h(
                "span",
                {},
                `${Number(row.total_debts ?? 0).toLocaleString("vi-VN")} ${row.company_currency?.symbol ?? ""}`,
            ),
    },
    {
        label: "Công nợ hiện tại",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        Number(row.current_debt ?? 0) > 0
                            ? "text-red-600 font-semibold"
                            : "text-green-600 font-semibold",
                },
                `${Number(row.current_debt ?? 0).toLocaleString("vi-VN")} ${row.company_currency?.symbol ?? ""}`,
            ),
    },
    {
        label: "Tạm ứng",
        render: (row) =>
            h(
                "span",
                {},
                `${Number(row.total_advance ?? 0).toLocaleString("vi-VN")} ${row.company_currency?.symbol ?? ""}`,
            ),
    },
    {
        key: "status",
        label: "Trạng thái",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        row.status === "active"
                            ? "bg-green-100 text-green-700 px-2 py-1 rounded"
                            : "bg-red-100 text-red-700 px-2 py-1 rounded",
                },
                getStatusLabel(row.status),
            ),
    },
];

/* ================= ACTIONS (FIX GIỐNG PRODUCT STYLE) ================= */
const actions = computed(() => [
    {
        icon: EditButtonIcon,
        type: "edit",
        hidden: () => !can("supplier.update"),
        onClick: (item) => openEdit(item),
    },
    {
        type: "status",
        // icon đổi theo trạng thái của từng dòng
        icon: (item) => (item.status === "active" ? Lock : Unlock),
        // quyền cũng đổi theo trạng thái của từng dòng:
        // đang active (sắp bị khóa) -> cần quyền lock
        // đang inactive (sắp được mở) -> cần quyền unlock
        hidden: (item) =>
            item.status === "active"
                ? !can("supplier.lock")
                : !can("supplier.unlock"),
        onClick: (item) => toggleStatus(item),
    },
    {
        icon: DetailButtonIcon,
        type: "view",
        hidden: () => !can("supplier.detail"),
        onClick: (item) => openDebtDetail(item),
        tooltip: "Xem chi tiết",
    },
]);

/* ================= METHODS ================= */
function openCreate() {
    selectedSupplier.value = null;
    showModal.value = true;
}

function openEdit(item) {
    selectedSupplier.value = item;
    showModal.value = true;
}
function openDebtDetail(item) {
    selectedSupplier.value = item;
    showDebtModal.value = true;
}

function formatDebt(value) {
    return Number(value ?? 0).toLocaleString("vi-VN");
}

function handlePageChange(page) {
    getData(page);
}
const getCurrencies = async () => {
    try {
        const res = await axios.get("/api/accountant/currencies");

        console.log(res.data);

        currencies.value = res.data.data ?? res.data;
    } catch (e) {
        console.error(e);
    }
};
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
    const res = await axios.get("/api/purchase/suppliers", {
        params: {
            page,
            per_page: perPage.value,
            ...filterParams.value,
        },
    });

    const data = res.data;

    suppliers.value = {
        data: data?.data ?? [],
        total: data?.total ?? 0,
        per_page: data?.per_page ?? 10,
        current_page: data?.current_page ?? 1,
        last_page: data?.last_page ?? 1,
    };
};
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};
const getData = debounce((page = 1) => {
    fetchData(page);
}, 300);

/* toggle status */
async function toggleStatus(item) {
    await axios.patch(`/api/purchase/suppliers/${item.id}/status`);
    getData(suppliers.value.current_page);
}

/* reload */
function reloadData() {
    showModal.value = false;
    getData(suppliers.value.current_page);
}

/* init */
onMounted(async () => {
    await Promise.all([getData(), getCurrencies()]);
});
</script>
