<template>
    <Head title="Nhân sự" />

    <AdminLayout>
        <PageBreadcrumb
            title="Nhân sự"
            :items="[{ text: 'Nhân sự', link: null }]"
        />

        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách nhân sự</h2>

            <button
                v-if="can('user.create')"
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded"
            >
                + Thêm nhân sự
            </button>
        </div>

        <DataTable
            :columns="columns"
            :data="users.data"
            :showIndex="true"
            :actions="actions"
            @toggle-status="toggleStatus"
            :startIndex="(permissions.current_page - 1) * permissions.per_page"
            emptyMessage="Không có nhân sự"
        />
        <Pagination
            :totalItems="users.total"
            :itemsPerPage="users.per_page"
            :currentPage="users.current_page"
            :doingShow="users.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <UserForm
                :user="selectedUser"
                :company="company"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/components/Pagination.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import UserForm from "./UserForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import { ref, reactive, onMounted } from "vue";
import axios from "axios";

const handlePageChange = (page) => {
    getData(page);
};
const form = ref({});
const users = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});
const selectedUser = ref(null);
const showModal = ref(false);
const permissions = usePage().props.auth.permissions;
const can = (permission) => {
    return permissions.includes(permission);
};

const columns = [
    {
        key: "name",
        label: "Họ tên",
    },

    {
        key: "username",
        label: "Tên đăng nhập",
    },

    {
        key: "email",
        label: "Email",
    },

    {
        key: "phone",
        label: "SĐT",
    },
    {
        key: "roles",
        label: "Vai trò",
    },

    {
        key: "status",
        label: "Trạng thái",
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
        icon: DetailButtonIcon,
        onClick: (item) => {
            show(item.id);
        },
    },
];

function openCreate() {
    selectedUser.value = null;
    showModal.value = true;
}

function openEdit(user) {
    selectedUser.value = user;
    showModal.value = true;
}
const reloadData = () => {
    getData();
    showModal.value = false;
};

const getData = async (page = 1) => {
    const response = await axios.get(`/api/users/user?page=${page}`);
    users.value = response.data;
};

async function toggleStatus(user) {
    const id = user.id || user.user_id;
    const newStatus = user.status === "active" ? "blocked" : "active";

    const confirmMsg =
        newStatus === "blocked"
            ? "Bạn muốn khóa tài khoản này?"
            : "Bạn muốn mở khóa tài khoản này?";

    if (!confirm(confirmMsg)) return;

    try {
        await axios.patch(`/api/users/${id}/status`, {
            status: newStatus,
        });

        // update UI ngay không reload
        const index = users.value.data.findIndex((u) => u.id === id);
        if (index !== -1) {
            users.value.data[index].status = newStatus;
        }
    } catch (error) {
        console.log(error.response?.data || error);
        alert("Cập nhật thất bại");
    }
}
function show(id) {
    window.location.href = `/user/${id}`;
}

onMounted(() => {
    reloadData();
});
</script>
