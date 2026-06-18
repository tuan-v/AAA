<template>
    <Head title="Quản lý Đơn hàng" />

    <AdminLayout>
        <PageBreadcrumb
            title="Quản lý Đơn hàng"
            :items="[{ text: 'Đơn hàng', link: null }]"
        />

        <!-- TAB HEADER -->
        <div class="mb-6 border-b">
            <div class="flex">
                <button
                    @click="switchTab('purchase')"
                    :class="[
                        'px-8 py-4 font-semibold text-lg border-b-2 transition-all',
                        activeTab === 'purchase'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700',
                    ]"
                >
                    Đơn Mua Hàng
                </button>
                <button
                    @click="switchTab('sale')"
                    :class="[
                        'px-8 py-4 font-semibold text-lg border-b-2 transition-all',
                        activeTab === 'sale'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700',
                    ]"
                >
                    Đơn Bán Hàng
                </button>
            </div>
        </div>
        <div v-if="activeTab === 'purchase'">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl font-bold">Danh sách đơn mua hàng</h2>
            </div>
            <div class="grid grid-cols-4 gap-4 mb-5">
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <p class="text-gray-500 text-sm">Tổng đơn</p>
                    <p class="text-2xl font-bold">
                        {{ orders.total }}
                    </p>
                </div>

                <div class="bg-yellow-50 p-4 rounded-xl border">
                    <p class="text-yellow-600 text-sm">Nhập một phần</p>
                    <p class="text-2xl font-bold">
                        {{
                            orders.data.filter((x) => x.status === "partial")
                                .length
                        }}
                    </p>
                </div>

                <div class="bg-blue-50 p-4 rounded-xl border">
                    <p class="text-blue-600 text-sm">Chờ nhập kho</p>
                    <p class="text-2xl font-bold">
                        {{
                            orders.data.filter((x) => x.status === "approved")
                                .length
                        }}
                    </p>
                </div>

                <div class="bg-green-50 p-4 rounded-xl border">
                    <p class="text-green-600 text-sm">Nhập đầy đủ</p>
                    <p class="text-2xl font-bold">
                        {{
                            orders.data.filter((x) => x.status === "completed")
                                .length
                        }}
                    </p>
                </div>
            </div>
            <!-- FILTER -->
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
            >
                <SearchPage :filters="filters" @filter="handleFilter" />
            </div>
            <DataTable
                :columns="columns"
                :data="orders.data"
                :showIndex="true"
                :actions="actions"
                :indexOffset="(orders.current_page - 1) * orders.per_page"
                emptyMessage="Không có đơn mua hàng"
            />

            <Pagination
                :totalItems="orders.total"
                :itemsPerPage="orders.per_page"
                :currentPage="orders.current_page"
                :doingShow="orders.data.length"
                @page-change="handlePageChange"
            />
        </div>
        <div v-else>
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl font-bold">Danh sách đơn bán hàng</h2>
            </div>
            <div class="grid grid-cols-4 gap-4 mb-5">
                <div class="grid grid-cols-4 gap-4 mb-5">
                    <div class="bg-white p-4 rounded-xl border shadow-sm">
                        <p class="text-gray-500 text-sm">Tổng đơn</p>
                        <p class="text-2xl font-bold">{{ orders.total }}</p>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-xl border">
                        <p class="text-yellow-600 text-sm">Chờ xử lý</p>
                        <p class="text-2xl font-bold">
                            {{
                                orders.data.filter(
                                    (x) => x.status === "pending",
                                ).length
                            }}
                        </p>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-xl border">
                        <p class="text-blue-600 text-sm">Đã duyệt</p>
                        <p class="text-2xl font-bold">
                            {{
                                orders.data.filter((x) =>
                                    ["approved", "completed"].includes(
                                        x.status,
                                    ),
                                ).length
                            }}
                        </p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-xl border">
                        <p class="text-green-600 text-sm">Doanh thu</p>
                        <p class="text-2xl font-bold">
                            {{
                                formatMoney(
                                    orders.data.reduce(
                                        (sum, item) =>
                                            sum +
                                            Number(item.total_amount ?? 0),
                                        0,
                                    ),
                                )
                            }}
                        </p>
                    </div>
                </div>
            </div>
            <!-- FILTER -->
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
            >
                <SearchPage :filters="saleFilters" @filter="handleSaleFilter" />
            </div>

            <DataTable
                :columns="saleColumns"
                :data="saleOrders.data"
                :showIndex="true"
                :actions="saleActions"
                :indexOffset="
                    (saleOrders.current_page - 1) * saleOrders.per_page
                "
                emptyMessage="Không có đơn bán hàng"
            />

            <Pagination
                :totalItems="saleOrders.total"
                :itemsPerPage="saleOrders.per_page"
                :currentPage="saleOrders.current_page"
                @page-change="handleSalePageChange"
            />
        </div>
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <PurchaseOrderForm
                :key="supplierKey"
                :order="selectedOrder"
                :suppliers="suppliers"
                :currencies="currencies"
                :products="products"
                @saved="reloadData"
                @close="showModal = false"
                @supplier-created="onSupplierCreated"
            />
        </template>
    </Modal>
</template>

<script setup>
import { h, ref, onMounted, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import { formatMoney, removeMoneyFormat } from "@/config/helpers";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import CheckIcon from "@/icons/CheckIcon.vue";
import SearchPage from "@/components/SearchPage.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import ImportIcon from "../../../icons/ImportIcon.vue";
// import Index from "../../Sale/Order/Index.vue";
// import Index from "../../Purchase/Order/Index.vue";
// const activeTab = ref("purchase");
// const purchaseRef = ref(null);
// const saleRef = ref(null);
// function switchTab(tab) {
//     activeTab.value = tab;
// }
// ==================== ĐƠN MUA ====================
const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Tìm mã đơn hoặc NCC...",
    },

    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            {
                value: "approved",
                label: "Đang chờ nhập kho",
            },
            {
                value: "partial",
                label: "Nhập một phần",
            },
            {
                value: "completed",
                label: "Nhập đầy đủ",
            },
            {
                value: "cancelled",
                label: "Đã hủy",
            },
        ],
    },
];
const statusConfig = {
    approved: {
        text: "Đang chờ nhập kho",
        class: "bg-blue-100 text-blue-700",
    },

    partial: {
        text: "Nhập một phần",
        class: "bg-orange-100 text-orange-700",
    },

    completed: {
        text: "Nhập đầy đủ",
        class: "bg-green-100 text-green-700",
    },

    cancelled: {
        text: "Đã hủy",
        class: "bg-red-100 text-red-700",
    },
};
function handleFilter(params) {
    search.value = params.search || "";
    statusFilter.value = params.status || "";

    getData();
}
const orders = ref({
    data: [],
});
const supplierKey = ref(0);
const onSupplierCreated = async (newSupplier) => {
    await fetchSuppliers(); // Reload danh sách NCC
    // Modal PurchaseOrderForm vẫn đang mở, không cần làm gì thêm
    // Buộc cập nhật lại form (quan trọng)
    selectedOrder.value = { ...selectedOrder.value }; // trigger reactivity

    console.log("✅ Đã reload suppliers:", suppliers.value.length);
};
const suppliers = ref([]);
const products = ref([]);
const currencies = ref([]);

const search = ref("");
const statusFilter = ref("");
const activeTab = ref("purchase");
const showModal = ref(false);

const selectedOrder = ref(null);

const columns = [
    {
        key: "code",
        label: "Mã đơn",
        align: "text-start",
    },

    {
        label: "Nhà cung cấp",
        align: "text-start",
        render: (row) => {
            return h(
                "span",
                { class: "text-gray-700" },
                row.supplier?.name ?? "—",
            );
        },
    },
    {
        key: "expected_received_date",
        label: "Dự kiến nhận",
        align: "text-right",
        render: (row) => {
            return h(
                "span",
                { class: "text-gray-700" },
                row.expected_received_date ?? "—",
            );
        },
    },

    {
        label: "Tổng tiền",
        align: "text-right",
        render: (row) => {
            const value = new Intl.NumberFormat("vi-VN").format(
                row.total_amount ?? 0,
            );

            const symbol = row.currency?.symbol ?? "";

            return h(
                "span",
                {
                    class: "font-semibold ",
                },
                `${value} ${symbol}`,
            );
        },
    },

    {
        label: "Trạng thái",
        align: "text-start",
        render: (row) => {
            const status = statusConfig[row.status] ?? statusConfig.approved;

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

const actions = [
    {
        icon: ImportIcon,
        title: "Nhập kho",
        visible: (row) => {
            return row.status === "approved" || row.status === "partial";
        },
        disabled: (row) => row.status === "completed",
        class: (row) =>
            row.status === "completed" ? "opacity-40 cursor-not-allowed" : "",

        onClick: (item) => {
            if (item.status === "completed") {
                toast.warning("Đơn hàng đã được nhập kho đầy đủ", {
                    position: "top-right",
                    autoClose: 3000,
                    theme: "colored",
                });

                return;
            }

            openStockIn(item);
        },
    },
];
function openStockIn(item) {
    console.log(item);
    window.location.href = `/warehouse/slips/create?order_id=${item.id}`;
}
async function getData(page = 1) {
    const res = await axios.get("/api/warehouse/orders", {
        params: {
            page,
            search: search.value,
            // status: statusFilter.value,
            status: "approved",
        },
    });

    orders.value = res.data;
}

async function fetchSuppliers() {
    const res = await axios.get("/api/purchase/suppliers/all");

    suppliers.value = res.data.data ?? res.data;
}

async function fetchProducts() {
    const res = await axios.get("/api/warehouse/products");

    products.value = res.data.data;
}

async function fetchCurrencies() {
    const res = await axios.get("/api/currencies");

    currencies.value = res.data.data ?? res.data;
}

function changeTab(tab) {
    activeTab.value = tab;
    getData(1);
}
function reloadData() {
    showModal.value = false;
    getData();
}

function handlePageChange(page) {
    getData(page);
}

onMounted(() => {
    getData();
    fetchSuppliers();
    fetchProducts();
    fetchCurrencies();
});
</script>
