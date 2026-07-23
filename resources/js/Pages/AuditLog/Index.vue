<template>
    <Head title="Lịch sử hoạt động" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[{ text: 'Lịch sử hoạt động', link: null }]"
        />

        <div
            v-if="!can('nhat_ky.xem')"
            class="p-10 text-center text-gray-500"
        >
            Bạn không có quyền truy cập trang này.
        </div>

        <template v-else>
            <h2 class="text-2xl font-bold mb-5">Lịch sử hoạt động hệ thống</h2>

            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
            >
                <SearchPage :filters="filters" @filter="handleFilter" />
            </div>

            <DataTable
                :columns="columns"
                :data="logs.data"
                :showIndex="true"
                :actions="actions"
                :indexOffset="(logs.current_page - 1) * logs.per_page"
                emptyMessage="Không có dữ liệu lịch sử"
            />

            <Pagination
                :totalItems="logs.total"
                :itemsPerPage="logs.per_page"
                :currentPage="logs.current_page"
                :doingShow="logs.data.length"
                @page-change="handlePageChange"
                @items-per-page-change="handlePerPageChange"
            />
        </template>
    </AdminLayout>

    <Modal v-if="showDetailModal" @close="showDetailModal = false">
        <template #header>
            <h3 class="text-xl font-semibold">Chi tiết hoạt động</h3>
        </template>
        <template #body>
            <div v-if="detailLoading" class="py-12 text-center text-gray-500">
                Đang tải thông tin truy vết...
            </div>
            <AuditLogDetail
                v-else
                :log="selectedLog"
                @close="showDetailModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, h } from "vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import SearchPage from "@/components/SearchPage.vue";
import DetailButtonIcon from "@/icons/DetailButtonIcon.vue";
import { usePermission } from "@/composables/usePermission";
import AuditLogDetail from "./AuditLogDetail.vue";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";
import { toast } from "vue3-toastify";
const { can } = usePermission();

const filters = [
    { name: "search", type: "text", placeholder: "Tìm theo mô tả / model..." },
    {
        name: "action",
        type: "select",
        placeholder: "Hành động",
        options: [
            { value: "create", label: "Thêm mới" },
            { value: "update", label: "Cập nhật" },
            { value: "approve", label: "Duyệt" },
            { value: "reject", label: "Từ chối" },
            { value: "cancel", label: "Hủy" },
            { value: "delete", label: "Xóa" },
            { value: "lock", label: "Khóa" },
            { value: "unlock", label: "Mở khóa" },
        ],
    },
    { name: "date_from", type: "date", placeholder: "Từ ngày", config: { maxDate: "today" } },
    { name: "date_to", type: "date", placeholder: "Đến ngày", config: { maxDate: "today" } },
    {
        name: "user_id",
        type: "select",
        placeholder: "Nhân sự",
        options: [],
    },
];

const actionConfig = {
    create: { text: "Thêm mới", class: "bg-blue-100 text-blue-700" },
    update: { text: "Cập nhật", class: "bg-yellow-100 text-yellow-700" },
    approve: { text: "Duyệt", class: "bg-green-100 text-green-700" },
    reject: { text: "Từ chối", class: "bg-red-100 text-red-700" },
    cancel: { text: "Hủy", class: "bg-orange-100 text-orange-700" },
    delete: { text: "Xóa", class: "bg-gray-200 text-gray-700" },
    lock: { text: "Khóa", class: "bg-slate-200 text-slate-700" },
    unlock: { text: "Mở khóa", class: "bg-sky-100 text-sky-700" },
};

const logs = ref({ data: [], total: 0, per_page: 10, current_page: 1 });
const perPage = ref(10);
const filterParams = ref({});
const showDetailModal = ref(false);
const detailLoading = ref(false);
const selectedLog = ref(null);
const users = ref([]);
const columns = [
    {
        label: "Người thực hiện",
        render: (row) => h("span", {}, row.user?.name ?? "Hệ thống"),
    },
    {
        label: "Hành động",
        render: (row) => {
            const config = actionConfig[row.action_key] ?? {
                text: row.action_label || "Hành động khác",
                class: "bg-gray-100 text-gray-600",
            };
            return h(
                "span",
                {
                    class: `${config.class} px-3 py-1 rounded-full text-xs font-medium`,
                },
                config.text,
            );
        },
    },
    {
        label: "Đối tượng",
        render: (row) =>
            h("span", { class: "font-medium text-gray-700" }, `${row.model_label || "Dữ liệu"} #${row.model_id}`),
    },

    {
        key: "description",
        label: "Mô tả",
    },
    {
        key: "created_at",
        label: "Thời gian",
        align: "text-right",
        render: (row) => {
            const time = row.created_at_formatted || "-";
            return h("span", { class: "whitespace-nowrap" }, time);
        },
    },
];

const actions = [
    {
        icon: DetailButtonIcon,
        title: "Xem chi tiết",
        hidden: () => !can("nhat_ky.xem"),
        onClick: async (row) => {
            showDetailModal.value = true;
            detailLoading.value = true;
            selectedLog.value = null;
            try {
                const response = await axios.get(`/api/audit-logs/${row.id}`);
                selectedLog.value = response.data;
            } catch (error) {
                showDetailModal.value = false;
                toast.error("Không thể tải chi tiết lịch sử hoạt động.");
            } finally {
                detailLoading.value = false;
            }
        },
    },
];

function handleFilter(params) {
    filterParams.value = params;
    getData(1);
}

async function getData(page = 1) {
    const res = await axios.get("/api/audit-logs", {
        params: { page, per_page: perPage.value, ...filterParams.value },
    });
    logs.value = res.data;
}
const fetchUsers = async () => {
    try {
        const res = await axios.get("/api/users/user"); // hoặc route của bạn
        users.value = res.data.data || res.data; // tùy cấu trúc trả về

        // Tìm filter user_id và gán options
        const userFilter = filters.find((f) => f.name === "user_id");
        if (userFilter) {
            userFilter.options = users.value.map((user) => ({
                value: user.id,
                label: user.name || user.email || `User ${user.id}`,
            }));
        }
    } catch (error) {
        console.error("Lỗi khi lấy danh sách user:", error);
    }
};
function handlePageChange(page) {
    getData(page);
}

function handlePerPageChange(value) {
    perPage.value = value;
    getData(1);
}

useRealtimeRefresh(() => getData(1));

onMounted(() => {
    fetchUsers();
    getData(1);
});
</script>
