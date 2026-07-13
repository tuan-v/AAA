<template>
    <head title="Quản lý quyền" />
    <AdminLayout>
        <PageBreadcrumb
            title="Quyền"
            :items="[{ text: 'Nhân sự', link: null }]"
        />
        <div class="flex justify-between mb-5">
            <h2 class="text-2xl font-bold">Danh sách quyền</h2>
            <button
                v-if="can('permission.create')"
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
            :startIndex="(permissions.current_page - 1) * permissions.per_page"
            emptyMessage="Không có quyền nào"
        />
        <Pagination
            :totalItems="permissions.total"
            :itemsPerPage="permissions.per_page"
            :currentPage="permissions.current_page"
            :doingShow="permissions.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
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
import Pagination from "@/components/Pagination.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import PermissionForm from "./PermissionForm.vue";
import { Head, usePage } from "@inertiajs/vue3";
import DeleteIcon from "../../icons/DeleteIcon.vue";
const page = ref(1);
const perPage = ref(10);
const handlePageChange = (page) => {
    getData(page);
};
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};
const permissions = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});
const permissionss = usePage().props.auth?.permissions || [];
const can = (permission) => {
    return permissionss.includes(permission);
};

const selectedPermission = ref(null);

const showModal = ref(false);

const columns = [
    {
        key: "name",
        label: "Tên quyền",
    },
    {
        key: "group",
        label: "Nhóm",
    },
    {
        key: "description",
        label: "Mô tả",
    },
];

const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        onClick: (item) => openEdit(item),
    },
    {
        icon: DeleteIcon,
        class: "bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700",
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
    getData(permissions.value.current_page || 1);
    showModal.value = false;
};
const getData = async (page = 1) => {
    try {
        const res = await axios.get("/api/permissions", {
            params: {
                page,
                per_page: perPage.value,
            },
        });

        permissions.value = res.data;
    } catch (error) {
        console.error(error);
    }
};

async function deletePermission(id) {
    await axios.delete(`/api/permissions/${id}`);

    reloadData();
}
function openEdit(permission) {
    selectedPermission.value = permission;
    showModal.value = true;
}

onMounted(() => {
    reloadData();
});
</script>
