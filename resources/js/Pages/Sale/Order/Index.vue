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
                v-if="can('don_ban.them')"
                @click="openCreate"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
            >
                + Đơn bán
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
                @duplicate="openDuplicate"
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
import { formatMoney, formatQuantity } from "@/config/helpers";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import SaleOrderDetail from "./SaleOrderDetail.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import CheckIcon from "@/icons/CheckIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import XIcon from "@/icons/XIcon.vue";
import { useActionConfirm } from "@/composables/useActionConfirm";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";
import { cloneSalesOrderForCreate } from "@/config/orderHelpers";
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
            { value: "pending", label: "Chờ xác nhận" },
            { value: "approved", label: "Đã xác nhận" },
            { value: "partial", label: "Đang giao hàng" },
            { value: "completed", label: "Hoàn thành" },
            { value: "cancelled", label: "Đã hủy" },
        ],
    },
];

const statusConfig = {
    pending: { text: "Chờ xác nhận", class: "bg-yellow-100 text-yellow-700" },
    approved: { text: "Đã xác nhận", class: "bg-blue-100 text-blue-700" },
    partial: { text: "Đang giao hàng", class: "bg-purple-100 text-purple-700" },
    completed: { text: "Hoàn thành", class: "bg-green-100 text-green-700" },
    cancelled: { text: "Đã hủy", class: "bg-red-100 text-red-700" },
};

const codStatusConfig = {
    pending: { text: "Chờ giao", class: "bg-yellow-100 text-yellow-700" },
    shipping: { text: "Đang giao", class: "bg-blue-100 text-blue-700" },
    collected: { text: "Chờ đối soát", class: "bg-orange-100 text-orange-700" },
    reconciled: { text: "Đã đối soát", class: "bg-green-100 text-green-700" },
    failed: { text: "Giao thất bại", class: "bg-red-100 text-red-700" },
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

function formatOrderQuantity(row) {
    const quantitiesByUnit = new Map();

    for (const item of row.items || []) {
        const unit =
            item.product?.unit?.symbol || item.product?.unit?.name || "";
        quantitiesByUnit.set(
            unit,
            (quantitiesByUnit.get(unit) || 0) + Number(item.quantity || 0),
        );
    }

    return [...quantitiesByUnit.entries()]
        .map(
            ([unit, quantity]) =>
                `${formatQuantity(quantity)}${unit ? ` ${unit}` : ""}`,
        )
        .join(", ");
}

// Columns
const columns = [
    { key: "code", label: "Mã đơn" },
    {
        label: "Nguồn đơn",
        render: (row) => h("span", {
            class: row.sales_channel === "storefront"
                ? "rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700"
                : "rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600",
        }, row.sales_channel === "storefront" ? "Website" : row.sales_channel === "pos" ? "Tại quầy" : "Nội bộ"),
    },
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
        label: "Vận chuyển",
        render: (row) => h("div", { class: "min-w-40" }, [
            h("div", { class: row.shipping_partner ? "font-semibold text-gray-800" : "text-gray-400" },
                row.shipping_partner?.name || "Chưa gán đơn vị"),
            h("div", { class: row.tracking_code ? "mt-1 font-mono text-xs text-blue-600" : "mt-1 text-xs text-gray-400" },
                row.tracking_code ? `Mã: ${row.tracking_code}` : "Chưa có mã vận đơn"),
        ]),
    },
    {
        label: "COD",
        align: "text-right",
        render: (row) => row.payment_method === "cod"
            ? h("div", { class: "min-w-28" }, [
                h("div", { class: "font-semibold text-gray-800" }, `${formatMoney(row.cod_amount)} ${row.currency?.symbol ?? ""}`),
                h("span", { class: `mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold ${codStatusConfig[row.cod_status]?.class || "bg-gray-100 text-gray-600"}` },
                    codStatusConfig[row.cod_status]?.text || "Chờ xử lý"),
            ])
            : h("span", { class: "text-gray-400" }, "Không COD"),
    },
    {
        label: "SL SP",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                { class: "font-semibold text-blue-600" },
                formatOrderQuantity(row),
            ),
    },
    {
        label: "Tổng tiền",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                { class: "font-semibold text-green-600" },
                `${formatMoney(row.total_amount)} ${row.currency?.symbol ?? ""}`,
            ),
    },

    {
        label: "Trạng thái",
        align: "text-center",
        render: (row) => {
            const status = ({
                returned: { text: "Đã hoàn / Hủy giao", class: "bg-orange-100 text-orange-700" },
                return_pending_warehouse: { text: "Chờ kho nhận hàng hoàn", class: "bg-orange-100 text-orange-700" },
                return_pending_accountant: { text: "Chờ kế toán duyệt hoàn", class: "bg-orange-100 text-orange-700" },
            })[row?.effective_status] || statusConfig[row?.status];

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
            !can("don_ban.sua") || HIDDEN_EDIT_STATUSES.includes(item.status),
    },
    {
        icon: CheckIcon,
        type: "approve",
        confirm: false,
        title: "Xác nhận đơn",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        onClick: (item) => {
            if (item.status !== "pending") return;
            openApproveConfirm(item);
        },
        // gộp luôn điều kiện "cancelled" vào đây vì action.visible không được DataTable đọc
        hidden: (item) =>
            !can("don_ban.duyet") ||
            item.sales_channel === "pos" ||
            item.status !== "pending",
    },
    {
        icon: XIcon,
        title: "Hủy đơn",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        // TODM: cần xác nhận lại hàm xử lý thật sự (hiện đang gọi nhầm showDetail đã bị comment)
        confirm: false,
        onClick: (item) => cancelOrder(item),
        hidden: (item) =>
            !can("don_ban.huy") || item.status !== "pending",
    },
    {
        icon: DetailButtonIcon,
        title: "Chi tiết",
        onClick: (item) => openDetail(item), // sửa từ showDetail -> openDetail
        hidden: () => !can("don_ban.xem_chi_tiet"),
    },
];
const HIDDEN_EDIT_STATUSES = ["approved", "completed", "partial", "cancelled"];
const LOCKED_STATUSES = ["approved", "partial", "completed"];
const { confirmAction } = useActionConfirm();

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
    const res = await axios.get("/api/products/for-select");
    products.value = res.data ?? [];
}

async function fetchCurrencies() {
    const res = await axios.get("/api/currencies/for-select", {
        params: { scope: "all" },
    });
    currencies.value = res.data?.data ?? res.data ?? [];
}

// Modal
function openCreate() {
    selectedOrder.value = null;
    orderKey.value++;
    showModal.value = true;
}

function openDuplicate(order) {
    const template = cloneSalesOrderForCreate(order);
    if (!template) return;

    showSaleDetailModal.value = false;
    selectedOrder.value = template;
    orderKey.value++;
    showModal.value = true;
}

async function openEdit(item) {
    if (item.status !== "pending") {
        toast.warning("Chỉ được chỉnh sửa đơn bán đang chờ xác nhận.");
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
async function cancelOrder(item) {
    const reason = await confirmAction({
        title: "Xác nhận hủy đơn bán",
        message: `Lý do hủy sẽ được hiển thị cho khách hàng trong tài khoản website.`,
        confirmText: "Hủy đơn",
        tone: "danger",
        inputLabel: "Lý do hủy đơn",
        inputPlaceholder: "Ví dụ: Sản phẩm tạm hết hàng, không thể giao đúng hẹn...",
        inputRequired: true,
        inputMinLength: 5,
    });
    if (!reason) return;

    try {
        await axios.post(`/api/sale/orders/${item.id}/cancel`, { reason });
        toast.success("Hủy đơn bán thành công");
        getData();
    } catch (error) {
        toast.error(error.response?.data?.message || "Không thể hủy đơn bán");
    }
}
// function openDetail() {}
async function confirmApprove() {
    if (!pendingApproveItem.value) return;

    try {
        await axios.post(
            `/api/sale/orders/${pendingApproveItem.value.id}/approve`,
        );

        toast.success("Xác nhận đơn bán hàng thành công!");
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

async function refreshRealtimeData() {
    await getData(orders.value.current_page || 1);

    if (showSaleDetailModal.value && detailOrder.value?.id) {
        try {
            const res = await axios.get(
                `/api/sale/orders/${detailOrder.value.id}`,
            );
            detailOrder.value = res.data.data ?? res.data;
        } catch (error) {
            if (error.response?.status === 404) {
                showSaleDetailModal.value = false;
                detailOrder.value = null;
            }
        }
    }
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

useRealtimeRefresh(refreshRealtimeData);

onMounted(() => {
    getData();
    fetchCustomers();
    fetchProducts();
    fetchCurrencies();
    fetchProvinces();
});
</script>
