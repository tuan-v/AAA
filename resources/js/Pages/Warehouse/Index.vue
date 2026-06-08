<template>
    <Head title="Kho hàng" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Warehouse', link: null }]" />

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
            :data="warehouses"
            :showIndex="true"
            :actions="actions"
            emptyMessage="Không có kho hàng"
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
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import WarehouseForm from "./WarehouseForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import { get } from "lodash";

const warehouses = ref([]);

const selectedWarehouse = ref(null);
const showModal = ref(false);

const columns = [
    {
        label: "Tên",
        key: "name",
    },
    {
        label: "Địa chỉ",
        key: "address",
    },
    {
        label: "Giá trị tồn",
        key: "total_inventory_value",
    },
    {
        key: "status_text",
        label: "Trạng thái",
    },
];
console.log(warehouses.value[0]);
const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        onClick: (item) => openEdit(item),
    },
    {
        icon: (item) => (item.status ? Lock : Unlock),
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

function getData() {
    axios
        .get("/api/warehouses")
        .then((response) => {
            warehouses.value = response.data;
        })
        .catch((error) => {
            console.error(error);
        });
}

async function toggleStatus(item) {
    const newStatus = item.status ? 0 : 1;

    const confirmMsg = newStatus
        ? "Bạn muốn mở khóa kho này?"
        : "Bạn muốn khóa kho này?";

    if (!confirm(confirmMsg)) return;

    try {
        await axios.patch(`/api/warehouses/${item.id}/status`, {
            status: newStatus,
        });

        // update UI ngay (không reload)
        const index = warehouses.value.findIndex((w) => w.id === item.id);
        if (index !== -1) {
            warehouses.value[index] = {
                ...warehouses.value[index],
                status: newStatus,
                status_text: newStatus ? "Hoạt động" : "Đã khóa",
            };
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
