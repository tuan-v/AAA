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
                +
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
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <WarehouseForm
                :warehouse="selectedWarehouse"
                @saved="getData"
                @close="showModal = false"
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

const handlePageChange = (page) => {
    getData(page);
};
const form = ref({});
const warehouses = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const selectedWarehouse = ref(null);
const showModal = ref(false);

const columns = [
    {
        label: "Tên",
        key: "name",
        align: "text-start",
    },
    {
        label: "Địa chỉ",
        key: "address",
        align: "text-start",
    },
    {
        label: "Giá trị tồn",
        key: "total_inventory_value",
        align: "text-right",
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
];

function openCreate() {
    selectedWarehouse.value = null;
    showModal.value = true;
}

function openEdit(warehouse) {
    selectedWarehouse.value = { ...warehouse }; // 🔥 clone
    showModal.value = true;
}

const getData = async (page = 1) => {
    const response = await axios.get(`/api/warehouses?page=${page}`);
    warehouses.value = response.data;
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
