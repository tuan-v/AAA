<template>
    <Head title="Kho hàng" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Kho hàng', link: null }]" />

        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách kho hàng</h2>

            <button
                v-if="can('kho.them')"
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
                + Kho
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5">
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <DataTable
            :columns="columns"
            :data="warehouses.data"
            :showIndex="true"
            :actions="actions"
            @toggle-status="toggleStatus"
            emptyMessage="Không có kho hàng"
        />
        <Pagination
            :totalItems="warehouses.total"
            :itemsPerPage="warehouses.per_page"
            :currentPage="warehouses.current_page"
            :doingShow="warehouses.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <WarehouseForm
                :warehouse="selectedWarehouse"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
    <Modal v-if="showDetailModal" @close="showDetailModal = false">
        <template #body>
            <WarehouseDetail
                :warehouse-id="selectedWarehouseId"
                @close="showDetailModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { ref, onMounted, h, computed } from "vue";
import axios from "axios";
import Pagination from "@/components/Pagination.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import WarehouseForm from "./WarehouseForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import { get } from "lodash";
import { formatMoney } from "@/config/helpers";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import WarehouseDetail from "./WarehouseDetail.vue";
import { usePermission } from "@/composables/usePermission";
import SearchPage from "@/components/SearchPage.vue";

const { can } = usePermission();
const handlePageChange = (page) => {
    getData(page, currentFilters.value);
};
const perPage = ref(10);
const currentFilters = ref({});
const form = ref({});
const warehouses = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const selectedWarehouse = ref(null);
const selectedWarehouseId = ref(null);
const showDetailModal = ref(false);
const showModal = ref(false);

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Tìm tên hoặc mã kho...",
    },
    {
        name: "min_inventory_value",
        type: "number",
        min: 0,
        step: 1000,
        placeholder: "Giá trị tồn từ...",
    },
    {
        name: "max_inventory_value",
        type: "number",
        min: 0,
        step: 1000,
        placeholder: "Giá trị tồn đến...",
    },
];

const columns = [
    {
        label: "Tên",
        key: "name",
        align: "text-start",
    },
    {
        label: "Địa chỉ",
        key: "full_address",
        align: "text-start",
    },
    {
        label: "Giá trị tồn",
        key: "total_inventory_value",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                { class: "font-medium" },
                `${formatMoney(row.total_inventory_value)} ${row.currency_symbol || ""}`,
            ),
    },
    {
        key: "status",
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
const actions = computed(() => [
    {
        icon: EditButtonIcon,
        type: "edit",
        hidden: () => !can("kho.sua"),
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
                ? !can("kho.khoa")
                : !can("kho.khoa"),
        onClick: (item) => toggleStatus(item),
    },
    {
        icon: DetailButtonIcon,
        type: "view",
        hidden: () => !can("kho.xem"),
        onClick: (item) => openDetail(item),
        tooltip: "Xem chi tiết",
    },
]);

function openCreate() {
    selectedWarehouse.value = null;
    showModal.value = true;
}

function openEdit(warehouse) {

    selectedWarehouse.value = { ...warehouse };

    form.id = warehouse.id; // 🔥 BẮT BUỘC PHẢI CÓ

    showModal.value = true;
}

function openDetail(warehouse) {
    const id = warehouse.id || warehouse.warehouse_id;

    if (!id) {
        alert("Không tìm thấy mã kho!");
        return;
    }

    selectedWarehouseId.value = id;
    showDetailModal.value = true;
}
const fetchData = async (page = 1, params = {}) => {
    try {
        const response = await axios.get("/api/warehouses", {
            params: {
                page,
                per_page: perPage.value,
                search: params.search || "",
                min_inventory_value: params.min_inventory_value || "",
                max_inventory_value: params.max_inventory_value || "",
            },
        });

        warehouses.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

const getData = (page = 1, params = {}) => {
    fetchData(page, params);
};
const handleFilter = (params) => {
    currentFilters.value = params;
    getData(1, params);
};
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1, currentFilters.value);
};
const reloadData = () => {
    getData(warehouses.value.current_page, currentFilters.value);
    showModal.value = false;
};
async function toggleStatus(warehouse) {
    const id = warehouse.id || warehouse.warehouse_id;
    const newStatus = warehouse.status === "active" ? "inactive" : "active";

    const confirmMsg = newStatus
        ? "Bạn muốn khóa kho này?"
        : "Bạn muốn mở khóa kho này?";

    if (!confirm(confirmMsg)) return;

    try {
        await axios.patch(`/api/warehouses/${warehouse.id}/status`, {
            status: newStatus,
        });

        // update UI ngay (không reload)
        const index = warehouses.value.data.findIndex((w) => w.id === id);
        if (index !== -1) {
            warehouses.value.data[index].status = newStatus;
        }
    } catch (error) {
        console.error(error);
        alert("Cập nhật thất bại");
    }
}

onMounted(() => {
    getData();
});
</script>
