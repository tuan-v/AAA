<template>
    <Head title="Phiếu" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Phiếu', link: null }]" />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách phiếu</h2>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5">
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>
        <div class="flex items-center border-b mb-5 gap-2">
            <!-- IMPORT TAB -->
            <button
                @click="activeTab = 'import'"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    activeTab === 'import'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Phiếu nhập
            </button>

            <!-- EXPORT TAB -->
            <button
                @click="activeTab = 'export'"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    activeTab === 'export'
                        ? 'border-red-500 text-red-600'
                        : 'border-transparent text-gray-500 hover:text-red-500'
                "
            >
                Phiếu xuất
            </button>
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="slips.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(slips.current_page - 1) * slips.per_page"
            emptyMessage="Không có phiếu nhập"
        >
        </DataTable>

        <!-- PAGINATION -->
        <Pagination
            :totalItems="slips.total"
            :itemsPerPage="slips.per_page"
            :currentPage="slips.current_page"
            :doingShow="slips.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <!-- MODAL -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <WarehouseImportForm
                :order="selectedSlip"
                :warehouses="warehouses"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
    <Modal v-if="showDetailModal" @close="showDetailModal = false">
        <template #body>
            <SlipDetail
                :slipId="selectedSlip?.id"
                @close="showDetailModal = false"
            />
        </template>
    </Modal>
</template>
<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { ref, onMounted, h, watch } from "vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import CheckIcon from "../../../icons/CheckIcon.vue";
import DeleteIcon from "../../../icons/DeleteIcon.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import DetailButtonIcon from "../../../icons/DetailButtonIcon.vue";
import SlipDetail from "../../Warehouse/Slip/SlipDetail.vue";
import { usePermission } from "@/composables/usePermission";
import { useActionConfirm } from "@/composables/useActionConfirm";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";
import SearchPage from "@/components/SearchPage.vue";

const { can } = usePermission();
const { confirmAction } = useActionConfirm();
const activeTab = ref("import");
const urlParams = new URLSearchParams(window.location.search);
const currentFilters = ref({
    search: urlParams.get("search") || "",
    warehouse_id: urlParams.get("warehouse_id") || "",
});
const filters = ref([
    {
        name: "search",
        type: "text",
        placeholder: "Tìm mã phiếu...",
    },
    {
        name: "warehouse_id",
        type: "select",
        placeholder: "Tất cả kho",
        options: [],
    },
]);

const showDetailModal = ref(false);
const showModal = ref(false);
const selectedSlip = ref(null);
const products = ref([]);
const warehouses = ref([]);
const perPage = ref(10);
const slips = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const columns = [
    {
        label: "Mã phiếu",
        key: "code",
        align: "text-start",
    },
    {
        label: "Mã đơn",
        align: "text-start",
        render: (row) =>
            h(
                "span",
                {},
                row.purchase_order_code || row.sales_order_code || "-",
            ),
    },
    {
        label: "Mã kho",
        align: "text-start",
        render: (row) => h("span", {}, row.warehouse?.code ?? "-"),
    },
    {
        label: "Người tạo",
        render: (row) => h("span", {}, row.created_by?.name ?? "-"),
    },
    {
        label: "Người duyệt/từ chối",
        render: (row) => h("span", {}, row.approved_by?.name ?? "-"),
    },
    {
        label: "Kho nhập/xuất",
        align: "text-start",
        render: (row) => h("span", {}, row.warehouse?.name ?? "-"),
    },
    {
        label: "Số mặt hàng",
        key: "total_items",
        align: "text-right",
    },
    {
        label: "Ghi chú",
        key: "note",
        align: "text-start",
    },
    {
        label: "Ngày tạo",
        key: "created_at",
        align: "text-right",
    },
    {
        label: "Ngày duyệt/từ chối",
        align: "text-right",
        render: (row) => h("span", {}, row.approved_at ?? "-"),
    },
    {
        label: "Trạng thái",
        render: (row) => {
            const config = {
                pending: {
                    text: "Chờ duyệt",
                    class: "bg-yellow-100 text-yellow-700",
                },
                approved: {
                    text: "Đã duyệt",
                    class: "bg-green-100 text-green-700",
                },
                rejected: {
                    text: "Từ chối",
                    class: "bg-red-100 text-red-700",
                },
            };

            const status = config[row.status];

            return h(
                "span",
                {
                    class:
                        status.class +
                        " px-3 py-1 rounded-full text-xs font-medium",
                },
                status.text,
            );
        },
    },
];
const actions = [
    {
        title: "Duyệt phiếu",
        icon: CheckIcon,
        hidden: (row) =>
            !can("phieu_kho.duyet") || row.status !== "pending",
        confirm: false,
        onClick: async (row) => {
            const confirmed = await confirmAction({
                title: "Duyệt phiếu kho",
                message: `Xác nhận duyệt phiếu ${row.code || `#${row.id}`} và cập nhật số lượng tồn kho?`,
                confirmText: "Duyệt phiếu",
                tone: "success",
            });
            if (!confirmed) return;
            try {
                await axios.post(`/api/warehouse/slips/${row.id}/approve`);

                toast.success("Duyệt phiếu thành công");
                await getData(slips.value.current_page);
            } catch (e) {
                console.error(e);
                toast.error("Không thể duyệt phiếu", {
                    position: "top-right",
                    autoClose: 3000,
                    theme: "colored",
                });
            }
        },
    },
    {
        title: "Từ chối",
        icon: DeleteIcon,
        hidden: (row) =>
            !can("phieu_kho.tu_choi") || row.status !== "pending",
        confirm: false,
        onClick: async (row) => {
            const confirmed = await confirmAction({
                title: "Từ chối phiếu kho",
                message: `Bạn có chắc muốn từ chối phiếu ${row.code || `#${row.id}`}?`,
                confirmText: "Từ chối phiếu",
                tone: "danger",
            });
            if (!confirmed) return;
            try {
                await axios.post(`/api/warehouse/slips/${row.id}/reject`);

                toast.success("Từ chối phiếu thành công");
                await getData(slips.value.current_page);
            } catch (e) {
                console.error(e);
                toast.error("Không thể từ chối phiếu", {
                    position: "top-right",
                    autoClose: 3000,
                    theme: "colored",
                });
            }
        },
    },
    {
        title: "Chi tiết",
        icon: DetailButtonIcon,
        hidden: () => !can("phieu_kho.xem_chi_tiet"),
        onClick: openDetail,
    },
];
async function openDetail(row) {
    selectedSlip.value = row;
    showDetailModal.value = true;
}
function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

const fetchData = async (page = 1, params = currentFilters.value) => {
    const url =
        activeTab.value === "import"
            ? "/api/warehouse/slips?type=import"
            : "/api/warehouse/slips?type=export";

    const res = await axios.get(url, {
        params: {
            page,
            per_page: perPage.value,
            search: params.search || "",
            warehouse_id: params.warehouse_id || "",
            context: "approved_only",
        },
    });

    slips.value = res.data;
};
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};
watch(activeTab, () => {
    getData(1);
});
function goToExport() {
    window.location.href = "/warehouse/export";
}
const fetchWarehouses = async () => {
    try {
        const res = await axios.get("/api/warehouses/all");

        warehouses.value = res.data.data ?? res.data;
        filters.value[1].options = warehouses.value.map((warehouse) => ({
            value: warehouse.id,
            label: warehouse.name,
        }));
    } catch (err) {
        console.error(err);
    }
};
const getData = debounce(
    (page = 1, params = currentFilters.value) => fetchData(page, params),
    300,
);

function handleFilter(params) {
    currentFilters.value = params;
    getData(1, params);
}

function openCreate() {
    selectedSlip.value = null;
    showModal.value = true;
}

function openEdit(order) {
    selectedSlip.value = order;
    showModal.value = true;
}

function handlePageChange(page) {
    getData(page);
}

function reloadData() {
    getData(slips.value.current_page);
    showModal.value = false;
}

useRealtimeRefresh(reloadData);

onMounted(() => {
    getData();
    fetchWarehouses();
});
</script>
