<template>
    <Head title="Kho hàng" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Kho hàng', link: null }]" />

        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách kho hàng</h2>

            <button
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
                + Kho
            </button>
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
import { ref, onMounted, h } from "vue";
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
const handlePageChange = (page) => {
    getData(page);
};
const perPage = ref(10);
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
const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        onClick: (item) => openEdit(item),
    },
    {
        icon: (item) => (item.status === "active" ? Lock : Unlock),
        type: "status",
        onClick: (item) => toggleStatus(item),
    },
    {
        title: "Chi tiết",
        icon: DetailButtonIcon,
        onClick: openDetail,
    },
];

function openCreate() {
    selectedWarehouse.value = null;
    showModal.value = true;
}

function openEdit(warehouse) {
    console.log(warehouse.id); // debug đúng

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
const fetchData = async (page = 1) => {
    try {
        const response = await axios.get("/api/warehouses", {
            params: {
                page,
                per_page: perPage.value,
            },
        });

        warehouses.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

const getData = (page = 1) => {
    fetchData(page);
};
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};
const reloadData = () => {
    getData(warehouses.value.current_page);
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
