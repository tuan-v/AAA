<template>
    <head title="Quản lý vai trò" />
    <AdminLayout>
        <PageBreadcrumb
            title="Vai trò"
            :items="[{ text: 'Vai trò', link: null }]"
        />
        <div class="flex justify-between mb-5">
            <h2 class="text-2xl font-bold">Danh sách vai trò</h2>

            <button
                v-if="can('role.create')"
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded"
            >
                + Thêm vai trò
            </button>
        </div>

        <DataTable
            :columns="columns"
            :data="roles.data"
            :actions="actions"
            :showIndex="true"
        />
        <Pagination
            :totalItems="roles.total"
            :itemsPerPage="roles.per_page"
            :currentPage="roles.current_page"
            :doingShow="roles.data.length"
            @page-change="handlePageChange"
        />

        <Modal v-if="showModal" @close="showModal = false">
            <template #body>
                <RoleForm
                    :role="selectedRole"
                    @saved="reloadData"
                    @close="showModal = false"
                />
            </template>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Pagination from "@/components/Pagination.vue";
import RoleForm from "./RoleForm.vue";
import { icons } from "lucide-vue-next";

const handlePageChange = (page) => {
    getData(page);
};
const roles = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});
const permissions = usePage().props.auth.permissions;
const can = (permission) => {
    return permissions.includes(permission);
};
const form = ref({});
const showModal = ref(false);

const selectedRole = ref(null);

const columns = [
    {
        key: "name",
        label: "Tên vai trò",
    },
];

const actions = [
    {
        icon: EditButtonIcon,
        onClick: (item) => {
            selectedRole.value = item;
            showModal.value = true;
        },
    },
];

function openCreate() {
    selectedRole.value = null;

    showModal.value = true;
}

const reloadData = () => {
    getData();
    showModal.value = false;
};
const getData = async (page = 1) => {
    const response = await axios.get(`/api/roles?page=${page}`);

    roles.value = response.data;
};

async function deleteRole(id) {
    await axios.delete(`/api/roles/${id}`);

    reloadData();
}

onMounted(() => {
    getData();
});
</script>
