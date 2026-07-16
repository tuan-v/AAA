<template>
    <Head title="Đơn bán hàng" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[{ text: 'Đơn bán hàng', link: null }]"
        />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách đơn bán hàng</h2>

            <button
                v-if="can('sale_order.create')"
                @click="openCreate"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
            >
                + Tạo đơn bán
            </button>
        </div>

        <!-- Thống kê -->
        <div class="grid grid-cols-4 gap-4 mb-5">
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <p class="text-gray-500 text-sm">Tổng đơn</p>
                <p class="text-2xl font-bold">{{ orders.total }}</p>
            </div>

            <div class="bg-yellow-50 p-4 rounded-xl border">
                <p class="text-yellow-600 text-sm">Chờ xử lý</p>
                <p class="text-2xl font-bold">
                    {{
                        orders.data.filter((x) => x.status === "pending").length
                    }}
                </p>
            </div>

            <div class="bg-blue-50 p-4 rounded-xl border">
                <p class="text-blue-600 text-sm">Đã duyệt</p>
                <p class="text-2xl font-bold">
                    {{
                        orders.data.filter((x) =>
                            ["approved", "completed"].includes(x.status),
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
                                    sum + Number(item.total_amount ?? 0),
                                0,
                            ),
                        )
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
            emptyMessage="Không có đơn bán hàng"
        />

        <Pagination
            :totalItems="orders.total"
            :itemsPerPage="orders.per_page"
            :currentPage="orders.current_page"
            :doingShow="orders.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <!-- Modal -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <SaleOrderForm
                :key="orderKey"
                :order="selectedOrder"
                :customers="customers"
                :currencies="currencies"
                :products="products"
                :provinces="provinces"
                :wards="wards"
                @saved="reloadData"
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

    <!-- Confirm Duyệt Đơn -->
    <div
        v-if="showConfirm"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div class="bg-white w-[420px] rounded-xl shadow-xl p-6 animate-fadeIn">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="bg-yellow-100 text-yellow-600 p-3 rounded-full text-xl"
                >
                    ⚠️
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Xác nhận duyệt đơn</h3>
                    <p class="text-sm text-gray-500">
                        Hành động này không thể hoàn tác
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg text-sm mb-5">
                Bạn có chắc muốn duyệt đơn
                <span class="font-semibold">{{
                    pendingApproveItem?.code
                }}</span>
                không?
            </div>

            <div class="flex justify-end gap-3">
                <button
                    @click="showConfirm = false"
                    class="px-4 py-2 rounded-lg border hover:bg-gray-100"
                >
                    Hủy
                </button>
                <button
                    @click="confirmApprove"
                    class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white"
                >
                    ✔ Duyệt đơn
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, ref, onMounted, computed } from "vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import SaleOrderForm from "./SaleOrderForm.vue"; // ← Đúng tên
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import SearchPage from "@/components/SearchPage.vue";
import { usePermission } from "../../../composables/usePermission.js";
import { formatMoney } from "@/config/helpers";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import SaleOrderDetail from "./SaleOrderDetail.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import CheckIcon from "@/icons/CheckIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
const showSaleDetailModal = ref(false);
const detailOrder = ref(null);
const { can } = usePermission();
const filters = [
    { name: "search", type: "text", placeholder: "Mã đơn / Tên khách hàng" },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            { value: "pending", label: "Chờ xử lý" },
            { value: "approved", label: "Đã duyệt" },
            { value: "completed", label: "Đã duyệt" },
            { value: "partial", label: "Đã duyệt" },
            { value: "cancelled", label: "Đã hủy" },
        ],
    },
];

const statusConfig = {
    pending: { text: "Chờ xử lý", class: "bg-yellow-100 text-yellow-700" },
    approved: { text: "Đã duyệt", class: "bg-blue-100 text-blue-700" },
    completed: { text: "Đã duyệt", class: "bg-blue-100 text-blue-700" },
    partial: { text: "Đã duyệt", class: "bg-blue-100 text-blue-700" },
    cancelled: { text: "Đã hủy", class: "bg-red-100 text-red-700" },
};

// Data
const orders = ref({ data: [], current_page: 1, per_page: 10, total: 0 });
const customers = ref([]);
const products = ref([]);
const currencies = ref([]);
const perPage = ref(10);
const search = ref("");
const statusFilter = ref("");
const showModal = ref(false);
const selectedOrder = ref(null);
const orderKey = ref(0); // Dùng để force re-render form khi cần

const showConfirm = ref(false);
const pendingApproveItem = ref(null);

// Columns
const columns = [
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
            const status = statusConfig[row?.status];

            return h(
                "span",
                {
                    class: `${status?.class || "bg-gray-100 text-gray-600"} px-3 py-1 rounded-full text-xs font-medium`,
                },
                status?.text || "Không rõ",
            );
        },
    },
];

// Actions
const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        title: "Chỉnh sửa",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        onClick: (item) => {
            if (item.status === "approved") return;
            openEdit(item);
        },
        hidden: (item) =>
            !can("sale_order.update") ||
            HIDDEN_EDIT_STATUSES.includes(item.status),
    },
    {
        icon: CheckIcon,
        type: "approve",
        title: "Duyệt đơn",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        onClick: (item) => {
            if (item.status === "approved") return;
            openApproveConfirm(item);
        },
        // gộp luôn điều kiện "cancelled" vào đây vì action.visible không được DataTable đọc
        hidden: (item) =>
            !can("sale_order.approve") ||
            item.status === "cancelled" ||
            HIDDEN_EDIT_STATUSES.includes(item.status),
    },
    {
        icon: DeleteIcon,
        title: "Hủy đơn",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        // TODM: cần xác nhận lại hàm xử lý thật sự (hiện đang gọi nhầm showDetail đã bị comment)
        onClick: (item) => cancelOrder(item),
        hidden: (item) =>
            !can("sale_order.cancel") ||
            HIDDEN_EDIT_STATUSES.includes(item.status),
    },
    {
        icon: DetailButtonIcon,
        title: "Chi tiết",
        onClick: (item) => openDetail(item), // sửa từ showDetail -> openDetail
        hidden: () => !can("sale_order.detail"),
    },
];
const HIDDEN_EDIT_STATUSES = ["approved", "completed","partial", "cancelled"];
const LOCKED_STATUSES = ["approved", "partial", "completed"];

function isLocked(row) {
    return LOCKED_STATUSES.includes(row.status);
}
// Filter
function handleFilter(params) {
    search.value = params.search || "";
    statusFilter.value = params.status || "";
    getData(1);
}
async function openDetail(item) {
    try {
        const res = await axios.get(`/api/sale/orders/${item.id}`);

        detailOrder.value = res.data.data ?? res.data;

        showSaleDetailModal.value = true;
    } catch (error) {
        toast.error("Không tải được thông tin đơn hàng");
    }
}
// API Calls
async function getData(page = 1) {
    const res = await axios.get("/api/sale/orders", {
        params: {
            page,
            per_page: perPage.value,
            search: search.value,
            status: statusFilter.value,
        },
    });
    orders.value = res.data;
}

const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};

async function fetchCustomers() {
    const res = await axios.get("/api/sale/customers/all");
    customers.value = res.data.data ?? res.data;
}

async function fetchProducts() {
    const res = await axios.get("/api/warehouse/products");
    products.value = res.data.data ?? [];
}

async function fetchCurrencies() {
    const res = await axios.get("/api/accountant/currencies");
    currencies.value = res.data.data ?? res.data;
}

// Modal
function openCreate() {
    selectedOrder.value = null;
    orderKey.value++;
    showModal.value = true;
}

async function openEdit(item) {
    if (item.status !== "pending") {
        toast.warning(
            "Đơn này không thể sửa vì đã được duyệt hoặc hoàn thành.",
        );
        return;
    }
    try {
        // Fetch chi tiết đầy đủ để có note, currency_id gốc, expected_delivery_date
        const res = await axios.get(`/api/sale/orders/${item.id}`);
        const fullOrder = res.data.data ?? res.data;
        selectedOrder.value = { ...fullOrder };
    } catch {
        // Fallback dùng data từ list nếu API lỗi
        selectedOrder.value = { ...item };
    }
    orderKey.value++;
    showModal.value = true;
}

function openApproveConfirm(item) {
    pendingApproveItem.value = item;
    showConfirm.value = true;
}
// function openDetail() {}
async function confirmApprove() {
    if (!pendingApproveItem.value) return;

    try {
        await axios.post(
            `/api/sale/orders/${pendingApproveItem.value.id}/approve`,
        );

        toast.success("Duyệt đơn bán hàng thành công!");
        showConfirm.value = false;
        pendingApproveItem.value = null;
        getData();
    } catch (err) {
        toast.error("Duyệt đơn thất bại");
        console.error(err);
    }
}

function reloadData() {
    showModal.value = false;
    getData();
}

function handlePageChange(page) {
    getData(page);
}
const provinces = ref([]);
const wards = ref([]);

async function fetchProvinces() {
    const res = await axios.get("/api/provinces");
    provinces.value = res.data;
}

onMounted(() => {
    getData();
    fetchCustomers();
    fetchProducts();
    fetchCurrencies();
    fetchProvinces();
});
</script>
