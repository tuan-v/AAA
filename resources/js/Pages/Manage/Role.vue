<template>
    <head title="Quản lý vai trò" />
    <AdminLayout>
        <PageBreadcrumb
            title="Vai trò"
            :items="[{ text: 'Vai trò', link: null }]"
        />
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                Danh sách vai trò
            </h2>

            <button
                v-if="can('role.create')"
                @click="openCreate"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow"
            >
                + Thêm vai trò
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div
                v-for="role in roles.data"
                :key="role.id"
                class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md transition p-5"
            >
                <!-- HEADER ROLE -->
                <div class="flex justify-between items-start mb-4">
                    <!-- ROLE BADGE -->
                    <div class="flex items-center gap-2">
                        <span
                            class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 border border-blue-200"
                        >
                            {{ role.name }}
                        </span>

                        <span class="text-xs text-gray-400">
                            #{{ role.id }}
                        </span>
                    </div>

                    <!-- ACTION -->
                    <button
                        v-if="can('role.update')"
                        @click="
                            selectedRole = role;
                            showModal = true;
                        "
                        class="p-2 rounded-md hover:bg-blue-50 text-blue-600 transition"
                        title="Chỉnh sửa"
                    >
                        <EditButtonIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- PERMISSIONS TITLE -->
                <div class="text-xs font-semibold text-gray-500 mb-2 uppercase">
                    Danh sách quyền
                </div>

                <!-- PERMISSIONS BADGE -->
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="permission in role.permissions"
                        :key="permission.id"
                        class="px-2 py-1 text-xs rounded-full font-medium border bg-purple-100 text-purple-700 border-purple-200 hover:bg-purple-200 transition"
                    >
                        {{ permission.name }}
                    </span>

                    <!-- empty state -->
                    <div
                        v-if="!roles.data.length"
                        class="text-center py-12 text-gray-500"
                    >
                        <div class="text-lg font-medium">
                            Không có vai trò nào
                        </div>
                        <div class="text-sm">
                            Hãy tạo vai trò mới để bắt đầu
                        </div>
                    </div>
                    <span
                        v-if="
                            !role.permissions || role.permissions.length === 0
                        "
                        class="text-xs text-gray-400 italic"
                    >
                        Không có quyền
                    </span>
                </div>
            </div>
        </div>
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

console.log(usePage().props.auth);
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
