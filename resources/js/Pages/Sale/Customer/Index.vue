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
                v-if="can('khach_hang.them')"
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
            @items-per-page-change="handlePerPageChange"
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
    <!-- ==================== MODALS ==================== -->
    <!-- Modal Chi tiết -->
    <Modal v-if="showDetailModal" @close="showDetailModal = false" size="large">
        <template #header>
            <h3 class="text-xl font-semibold">Chi tiết khách hàng</h3>
        </template>
        <template #body>
            <CustomerDetail
                :customer-id="selectedCustomerId"
                @close="showDetailModal = false"
                @create-order="openCreateOrder"
            />
        </template>
    </Modal>

    <!-- Modal Tạo Đơn Hàng -->
    <Modal v-if="showOrderModal" @close="showOrderModal = false" size="large">
        <template #header>
            <h3 class="text-xl font-semibold">Tạo đơn hàng mới</h3>
        </template>
        <template #body>
            <SaleOrderForm
                :customer-id="createOrderCustomerId"
                :customers="customers.data"
                :currencies="currencies"
                :products="products"
                :provinces="provinces"
                @saved="handleOrderSaved"
                @close="showOrderModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, h, watch, computed } from "vue";
import axios from "axios";
import {
    formatMoney,
    getValidationMessage,
    removeMoneyFormat,
} from "@/config/helpers";
import { toast } from "vue3-toastify";
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
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import CustomerDetail from "./CustomerDetail.vue";
import SaleOrderForm from "../Order/SaleOrderForm.vue";
import { usePermission } from "@/composables/usePermission";
/* ================= STATE ================= */
const filterParams = ref({});
const { can, canAny } = usePermission();
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
const perPage = ref(10);
const products = ref([]);
const provinces = ref([]);
const currencies = ref([]);
const selectedCustomerId = ref(null);
const showDetailModal = ref(false);
const showModal = ref(false);
const selectedCustomer = ref(null);
const showOrderModal = ref(false);
const createOrderCustomerId = ref(null);
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
        label: "Công nợ đầu kỳ",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                {},
                formatMoney(row.opening_debt ?? 0, row.currency),
            ),
    },
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
const actions = computed(() => [
    {
        icon: EditButtonIcon,
        type: "edit",
        hidden: () => !can("khach_hang.sua"),
        onClick: (item) => openEdit(item),
    },
    {
        type: "status",
        // icon đổi theo trạng thái của từng dòng
        icon: (item) => (item.status === "active" ? Lock : Unlock),
        iconByItem: true,
        // quyền cũng đổi theo trạng thái của từng dòng:
        // đang active (sắp bị khóa) -> cần quyền lock
        // đang inactive (sắp được mở) -> cần quyền unlock
        hidden: (item) =>
            item.status === "active"
                ? !can("khach_hang.khoa")
                : !can("khach_hang.mo_khoa"),
        onClick: (item) => toggleStatus(item),
    },
    {
        icon: DetailButtonIcon,
        type: "view",
        hidden: () => !can("khach_hang.xem"),
        onClick: (item) => openDetail(item),
        tooltip: "Xem chi tiết",
    },
]);
/* ================= METHODS ================= */
function openCreate() {
    selectedCustomer.value = null;
    showModal.value = true;
}

function openEdit(item) {
    selectedCustomer.value = item;
    showModal.value = true;
}
function openDetail(item) {
    selectedCustomerId.value = item.id;
    showDetailModal.value = true;
}
function openCreateOrder(customerId) {
    showDetailModal.value = false;

    createOrderCustomerId.value = customerId;

    showOrderModal.value = true;
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
            per_page: perPage.value,
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
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
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
    try {
        const [currencyRes, customerRes, productRes, provinceRes] =
            await Promise.all([
            axios.get("/api/currencies/for-select"),
            axios.get("/api/sale/customers/all"),
            axios.get("/api/products/for-select"),
            axios.get("/api/provinces"),
            ]);

        currencies.value = currencyRes.data;
        products.value = productRes.data;
        provinces.value = provinceRes.data;
    } catch (error) {
        toast.error(getValidationMessage(error, "Không thể tải dữ liệu tạo đơn bán."));
    }
    getData(1);
});
</script>
