<template>
    <Head title="Công nợ nhà cung cấp" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[
                { text: 'Kế toán', link: '/accountant' },
                { text: 'Công nợ nhà cung cấp', link: null },
            ]"
        />

        <div class="flex justify-between items-center mb-5">
            <div>
                <h2 class="text-2xl font-bold">Công nợ nhà cung cấp</h2>
                <p class="text-sm text-gray-500">
                    Theo dõi công nợ phải trả và lịch sử thanh toán với nhà cung
                    cấp
                </p>
            </div>
        </div>

        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
        >
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <DataTable
            :columns="columns"
            :data="suppliers.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(suppliers.current_page - 1) * suppliers.per_page"
            emptyMessage="Không có nhà cung cấp"
        />

        <Pagination
            :totalItems="suppliers.total"
            :itemsPerPage="suppliers.per_page"
            :currentPage="suppliers.current_page"
            :doingShow="suppliers.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <Modal v-if="showDetail" @close="showDetail = false" size="large">
        <template #body>
            <div class="p-4 bg-white rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">
                            {{ selectedSupplier?.name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ selectedSupplier?.code }}
                        </p>
                    </div>
                    <button
                        class="text-gray-400 hover:text-red-500"
                        @click="showDetail = false"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="detailLoading" class="text-sm text-gray-500">
                    Đang tải...
                </div>
                <div v-else>
                    <div class="grid md:grid-cols-3 gap-3 mb-4">
                        <div class="rounded-xl border p-3 bg-red-50">
                            <p class="text-xs uppercase text-red-500">
                                Còn phải trả
                            </p>
                            <p class="text-xl font-semibold text-red-600">
                                {{ formatMoney(detailSummary.remaining_debt) }}
                            </p>
                        </div>
                        <div class="rounded-xl border p-3 bg-blue-50">
                            <p class="text-xs uppercase text-blue-500">
                                Tổng phát sinh
                            </p>
                            <p class="text-xl font-semibold text-blue-600">
                                {{
                                    formatMoney(detailSummary.total_receivable)
                                }}
                            </p>
                        </div>
                        <div class="rounded-xl border p-3 bg-green-50">
                            <p class="text-xs uppercase text-green-500">
                                Đã thanh toán
                            </p>
                            <p class="text-xl font-semibold text-green-600">
                                {{ formatMoney(detailSummary.total_paid) }}
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
                                        {{ formatMoney(item.amount) }}
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
import { ref, h, onMounted } from "vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import SearchPage from "@/components/SearchPage.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import { formatMoney } from "@/config/helpers";

const suppliers = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});
const filterParams = ref({});
const showDetail = ref(false);
const selectedSupplier = ref(null);
const detailSummary = ref({});
const debtHistory = ref([]);
const detailLoading = ref(false);

const filters = [
    { name: "search", type: "text", placeholder: "Tên NCC / mã NCC / SĐT" },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            { value: "active", label: "Đang hoạt động" },
            { value: "inactive", label: "Ngừng hoạt động" },
        ],
    },
];

const columns = [
    { key: "code", label: "Mã NCC", align: "text-start" },
    { key: "name", label: "Tên NCC", align: "text-start" },
    { key: "phone", label: "SĐT", align: "text-start" },
    {
        label: "Công nợ hiện tại",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        Number(row.current_debt ?? 0) > 0
                            ? "text-red-600 font-semibold"
                            : "text-green-600 font-semibold",
                },
                formatMoney(row.current_debt ?? 0, row.currency),
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

const actions = [
    {
        icon: DetailButtonIcon,
        type: "view",
        onClick: (item) => openDetail(item),
    },
];

function handleFilter(params) {
    filterParams.value = params;
    getData(1);
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

const fetchData = async (page = 1) => {
    const res = await axios.get("/api/accountant/suppliers-debt", {
        params: { page, ...filterParams.value },
    });
    const data = res.data;
    suppliers.value = {
        data: data.data ?? [],
        total: data.total ?? 0,
        per_page: data.per_page ?? 10,
        current_page: data.current_page ?? 1,
        last_page: data.last_page ?? 1,
    };
};

const getData = debounce(fetchData, 300);

async function openDetail(item) {
    selectedSupplier.value = item;
    showDetail.value = true;
    detailLoading.value = true;
    try {
        const res = await axios.get(
            `/api/accountant/suppliers-debt/${item.id}/detail`,
        );
        detailSummary.value = res.data.debt_summary || {};
        debtHistory.value = res.data.debt_history || [];
    } finally {
        detailLoading.value = false;
    }
}


onMounted(() => {
    getData(1);
});
</script>
