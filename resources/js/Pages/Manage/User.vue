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
                @saved="getData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import UserForm from "./UserForm.vue";

import EditButtonIcon from "@/icons/EditButtonIcon.vue";

import { ref, reactive, onMounted } from "vue";
import axios from "axios";
import { icons } from "lucide-vue-next";

const permissions = usePage().props.auth.permissions;
console.log(permissions);
const handlePageChange = (page) => {
    getData(page);
};
const form = ref({});
const users = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
});
const selectedUser = ref(null);
const showModal = ref(false);

// const createUser = () => {
//     selectedUser.value = null;

//     showModal.value = true;
// };
// const editUser = (user) => {
//     selectedUser.value = user;

//     showModal.value = true;
// };
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
        key: "role_name",
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
        onClick: (item) => {
            console.log("Edit", item);
            openEdit(item);
        },
    },
    // {
    //     label: "Xóa",
    //     onClick: (item) => {
    //         deleteUser(item.id);
    //     },
    // },
];

function openCreate() {
    console.log("Click");
    selectedUser.value = null;
    showModal.value = true;
    console.log(showModal.value);
}

function openEdit(user) {
    selectedUser.value = user;
    showModal.value = true;
}

const getData = async (page = 1) => {
    const response = await axios.get(`/api/users/user?page=${page}`);
    users.value = response.data;
};

function deleteUser(id) {
    if (!confirm("Bạn có chắc muốn xóa?")) {
        return;
    }

    axios.delete(`/api/users/${id}`).then(() => {
        getData();
    });
}

onMounted(() => {
    getData();
});
</script>
