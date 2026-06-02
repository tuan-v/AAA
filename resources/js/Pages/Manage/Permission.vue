<template>
    <AdminLayout>
        <div class="flex justify-between mb-5">
            <h2 class="text-2xl font-bold">Quản lý quyền</h2>

            <button
                class="bg-blue-500 text-white px-4 py-2 rounded"
                @click="openCreate"
            >
                + Thêm quyền
            </button>
        </div>

        <DataTable
            :columns="columns"
            :data="permissions.data"
            :actions="actions"
            :showIndex="true"
        />
        <Pagination
            :totalItems="permissions.total"
            :itemsPerPage="permissions.per_page"
            :currentPage="permissions.current_page"
            :doingShow="permissions.data.length"
            @page-change="handlePageChange"
        />
        <Modal v-if="showModal" @close="showModal = false">
            <template #body>
                <PermissionForm
                    :permission="selectedPermission"
                    @saved="reloadData"
                    @close="showModal = false"
                />
            </template>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import PermissionForm from "./PermissionForm.vue";

const handlePageChange = (page) => {
    getData(page);
};

const permissions = ref({
    data: [],
});

const selectedPermission = ref(null);

const showModal = ref(false);

const columns = [
    {
        key: "name",
        label: "Permission",
    },
    {
        key: "group",
        label: "Nhóm",
    },
];

const actions = [
    {
        icon: EditButtonIcon,
        onClick: (item) => {
            selectedPermission.value = item;
            showModal.value = true;
        },
    },
    {
        label: "Xóa",
        onClick: (item) => {
            deletePermission(item.id);
        },
    },
];

function openCreate() {
    selectedPermission.value = null;
    showModal.value = true;
}
const reloadData = () => {
    getData();
    showModal.value = false;
};
const getData = async (page = 1) => {
    const res = await axios.get(`/api/permissions?page=${page}`);

    permissions.value = res.data;
};

async function deletePermission(id) {
    await axios.delete(`/api/permissions/${id}`);

    reloadData();
}

onMounted(() => {
    reloadData();
});
</script>
