<template>
    <Head title="Quản lý Đơn hàng" />

    <AdminLayout>
        <PageBreadcrumb
            title="Quản lý Đơn hàng"
            :items="[{ text: 'Đơn hàng', link: null }]"
        />

        <!-- TAB -->
        <div class="mb-6 border-b">
            <div class="flex">
                <button
                    @click="activeTab = 'purchase'"
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
                    @click="activeTab = 'sale'"
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

        <!-- TAB ĐƠN MUA -->
        <div v-if="activeTab === 'purchase'">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl font-bold">Danh sách đơn mua hàng</h2>
            </div>

            <div class="grid grid-cols-4 gap-4 mb-5">
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <p class="text-gray-500 text-sm">Tổng đơn</p>
                    <p class="text-2xl font-bold">
                        {{ purchaseOrders.total }}
                    </p>
                </div>

                <div class="bg-yellow-50 p-4 rounded-xl border">
                    <p class="text-yellow-600 text-sm">Nhập một phần</p>
                    <p class="text-2xl font-bold">
                        {{
                            purchaseOrders.data.filter(
                                (x) => x.status === "partial",
                            ).length
                        }}
                    </p>
                </div>

                <div class="bg-blue-50 p-4 rounded-xl border">
                    <p class="text-blue-600 text-sm">Chờ nhập kho</p>
                    <p class="text-2xl font-bold">
                        {{
                            purchaseOrders.data.filter(
                                (x) => x.status === "approved",
                            ).length
                        }}
                    </p>
                </div>

                <div class="bg-green-50 p-4 rounded-xl border">
                    <p class="text-green-600 text-sm">Nhập đầy đủ</p>
                    <p class="text-2xl font-bold">
                        {{
                            purchaseOrders.data.filter(
                                (x) => x.status === "completed",
                            ).length
                        }}
                    </p>
                </div>
            </div>

            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
            >
                <SearchPage
                    :filters="purchaseFilters"
                    @filter="handlePurchaseFilter"
                />
            </div>

            <DataTable
                :columns="purchaseColumns"
                :data="purchaseOrders.data"
                :showIndex="true"
                :actions="purchaseActions"
                :indexOffset="
                    (purchaseOrders.current_page - 1) * purchaseOrders.per_page
                "
                emptyMessage="Không có đơn mua hàng"
            />

            <Pagination
                :totalItems="purchaseOrders.total"
                :itemsPerPage="purchaseOrders.per_page"
                :currentPage="purchaseOrders.current_page"
                :doingShow="purchaseOrders.data.length"
                @page-change="handlePurchasePageChange"
                @items-per-page-change="handlePurchasePerPageChange"
            />
        </div>

        <!-- TAB ĐƠN BÁN -->
        <div v-else>
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl font-bold">Danh sách đơn bán hàng</h2>
            </div>

            <div class="grid grid-cols-4 gap-4 mb-5">
                <div class="bg-white p-4 rounded-xl border shadow-sm">
                    <p class="text-gray-500 text-sm">Tổng đơn</p>
                    <p class="text-2xl font-bold">{{ saleOrders.total }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-xl border">
                    <p class="text-yellow-600 text-sm">Xuất một phần</p>
                    <p class="text-2xl font-bold">
                        {{
                            saleOrders.data.filter(
                                (x) => x.status === "partial",
                            ).length
                        }}
                    </p>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl border">
                    <p class="text-blue-600 text-sm">Chờ xuất kho</p>
                    <p class="text-2xl font-bold">
                        {{
                            saleOrders.data.filter((x) =>
                                ["approved"].includes(x.status),
                            ).length
                        }}
                    </p>
                </div>

                <div class="bg-green-50 p-4 rounded-xl border">
                    <p class="text-green-600 text-sm">Xuất đầy đủ</p>
                    <p class="text-2xl font-bold">
                        {{
                            saleOrders.data.filter((x) =>
                                ["completed"].includes(x.status),
                            ).length
                        }}
                    </p>
                </div>
            </div>

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
                :doingShow="saleOrders.data.length"
                :currentPage="saleOrders.current_page"
                @page-change="handleSalePageChange"
                @items-per-page-change="handleSalePerPageChange"
            />
        </div>
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <component
                :is="currentFormComponent"
                :key="modalKey"
                :order="selectedOrder"
                @saved="reloadCurrentTab"
                @close="showModal = false"
            />
        </template>
    </Modal>
    <Modal v-if="showSaleDetailModal" @close="showSaleDetailModal = false">
        <template #body>
            <SaleOrderDetail
                :order="detailOrder"
                @close="showSaleDetailModal = false"
            />
        </template>
    </Modal>
    <Modal
        v-if="showPurchaseDetailModal"
        @close="showPurchaseDetailModal = false"
    >
        <template #body>
            <PurchaseOrderDetail
                :order="purchaseDetailOrder"
                @close="showPurchaseDetailModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { ref, onMounted, h, computed } from "vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import SearchPage from "@/components/SearchPage.vue";
import Modal from "@/components/Modal.vue";

import { formatMoney } from "@/config/helpers";
import { toast } from "vue3-toastify";

import ImportIcon from "../../../icons/ImportIcon.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import CheckIcon from "@/icons/CheckIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import SaleOrderDetail from "../../Sale/Order/SaleOrderDetail.vue";
import PurchaseOrderDetail from "../../Purchase/Order/PurchaseOrderDetail.vue";
import { usePermission } from "@/composables/usePermission";

const canViewPage = computed(() => can("phieu_kho.xem"));
const { can } = usePermission();
const activeTab = ref("purchase");
const showModal = ref(false);
const modalKey = ref(0);
const selectedOrder = ref(null);
const currentFormComponent = ref(null);
// ==================== STATUS CONFIG RIÊNG CHO TỪNG TAB ====================
const purchaseStatusConfig = {
    approved: { text: "Đang chờ nhập kho", class: "bg-blue-100 text-blue-700" },
    partial: { text: "Nhập một phần", class: "bg-orange-100 text-orange-700" },
    completed: { text: "Nhập đầy đủ", class: "bg-green-100 text-green-700" },
    cancelled: { text: "Đã hủy", class: "bg-red-100 text-red-700" },
};

const saleStatusConfig = {
    partial: { text: "Xuất một phần", class: "bg-yellow-100 text-yellow-700" },
    approved: { text: "Đang chờ xuất kho", class: "bg-blue-100 text-blue-700" },
    completed: { text: "Xuất đầy đủ", class: "bg-green-100 text-green-700" },
    cancelled: { text: "Đã hủy", class: "bg-red-100 text-red-700" },
};
// ==================== ĐƠN MUA ====================
const suppliers = ref([]);
const products = ref([]);
const currencies = ref([]);
const purchaseDetailOrder = ref(null);
const showPurchaseDetailModal = ref(false);
const purchasePerPage = ref(10);
const purchaseOrders = ref({
    data: [],
    current_page: 1,
    per_page: 10,
    total: 0,
});
const purchaseFilters = [
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
const purchaseColumns = [
    {
        key: "code",
        label: "Mã đơn",
    },

    {
        label: "Nhà cung cấp",
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

        render: (row) => {
            const status =
                purchaseStatusConfig[row.status] ??
                purchaseStatusConfig.pending;

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
async function openDetail(item) {
    try {
        const res = await axios.get(`/api/sale/orders/${item.id}`);

        detailOrder.value = res.data.data ?? res.data;

        showSaleDetailModal.value = true;
    } catch (error) {
        toast.error("Không tải được thông tin đơn hàng");
    }
}
const purchaseActions = [
    {
        icon: ImportIcon,
        title: "Nhập kho",
        hidden: (row) =>
            !can("phieu_kho.them") ||
            !["approved", "partial"].includes(row.status),
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
    {
        icon: DetailButtonIcon,
        title: "Chi tiết",
        hidden: () => !can("don_mua.xem_chi_tiet"),
        onClick: openPurchaseDetail,
    },
];
function openStockIn(item) {
    window.location.href = `/warehouse/slips/purchasecreate?order_id=${item.id}`;
}
// ==================== ĐƠN BÁN ====================
const salePerPage = ref(10);
const detailOrder = ref(null);
const showSaleDetailModal = ref(false);
const customers = ref([]);
const provinces = ref([]);
const wards = ref([]);
const saleOrders = ref({ data: [], current_page: 1, per_page: 10, total: 0 });
const saleFilters = [
    { name: "search", type: "text", placeholder: "Mã đơn / Tên khách hàng" },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            { value: "partial", label: "Xuất một phần" },
            { value: "approved", label: "Đang chờ xuất kho" },
            { value: "completed", label: "Xuất đầy đủ" },
            { value: "cancelled", label: "Đã hủy" },
        ],
    },
];

const saleColumns = [
    { key: "code", label: "Mã đơn" },
    {
        label: "Khách hàng",
        render: (row) =>
            h("div", { class: "flex flex-col" }, [
                h(
                    "span",
                    { class: "font-medium text-gray-800" },
                    row.customer?.name ?? "-",
                ),
                h(
                    "span",
                    { class: "text-xs text-gray-500" },
                    row.customer?.code ?? "",
                ),
            ]),
    },
    {
        label: "Ngày giao",
        render: (row) => h("span", {}, row.expected_delivery_date ?? "-"),
    },
    {
        label: "SL SP",
        render: (row) =>
            h(
                "span",
                { class: "font-semibold text-blue-600" },
                row.total_quantity ?? 0,
            ),
    },
    {
        label: "Tổng tiền",
        render: (row) =>
            h(
                "span",
                { class: "font-semibold text-green-600" },
                `${formatMoney(row.total_amount)} ${row.currency?.symbol ?? ""}`,
            ),
    },
    {
        label: "Công nợ",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        Number(row.remaining_debt) > 0
                            ? "text-red-600 font-semibold"
                            : "text-green-600",
                },
                formatMoney(row.remaining_debt ?? 0),
            ),
    },
    {
        label: "Trạng thái",
        render: (row) => {
            const status = saleStatusConfig[row.status];
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

const saleActions = [
    {
        icon: ImportIcon,
        title: "Xuất kho",
        hidden: (row) =>
            !can("phieu_kho.them") ||
            !["approved", "partial"].includes(row.status),
        onClick: (item) =>
            (window.location.href = `/warehouse/slips/salecreate?order_id=${item.id}&type=sale`),
    },
    {
        icon: DetailButtonIcon,
        title: "Chi tiết",
        hidden: () => !can("don_ban.xem_chi_tiet"),
        onClick: openDetail,
    },
];

// API
async function openPurchaseDetail(item) {
    try {
        const res = await axios.get(`/api/purchase/orders/${item.id}`);

        purchaseDetailOrder.value = res.data;

        showPurchaseDetailModal.value = true;
    } catch (err) {
        toast.error("Không tải được chi tiết đơn mua");
    }
}
async function getPurchaseData(page = 1) {
    const res = await axios.get("/api/warehouse/orders", {
        params: {
            page,
            per_page: purchasePerPage.value,
            search: search.value,
            status: statusFilter.value,
        },
    });
    purchaseOrders.value = res.data;
    purchasePerPage.value = res.data.per_page;
}
const handlePurchasePerPageChange = (value) => {
    purchasePerPage.value = value;
    getPurchaseData(1);
};

async function getSaleData(page = 1) {
    const res = await axios.get("/api/saleorders/warehouse", {
        params: {
            page,
            per_page: salePerPage.value,
            search: search.value,
            status: statusFilter.value,
        },
    });
    saleOrders.value = res.data;
    salePerPage.value = res.data.per_page;
}
const handleSalePerPageChange = (value) => {
    salePerPage.value = value;
    getSaleData(1);
};
const search = ref("");
const statusFilter = ref("");
function handlePurchaseFilter(params) {
    search.value = params.search || "";
    statusFilter.value = params.status || "";

    getPurchaseData();
}
function handleSaleFilter(params) {
    search.value = params.search || "";
    statusFilter.value = params.status || "";
    getSaleData();
}
function handlePurchasePageChange(page) {
    getPurchaseData(page);
}
function handleSalePageChange(page) {
    getSaleData(page);
}

function reloadCurrentTab() {
    showModal.value = false;
    if (activeTab.value === "purchase") getPurchaseData();
    else getSaleData();
}

function openCreateSale() {
    selectedOrder.value = null;
    currentFormComponent.value = SaleOrderForm;
    modalKey.value++;
    showModal.value = true;
}

onMounted(() => {
    getPurchaseData();
    getSaleData();
});
</script>
