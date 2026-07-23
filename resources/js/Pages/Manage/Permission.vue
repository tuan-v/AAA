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
                v-if="can('quyen.them')"
                class="bg-blue-500 text-white px-4 py-2 rounded"
                @click="openCreate"
            >
                + Thêm quyền
            </button>
        </div>
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6"
        >
            <SearchPage :filters="filters" @filter="handleFilter" />
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
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import Pagination from "@/components/Pagination.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import PermissionForm from "./PermissionForm.vue";
import { Head, usePage } from "@inertiajs/vue3";
import DeleteIcon from "../../icons/DeleteIcon.vue";
import SearchPage from "@/components/SearchPage.vue";
import { toast } from "vue3-toastify";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Tìm theo tên quyền, nhóm hoặc mô tả...",
    },
    {
        name: "group",
        type: "text",
        placeholder: "Lọc theo nhóm (ví dụ: user, role, permission...)",
    },
];
const filterParams = ref({});
const handleFilter = (params) => {
    filterParams.value = params;
    getData(1);
};
const page = ref(1);
const perPage = ref(50);
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
    per_page: 50,
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
        title: "Chỉnh sửa",
        icon: EditButtonIcon,
        hidden: () => !can("quyen.sua"),
        onClick: openEdit,
    },
    {
        title: "Xóa",
        icon: DeleteIcon,
        class: "bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700",
        hidden: () => !can("quyen.xoa"),
        onClick: async (item) => {
            if (!confirm(`Bạn có chắc muốn xóa quyền "${item.name}"?`)) {
                return;
            }

            try {
                await axios.delete(`/api/permissions/${item.id}`);

                reloadData();
            } catch (e) {
                console.error(e);

                toast.error(
                    e.response?.data?.message ??
                        "Không thể xóa quyền.",
                );
            }
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
                ...filterParams.value, // ← Thêm dòng này
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

useRealtimeRefresh(reloadData);

onMounted(() => {
    reloadData();
});
</script>
