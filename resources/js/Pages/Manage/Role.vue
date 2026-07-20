<template>
    <Head title="Quản lý vai trò" />
    <AdminLayout>
        <PageBreadcrumb
            title="Vai trò"
            :items="[{ text: 'Vai trò', link: null }]"
        />

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                    Danh sách vai trò
                </h2>
            </div>
            <button
                v-if="can('vai_tro.them')"
                @click="openCreate"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            >
                + Thêm vai trò
            </button>
        </div>

        <div
            class="mb-4 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        >
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
        >
            <div
                class="hidden grid-cols-[minmax(240px,1fr)_110px_240px_48px] gap-4 border-b border-gray-200 bg-gray-50 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:grid dark:border-gray-700 dark:bg-gray-900/30"
            >
                <span>Vai trò</span><span class="text-center">Người dùng</span
                ><span>Quyền truy cập</span><span></span>
            </div>
            <div v-if="loading" class="p-8 text-center text-sm text-gray-500">
                Đang tải danh sách...
            </div>
            <div v-else-if="!roles.length" class="p-10 text-center">
                <p class="font-medium text-gray-700 dark:text-gray-200">
                    Không có vai trò nào
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Hãy thay đổi bộ lọc hoặc tạo vai trò mới.
                </p>
            </div>

            <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-700/40"
                >
                    <div class="flex items-center gap-4 px-4 py-3">
                        <div
                            :class="
                                role.type === 'system'
                                    ? 'bg-slate-100 text-slate-600'
                                    : 'bg-blue-50 text-blue-600'
                            "
                            class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl sm:flex"
                        >
                            <ShieldCheck
                                v-if="role.type === 'system'"
                                class="h-4 w-4"
                            />
                            <UserRound v-else class="h-4 w-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="truncate font-semibold text-gray-800 dark:text-white"
                                    >{{ role.name }}</span
                                >
                                <span
                                    :class="
                                        role.type === 'system'
                                            ? 'bg-slate-100 text-slate-600'
                                            : 'bg-blue-50 text-blue-600'
                                    "
                                    class="rounded px-2 py-0.5 text-[11px] font-medium"
                                >
                                    {{
                                        role.type === "system"
                                            ? "Hệ thống"
                                            : "Công ty"
                                    }}
                                </span>
                                <span
                                    v-if="role.is_protected"
                                    class="rounded bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-600"
                                    >Bảo vệ</span
                                >
                            </div>
                            <p class="mt-0.5 truncate text-xs text-gray-500">
                                {{ role.description || "Không có mô tả" }}
                            </p>
                        </div>

                        <div class="hidden w-24 shrink-0 text-center sm:block">
                            <div
                                class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                            >
                                {{ role.users_count || 0 }}
                            </div>
                            <div class="text-[11px] text-gray-400">
                                người dùng
                            </div>
                        </div>

                        <div class="hidden w-56 shrink-0 md:block">
                            <div
                                class="flex items-center gap-1.5 overflow-hidden"
                            >
                                <span
                                    v-for="permission in permissionNames(
                                        role,
                                    ).slice(0, 2)"
                                    :key="permission"
                                    class="max-w-[85px] truncate rounded bg-purple-50 px-2 py-1 text-[11px] text-purple-600"
                                    :title="permission"
                                    >{{ permissionLabel(role, permission) }}</span
                                >
                                <button
                                    v-if="permissionNames(role).length > 2"
                                    type="button"
                                    @click="togglePermissions(role.id)"
                                    class="whitespace-nowrap rounded px-1.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"
                                >
                                    {{
                                        expandedRoleId === role.id
                                            ? "Thu gọn"
                                            : `+${permissionNames(role).length - 2} quyền`
                                    }}
                                </button>
                                <span
                                    v-if="!permissionNames(role).length"
                                    class="text-xs italic text-gray-400"
                                    >Chưa có quyền</span
                                >
                            </div>
                        </div>

                        <button
                            v-if="permissionNames(role).length"
                            type="button"
                            @click="togglePermissions(role.id)"
                            class="rounded-lg px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 md:hidden"
                        >
                            {{ permissionNames(role).length }} quyền
                        </button>

                        <button
                            v-if="can('vai_tro.sua') && !role.is_protected"
                            @click="openEdit(role)"
                            class="rounded-lg p-2 text-blue-600 hover:bg-blue-50"
                            title="Chỉnh sửa vai trò"
                        >
                            <EditButtonIcon class="h-4 w-4" />
                        </button>
                    </div>

                    <div
                        v-if="expandedRoleId === role.id"
                        class="border-t border-gray-100 bg-gray-50/70 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/20"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span
                                class="text-xs font-semibold uppercase tracking-wide text-gray-500"
                                >Toàn bộ
                                {{ permissionNames(role).length }} quyền</span
                            >
                            <button
                                type="button"
                                @click="togglePermissions(role.id)"
                                class="text-xs text-blue-600 hover:underline"
                            >
                                Thu gọn
                            </button>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            <div
                                v-for="(
                                    group, moduleName
                                ) in groupedPermissions(role)"
                                :key="moduleName"
                                class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div
                                    class="mb-2 text-xs font-semibold capitalize text-gray-700 dark:text-gray-200"
                                >
                                    {{ moduleLabel(moduleName) }}
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="permission in group"
                                        :key="permission"
                                        class="rounded-md bg-purple-50 px-2 py-1 text-[11px] text-purple-700 dark:bg-purple-900/20 dark:text-purple-300"
                                        :title="permission"
                                        >{{ permissionLabel(role, permission) }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
import { computed, onMounted, ref } from "vue";
import { ShieldCheck, UserRound } from "lucide-vue-next";
import { Head, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import Modal from "@/components/Modal.vue";
import SearchPage from "@/components/SearchPage.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import RoleForm from "./RoleForm.vue";

const filters = [
    { name: "search", type: "text", placeholder: "Tìm tên vai trò..." },
];
const roles = ref([]);
const loading = ref(false);
const filterParams = ref({});
const selectedRole = ref(null);
const showModal = ref(false);
const expandedRoleId = ref(null);
const permissions = usePage().props.auth.permissions;
const can = (permission) => permissions.includes(permission);

const permissionNames = (role) =>
    role.permissions_list || role.permissions?.map((item) => item.name) || [];
const permissionLabel = (role, name) =>
    role.permissions?.find((item) => item.name === name)?.description || name;
const moduleLabel = (name) => ({
    nhan_su: 'Nhân sự', vai_tro: 'Vai trò', quyen: 'Quyền', nhat_ky: 'Nhật ký hoạt động',
    tai_khoan: 'Tài khoản', ngan_hang: 'Ngân hàng', tien_te: 'Tiền tệ',
    cong_no_khach_hang: 'Công nợ khách hàng', cong_no_nha_cung_cap: 'Công nợ nhà cung cấp',
    danh_muc_mua_hang: 'Danh mục mua hàng', don_mua: 'Đơn mua', san_pham_mua_hang: 'Sản phẩm mua hàng',
    don_vi_mua_hang: 'Đơn vị tính mua hàng', khach_hang: 'Khách hàng', don_ban: 'Đơn bán',
    nha_cung_cap: 'Nhà cung cấp', giao_dich: 'Giao dịch', loai_giao_dich: 'Loại giao dịch',
    kho: 'Kho', danh_muc_kho: 'Danh mục kho', san_pham_kho: 'Sản phẩm kho', phieu_kho: 'Phiếu kho',
    chuyen_kho: 'Chuyển kho', don_vi_kho: 'Đơn vị tính kho',
}[name] || name.replaceAll('_', ' '));
const totalUsers = computed(() =>
    roles.value.reduce(
        (total, role) => total + Number(role.users_count || 0),
        0,
    ),
);

function groupedPermissions(role) {
    return permissionNames(role).reduce((groups, permission) => {
        const moduleName = permission.split(".")[0].replaceAll("_", " ");
        (groups[moduleName] ||= []).push(permission);
        return groups;
    }, {});
}

function togglePermissions(roleId) {
    expandedRoleId.value = expandedRoleId.value === roleId ? null : roleId;
}

function openCreate() {
    selectedRole.value = null;
    showModal.value = true;
}

function openEdit(role) {
    selectedRole.value = role;
    showModal.value = true;
}

function handleFilter(params) {
    filterParams.value = params;
    getData();
}

async function getData() {
    loading.value = true;
    try {
        const { data } = await axios.get("/api/roles", {
            params: filterParams.value,
        });
        roles.value = [
            ...(data.data?.system || []),
            ...(data.data?.user || []),
        ];
    } finally {
        loading.value = false;
    }
}

function reloadData() {
    showModal.value = false;
    getData();
}

onMounted(getData);
</script>
