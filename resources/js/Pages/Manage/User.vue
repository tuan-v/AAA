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
                v-if="can('nhan_su.them')"
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded"
            >
                + Thêm nhân sự
            </button>
        </div>
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6"
        >
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>
        <DataTable
            :columns="columns"
            :data="users.data"
            :showIndex="true"
            :actions="actions"
            @toggle-status="toggleStatus"
            :startIndex="(users.current_page - 1) * users.per_page"
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
    <Modal v-if="showDetail" @close="showDetail = false">
        <template #body>
            <UserDetail :userId="selectedUserId" @close="showDetail = false" />
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
import UserDetail from "./UserDetail.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import { ref, reactive, onMounted, h, computed } from "vue";
import axios from "axios";
import SearchPage from "@/components/SearchPage.vue";
const filters = ref([
    // ← đổi thành ref
    {
        name: "search",
        type: "text",
        placeholder: "Tìm theo tên, email, username, số điện thoại...",
    },
    {
        name: "role",
        type: "select",
        placeholder: "Lọc theo vai trò",
        options: [],
    },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            { value: "active", label: "Đang hoạt động" },
            { value: "blocked", label: "Đã khóa" },
        ],
    },
]);
const filterParams = ref({});
const handleFilter = (params) => {
    filterParams.value = params;
    getData(1);
};
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
const showDetail = ref(false);
const selectedUserId = ref(null);
const selectedUser = ref(null);
const showModal = ref(false);
const permissions = usePage().props.auth.permissions;
const can = (permission) => {
    return permissions.includes(permission);
};
const rolesForFilter = ref([]);

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
        type: "status",
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

const actions = computed(() => [
    {
        icon: EditButtonIcon,
        type: "edit",
        hidden: () => !can("nhan_su.sua"),
        onClick: (item) => openEdit(item),
    },
    {
        type: "status",
        // icon đổi theo trạng thái của từng dòng
        icon: (item) => (item.status === "active" ? Lock : Unlock),
        // quyền cũng đổi theo trạng thái của từng dòng:
        // đang active (sắp bị khóa) -> cần quyền lock
        // đang inactive (sắp được mở) -> cần quyền unlock
        hidden: (item) =>
            !can("nhan_su.khoa"),
        onClick: (item) => toggleStatus(item),
    },
    {
        icon: DetailButtonIcon,
        type: "view",
        hidden: () => !can("nhan_su.xem"),
        onClick: (item) => openDetail(item),
        tooltip: "Xem chi tiết",
    },
]);

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
const loadRoles = async () => {
    try {
        const res = await axios.get("/api/roles");

        const data = [
            ...(res.data.data.system || []),
            ...(res.data.data.user || []),
        ];

        const roleFilter = filters.value.find((f) => f.name === "role");

        if (roleFilter) {
            roleFilter.options = [
                { value: "", label: "Tất cả vai trò" },
                ...data.map((role) => ({
                    value: role.name,
                    label: role.name,
                })),
            ];
        }
    } catch (error) {
        console.error("Không load được vai trò", error);
    }
};
const getData = async (page = 1) => {
    const response = await axios.get(`/api/users/user`, {
        params: {
            page,
            per_page: users.value.per_page,
            ...filterParams.value,
        },
    });
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
        alert("Cập nhật thất bại");
    }
}
function show(id) {
    selectedUserId.value = id;
    showDetail.value = true;
}

onMounted(() => {
    loadRoles();
    reloadData();
});
</script>
