<template>
    <Head title="Đơn mua hàng" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[
                {
                    text: 'Đơn mua hàng',
                    link: null,
                },
            ]"
        />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách đơn mua hàng</h2>

            <button
                @click="openCreate"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
            >
                + Đơn mua
            </button>
        </div>
        <div class="grid grid-cols-4 gap-4 mb-5">
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <p class="text-gray-500 text-sm">Tổng đơn</p>
                <p class="text-2xl font-bold">
                    {{ orders.total }}
                </p>
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
                            ["approved", "partial", "completed"].includes(
                                x.status,
                            ),
                        ).length
                    }}
                </p>
            </div>

            <!-- <div class="bg-green-50 p-4 rounded-xl border">
                <p class="text-green-600 text-sm">Hoàn thành</p>
                <p class="text-2xl font-bold">
                    {{
                        orders.data.filter((x) => x.status === "completed")
                            .length
                    }}
                </p>
            </div> -->
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
    <div
        v-if="showConfirm"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div class="bg-white w-[420px] rounded-xl shadow-xl p-6 animate-fadeIn">
            <!-- ICON -->
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

            <!-- CONTENT -->
            <div class="bg-gray-50 p-3 rounded-lg text-sm mb-5">
                Bạn có chắc muốn duyệt đơn:
                <span class="font-semibold">
                    {{ pendingApproveItem?.code }}
                </span>
                không?
            </div>

            <!-- ACTIONS -->
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
import { h, ref, onMounted, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import PurchaseOrderForm from "./PurchaseOrderForm.vue";
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
import DeleteIcon from "../../../icons/DeleteIcon.vue";
import PurchaseOrderDetail from "./PurchaseOrderDetail.vue";
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
                value: "pending",
                label: "Chờ xử lý",
            },
            {
                value: "approved",
                label: "Đã duyệt",
            },
            {
                value: "partial",
                label: "Đã duyệt",
            },
            {
                value: "completed",
                label: "Đã duyệt",
            },
            {
                value: "cancelled",
                label: "Đã hủy",
            },
        ],
    },
];
const statusConfig = {
    pending: {
        text: "Chờ xử lý",
        class: "bg-yellow-100 text-yellow-700",
    },

    approved: {
        text: "Đã duyệt",
        class: "bg-blue-100 text-blue-700",
    },

    partial: {
        text: "Đã duyệt",
        class: "bg-blue-100 text-blue-700",
    },

    completed: {
        text: "Đã duyệt",
        class: "bg-blue-100 text-blue-700",
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
const purchaseDetailOrder = ref(null);
const showPurchaseDetailModal = ref(false);
const search = ref("");
const statusFilter = ref("");

const showModal = ref(false);

const selectedOrder = ref(null);

const columns = [
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
            const status = statusConfig[row.status] ?? statusConfig.pending;

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
        icon: EditButtonIcon,
        type: "edit",
        title: "Chỉnh sửa",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        onClick: (item) => {
            if (item.status === "approved") return;
            openEdit(item);
        },
        hidden: (item) => HIDDEN_EDIT_STATUSES.includes(item.status),
    },
    {
        icon: CheckIcon,
        type: "approve",
        title: "Duyệt đơn",
        visible: (row) => row.status !== "cancelled",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        onClick: (item) => {
            if (item.status === "approved") return;
            openApproveConfirm(item);
        },
        hidden: (item) => HIDDEN_EDIT_STATUSES.includes(item.status),
    },

    {
        icon: DeleteIcon,
        title: "Hủy đơn",
        disabled: (row) => isLocked(row),
        class: (row) => (isLocked(row) ? "opacity-40 cursor-not-allowed" : ""),
        onClick: (item) => showDetail(item),
        hidden: (item) => HIDDEN_EDIT_STATUSES.includes(item.status),
    },
    {
        icon: DetailButtonIcon,
        title: "Chi tiết",
        onClick: openPurchaseDetail,
    },
];
const HIDDEN_EDIT_STATUSES = ["approved", "partial", "completed"];
const showConfirm = ref(false);
const pendingApproveItem = ref(null);
function openApproveConfirm(item) {
    pendingApproveItem.value = item;
    showConfirm.value = true;
}
async function confirmApprove() {
    if (!pendingApproveItem.value) return;

    const item = pendingApproveItem.value;

    try {
        await axios.post(`/api/purchase/orders/${item.id}/approve`);

        item.status = "approved";

        toast.success("Duyệt đơn thành công", {
            position: "top-right",
            autoClose: 3000,
            theme: "colored",
        });

        showConfirm.value = false;
        pendingApproveItem.value = null;

        getData(); // reload lại danh sách
    } catch (err) {
        toast.error("Duyệt đơn thất bại");
        console.error(err);
    }
}
async function getData(page = 1) {
    const res = await axios.get("/api/purchase/orders", {
        params: {
            page,
            search: search.value,
            status: statusFilter.value,
        },
    });

    orders.value = res.data;
}
const LOCKED_STATUSES = ["approved", "partial", "completed"];

function isLocked(row) {
    return LOCKED_STATUSES.includes(row.status);
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
    const res = await axios.get("/api/accountant/currencies");

    currencies.value = res.data.data ?? res.data;
}

function openCreate() {
    selectedOrder.value = null;
    showModal.value = true;
}

function openEdit(item) {
    if (item.status === "approved") {
        toast.warning("Đơn này đã được duyệt rồi, không thể sửa được.", {
            position: "top-right",
            timeout: 3000,
        });
        return;
    }
    selectedOrder.value = item;
    showModal.value = true;
}
async function openPurchaseDetail(item) {
    try {
        const res = await axios.get(`/api/purchase/orders/${item.id}`);

        purchaseDetailOrder.value = res.data;

        showPurchaseDetailModal.value = true;
    } catch (err) {
        toast.error("Không tải được chi tiết đơn mua");
    }
}
// function showDetail(item) {
//     window.location.href = `/purchase/orders/${item.id}`;
// }

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
