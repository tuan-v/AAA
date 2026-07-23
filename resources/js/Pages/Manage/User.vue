<template>
    <Head title="Nhân sự" />

    <AdminLayout>
        <PageBreadcrumb
            title="Nhân sự"
            :items="[{ text: 'Nhân sự', link: null }]"
        />

        <section
            class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-800 p-6 text-white shadow-xl shadow-indigo-950/10 sm:p-8"
        >
            <div
                class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
            >
                <div class="max-w-2xl">
                    <span
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-indigo-100 backdrop-blur"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-400"
                        ></span>
                        Không gian quản trị nhân sự
                    </span>
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Đội ngũ của {{ company?.name || "công ty" }}
                    </h1>
                    <p
                        class="mt-3 max-w-xl text-sm leading-6 text-indigo-100/80 sm:text-base"
                    >
                        Quản lý tài khoản, vai trò, phòng ban và trạng thái làm
                        việc trong một màn hình tập trung.
                    </p>
                </div>

                <button
                    v-if="can('nhan_su.them')"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-700 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-white/20"
                    @click="openCreate"
                >
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-100 text-lg leading-none"
                        >+</span
                    >
                    Nhân sự
                </button>
            </div>
        </section>

        <section
            class="relative z-20 -mt-3 grid grid-cols-1 gap-4 px-3 sm:grid-cols-3 sm:px-6"
        >
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                        >
                            {{ stat.label }}
                        </p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">
                            {{ stat.value }}
                        </p>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl"
                        :class="stat.iconClass"
                    >
                        <span class="text-lg font-bold">{{ stat.icon }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="border-b border-slate-100 p-4 sm:p-5">
                <div
                    class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Danh sách nhân sự
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Tìm kiếm và lọc nhanh theo vai trò, phòng ban hoặc
                            trạng thái.
                        </p>
                    </div>
                    <span
                        class="mt-2 inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 sm:mt-0"
                    >
                        {{ users.total }} nhân sự
                    </span>
                </div>
                <SearchPage :filters="filters" @filter="handleFilter" />
            </div>

            <div
                v-if="loadError"
                class="m-5 flex flex-col items-center rounded-2xl border border-red-200 bg-red-50 p-8 text-center"
            >
                <p class="font-semibold text-red-700">
                    Không thể tải danh sách nhân sự
                </p>
                <p class="mt-1 text-sm text-red-600">{{ loadError }}</p>
                <button
                    class="mt-4 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                    @click="getData(users.current_page)"
                >
                    Thử lại
                </button>
            </div>

            <template v-else>
                <DataTable
                    :columns="columns"
                    :data="users.data"
                    :loading="loading"
                    :showIndex="true"
                    :actions="actions"
                    :indexOffset="(users.current_page - 1) * users.per_page"
                    emptyMessage="Chưa có nhân sự phù hợp"
                />
                <div class="border-t border-slate-100 px-4 py-4 sm:px-5">
                    <Pagination
                        :totalItems="users.total"
                        :itemsPerPage="users.per_page"
                        :currentPage="users.current_page"
                        :doingShow="users.data.length"
                        @page-change="handlePageChange"
                    />
                </div>
            </template>
        </section>
    </AdminLayout>

    <Modal v-if="showModal" @close="closeUserForm">
        <template #body>
            <UserForm
                :user="selectedUser"
                :company="company"
                @saved="reloadData"
                @close="closeUserForm"
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
import { computed, h, onMounted, ref } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/components/Pagination.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import SearchPage from "@/components/SearchPage.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import UserForm from "./UserForm.vue";
import UserDetail from "./UserDetail.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

const page = usePage();
const permissions = computed(
    () => page.props.auth?.permissions || page.props.auths?.permissions || [],
);
const company = computed(
    () =>
        page.props.auth?.user?.company ||
        page.props.auths?.user?.company ||
        null,
);
const can = (permission) => permissions.value.includes(permission);

const filters = ref([
    {
        name: "search",
        type: "text",
        placeholder: "Tìm tên, email, tài khoản, số điện thoại...",
    },
    {
        name: "department_id",
        type: "select",
        placeholder: "Tất cả phòng ban",
        options: [],
    },
    {
        name: "role",
        type: "select",
        placeholder: "Tất cả vai trò",
        options: [],
    },
    {
        name: "status",
        type: "select",
        placeholder: "Tất cả trạng thái",
        options: [
            { value: "active", label: "Đang hoạt động" },
            { value: "blocked", label: "Đã khóa" },
            { value: "inactive", label: "Ngừng hoạt động" },
            { value: "pending", label: "Chờ kích hoạt" },
        ],
    },
]);

const filterParams = ref({});
const users = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});
const loading = ref(false);
const loadError = ref("");
const showDetail = ref(false);
const selectedUserId = ref(null);
const selectedUser = ref(null);
const showModal = ref(false);

const stats = computed(() => {
    const visibleUsers = users.value.data || [];
    return [
        {
            label: "Tổng nhân sự",
            value: users.value.total || 0,
            icon: "↗",
            iconClass: "bg-indigo-50 text-indigo-600",
        },
        {
            label: "Hoạt động trên trang",
            value: visibleUsers.filter((user) => user.status === "active")
                .length,
            icon: "✓",
            iconClass: "bg-emerald-50 text-emerald-600",
        },
        {
            label: "Phòng ban hiển thị",
            value: new Set(
                visibleUsers.map((user) => user.department_id).filter(Boolean),
            ).size,
            icon: "⌂",
            iconClass: "bg-amber-50 text-amber-600",
        },
    ];
});

const statusMeta = {
    active: [
        "Đang hoạt động",
        "bg-emerald-50 text-emerald-700 ring-emerald-600/20",
    ],
    blocked: ["Đã khóa", "bg-red-50 text-red-700 ring-red-600/20"],
    inactive: [
        "Ngừng hoạt động",
        "bg-slate-100 text-slate-600 ring-slate-500/20",
    ],
    pending: ["Chờ kích hoạt", "bg-amber-50 text-amber-700 ring-amber-600/20"],
};

const columns = [
    {
        key: "name",
        label: "Nhân sự",
        render: (row) =>
            h("div", { class: "flex items-center gap-3 min-w-[180px]" }, [
                h(
                    "div",
                    {
                        class: "flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-700",
                    },
                    (row.name || "?").charAt(0).toUpperCase(),
                ),
                h("div", {}, [
                    h(
                        "p",
                        { class: "font-semibold text-slate-900" },
                        row.name || "-",
                    ),
                    h(
                        "p",
                        { class: "text-xs text-slate-400" },
                        `@${row.username || "chưa-có"}`,
                    ),
                ]),
            ]),
    },
    {
        key: "email",
        label: "Liên hệ",
        render: (row) =>
            h("div", { class: "min-w-[190px]" }, [
                h("p", { class: "text-slate-700" }, row.email || "-"),
                h(
                    "p",
                    { class: "mt-1 text-xs text-slate-400" },
                    row.phone || "Chưa có số điện thoại",
                ),
            ]),
    },

    {
        label: "Phòng ban",
        render: (row) =>
            h(
                "span",
                { class: "font-medium text-slate-700" },
                row.department_record?.name || "-",
            ),
    },
    {
        label: "Chức vụ",
        render: (row) =>
            h(
                "span",
                { class: "text-slate-600" },
                row.position_record?.name || "-",
            ),
    },
    { key: "roles", label: "Vai trò" },
    {
        key: "status",
        label: "Trạng thái",
        render: (row) => {
            const [label, classes] = statusMeta[row.status] || [
                "Không xác định",
                "bg-slate-100 text-slate-600 ring-slate-500/20",
            ];
            return h(
                "span",
                {
                    class: `inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ${classes}`,
                },
                label,
            );
        },
    },
];

const actions = computed(() => [
    {
        icon: EditButtonIcon,
        type: "edit",
        hidden: () => !can("nhan_su.sua"),
        onClick: openEdit,
        tooltip: "Chỉnh sửa",
    },
    {
        type: "status",
        icon: (item) => (item.status === "active" ? Lock : Unlock),
        iconByItem: true,
        hidden: () => !can("nhan_su.khoa"),
        onClick: toggleStatus,
        tooltip: "Đổi trạng thái",
    },
    {
        icon: DetailButtonIcon,
        type: "view",
        hidden: () => !can("nhan_su.xem"),
        onClick: (item) => openDetail(item.id),
        tooltip: "Xem chi tiết",
    },
]);

function handleFilter(params) {
    filterParams.value = params;
    getData(1);
}

function handlePageChange(pageNumber) {
    getData(pageNumber);
}

function openCreate() {
    selectedUser.value = null;
    showModal.value = true;
}

function openEdit(user) {
    selectedUser.value = user;
    showModal.value = true;
}

function closeUserForm() {
    showModal.value = false;
    selectedUser.value = null;
}

function openDetail(id) {
    selectedUserId.value = id;
    showDetail.value = true;
}

async function getData(pageNumber = 1) {
    loading.value = true;
    loadError.value = "";
    try {
        const { data } = await axios.get("/api/users/user", {
            params: {
                page: pageNumber,
                per_page: users.value.per_page,
                ...filterParams.value,
            },
        });
        users.value = data;
    } catch (error) {
        loadError.value =
            error.response?.data?.message ||
            "Vui lòng kiểm tra kết nối và thử lại.";
    } finally {
        loading.value = false;
    }
}

async function toggleStatus(user) {
    const newStatus = user.status === "active" ? "blocked" : "active";
    const question =
        newStatus === "blocked"
            ? `Khóa tài khoản của ${user.name}?`
            : `Mở lại tài khoản của ${user.name}?`;
    if (!confirm(question)) return;

    try {
        await axios.patch(`/api/users/${user.id}/status`, {
            status: newStatus,
        });
        user.status = newStatus;
        toast.success(
            newStatus === "blocked"
                ? "Đã khóa tài khoản"
                : "Đã mở lại tài khoản",
        );
    } catch (error) {
        toast.error(
            error.response?.data?.message || "Không thể cập nhật trạng thái.",
        );
    }
}

async function reloadData() {
    closeUserForm();
    await getData(users.value.current_page);
}

async function loadFilterOptions() {
    const results = await Promise.allSettled([
        axios.get("/api/roles"),
        axios.get("/api/departments/all"),
    ]);

    if (results[0].status === "fulfilled") {
        const roleData = [
            ...(results[0].value.data.data.system || []),
            ...(results[0].value.data.data.user || []),
        ];
        filters.value.find((item) => item.name === "role").options =
            roleData.map((role) => ({ value: role.name, label: role.name }));
    }
    if (results[1].status === "fulfilled") {
        filters.value.find((item) => item.name === "department_id").options =
            results[1].value.data.map((department) => ({
                value: department.id,
                label: department.name,
            }));
    }
}

useRealtimeRefresh(() => getData(users.value.current_page || 1));

onMounted(() => {
    loadFilterOptions();
    getData();
});
</script>
