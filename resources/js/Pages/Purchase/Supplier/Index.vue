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

    <Modal v-if="showDebtModal" @close="showDebtModal = false" size="large">
        <template #body>
            <div class="p-4 bg-white rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">
                            Công nợ nhà cung cấp
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ selectedSupplier?.name }}
                        </p>
                    </div>
                    <button
                        class="text-gray-400 hover:text-red-500"
                        @click="showDebtModal = false"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="debtLoading" class="text-sm text-gray-500">
                    Đang tải...
                </div>
                <div v-else>
                    <div class="grid md:grid-cols-3 gap-3 mb-4">
                        <div class="rounded-xl border p-3 bg-red-50">
                            <p class="text-xs uppercase text-red-500">
                                Còn phải trả
                            </p>
                            <p class="text-xl font-semibold text-red-600">
                                {{ formatDebt(debtSummary.remaining_debt) }}
                            </p>
                        </div>
                        <div class="rounded-xl border p-3 bg-blue-50">
                            <p class="text-xs uppercase text-blue-500">
                                Tổng phát sinh
                            </p>
                            <p class="text-xl font-semibold text-blue-600">
                                {{ formatDebt(debtSummary.total_receivable) }}
                            </p>
                        </div>
                        <div class="rounded-xl border p-3 bg-green-50">
                            <p class="text-xs uppercase text-green-500">
                                Đã thanh toán
                            </p>
                            <p class="text-xl font-semibold text-green-600">
                                {{ formatDebt(debtSummary.total_paid) }}
                            </p>
                        </div>
                    </div>

                    <div class="border rounded-xl overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left p-2">Thời gian</th>
                                    <th class="text-left p-2">Loại</th>
                                    <th class="text-left p-2">Số tiền</th>
                                    <th class="text-left p-2">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in debtHistory"
                                    :key="item.id"
                                    class="border-t"
                                >
                                    <td class="p-2">
                                        {{
                                            item.created_at
                                                ? new Date(
                                                      item.created_at,
                                                  ).toLocaleString()
                                                : "-"
                                        }}
                                    </td>
                                    <td class="p-2">{{ item.type }}</td>
                                    <td
                                        class="p-2"
                                        :class="
                                            item.amount >= 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{ formatDebt(item.amount) }}
                                    </td>
                                    <td class="p-2">{{ item.note || "-" }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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

import SupplierForm from "./SupplierForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import SearchPage from "../../../components/SearchPage.vue";

/* ================= STATE ================= */
const filterParams = ref({});

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
    console.log("FILTER:", params);
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
            h("span", {}, Number(row.total_debts ?? 0).toLocaleString("vi-VN")),
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
                Number(row.current_debt ?? 0).toLocaleString("vi-VN"),
            ),
    },
    {
        label: "Tạm ứng",
        render: (row) =>
            h(
                "span",
                {},
                Number(row.total_advance ?? 0).toLocaleString("vi-VN"),
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
        icon: Unlock,
        type: "status",
        onClick: (item) => toggleStatus(item),
    },
    {
        icon: EditButtonIcon,
        type: "view",
        onClick: (item) => openDebtDetail(item),
    },
];

/* ================= METHODS ================= */
function openCreate() {
    selectedSupplier.value = null;
    showModal.value = true;
}

function openEdit(item) {
    selectedSupplier.value = item;
    showModal.value = true;
}

async function openDebtDetail(item) {
    selectedSupplier.value = item;
    showDebtModal.value = true;
    debtLoading.value = true;
    try {
        const res = await axios.get(
            `/api/purchase/suppliers/${item.id}/detail`,
        );
        debtSummary.value = res.data.debt_summary || {};
        debtHistory.value = res.data.debt_history || [];
    } finally {
        debtLoading.value = false;
    }
}

function formatDebt(value) {
    return Number(value ?? 0).toLocaleString("vi-VN");
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
    const res = await axios.get("/api/purchase/suppliers", {
        params: {
            page,
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
onMounted(() => {
    getData();
});
</script>
