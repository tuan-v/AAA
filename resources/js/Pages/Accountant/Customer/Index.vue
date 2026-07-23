<template>
    <Head title="Công nợ khách hàng" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[
                { text: 'Kế toán', link: '/accountant' },
                { text: 'Công nợ khách hàng', link: null },
            ]"
        />

        <div class="flex justify-between items-center mb-5">
            <div>
                <h2 class="text-2xl font-bold">Công nợ khách hàng</h2>
                <p class="text-sm text-gray-500">
                    Theo dõi công nợ phải thu và lịch sử giao dịch của khách
                    hàng
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
            :data="customers.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(customers.current_page - 1) * customers.per_page"
            emptyMessage="Không có khách hàng"
        />

        <Pagination
            :totalItems="customers.total"
            :itemsPerPage="customers.per_page"
            :currentPage="customers.current_page"
            :doingShow="customers.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <CustomerDetail
        v-if="showDetail && selectedCustomer?.id"
        :customer-id="selectedCustomer.id"
        @close="closeDetail"
    />
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, h, onMounted } from "vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import SearchPage from "@/components/SearchPage.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import { formatMoney } from "@/config/helpers";

// Import component chi tiết mới tách
import CustomerDetail from "@/Pages/Sale/Customer/CustomerDetail.vue";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

const perPage = ref(10);
const customers = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});
const filterParams = ref({});
const showDetail = ref(false);
const selectedCustomer = ref(null);

const filters = [
    { name: "search", type: "text", placeholder: "Tên KH / mã KH / SĐT" },
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
    { key: "code", label: "Mã KH", align: "text-start" },
    { key: "name", label: "Tên KH", align: "text-start" },
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
                formatMoney(row.current_debt ?? 0, row.company_currency),
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
    const res = await axios.get("/api/accountant/customers-debt", {
        params: {
            page,
            per_page: perPage.value,
            ...filterParams.value,
        },
    });
    const data = res.data;
    customers.value = {
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
const getData = debounce(fetchData, 300);

// Hàm mở chi tiết giờ cực kỳ ngắn gọn
function openDetail(item) {
    selectedCustomer.value = item;
    showDetail.value = true;
}

function closeDetail() {
    showDetail.value = false;
    selectedCustomer.value = null;
}

useRealtimeRefresh(() => fetchData(customers.value.current_page || 1));

onMounted(() => {
    getData(1);
});
</script>
