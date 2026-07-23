<template>
    <Head title="Loại giao dịch" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Loại giao dịch', link: null }]" />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách loại giao dịch</h2>

            <button
                @click="openCreate"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
                + Loại giao dịch
            </button>
        </div>

        <!-- FILTER -->
        <div class="bg-white rounded-xl border shadow-sm p-4 mb-5">
            <p class="text-sm text-gray-500 mb-3">Tìm kiếm và lọc loại giao dịch</p>

            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="categories.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="
                (categories.current_page - 1) * categories.per_page
            "
            emptyMessage="Không có loại giao dịch"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="categories.total"
            :itemsPerPage="categories.per_page"
            :currentPage="categories.current_page"
            :doingShow="categories.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <!-- MODAL CREATE / EDIT -->
    <Modal v-if="showModal" @close="closeModal">
        <template #body>
            <TransactionCategoryForm
                :category="selectedCategory"
                @saved="reloadData"
                @close="closeModal"
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
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import Lock from "@/icons/Lock.vue";
import TransactionCategoryForm from "./TransactionCategoryForm.vue";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

/* ================= STATE ================= */

const categories = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const showModal = ref(false);
const selectedCategory = ref(null);
const filterParams = ref({});

/* ================= FILTER ================= */

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Mã / Tên loại giao dịch",
    },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            { value: "1", label: "Hoạt động" },
            { value: "0", label: "Đã khóa" },
        ],
    },
];

/* ================= TABLE COLUMNS ================= */

const columns = [
    {
        key: "code",
        label: "Mã",
        align: "text-start",
    },
    {
        key: "name",
        label: "Tên loại giao dịch",
        align: "text-start",
    },
    {
        key: "description",
        label: "Mô tả",
        render: (row) => h("span", { class: "text-gray-500" }, row.description ?? "-"),
    },
    {
        label: "Trạng thái",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        row.status === 1
                            ? "text-green-600 bg-green-50 px-2 py-1 rounded-full text-xs font-medium"
                            : "text-gray-500 bg-gray-100 px-2 py-1 rounded-full text-xs font-medium",
                },
                row.status === 1 ? "Hoạt động" : "Đã khóa",
            ),
    },
];

/* ================= ACTIONS ================= */

const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        onClick: (item) => openEdit(item),
    },
    {
        icon: Lock,
        type: "lock",
        onClick: (item) => toggleStatus(item),
    },
    {
        icon: DeleteIcon,
        type: "delete",
        onClick: (item) => handleDelete(item),
    },
];

/* ================= METHODS ================= */

function openCreate() {
    selectedCategory.value = null;
    showModal.value = true;
}

function openEdit(item) {
    selectedCategory.value = item;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedCategory.value = null;
}

function handleFilter(params) {
    filterParams.value = params;
    getData(1);
}

function handlePageChange(page) {
    getData(page);
}

/* debounce */
function debounce(fn, delay = 300) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
}

/* API */
const fetchData = async (page = 1) => {
    const res = await axios.get("/api/accountant/transaction-categories", {
        params: {
            page,
            ...filterParams.value,
        },
    });

    const data = res.data;

    categories.value = {
        data: data.data ?? [],
        total: data.meta?.total ?? 0,
        per_page: data.meta?.per_page ?? 10,
        current_page: data.meta?.current_page ?? 1,
        last_page: data.meta?.last_page ?? 1,
    };
};

const getData = debounce(fetchData, 300);

/* reload sau khi thêm/sửa */
function reloadData() {
    closeModal();
    getData(categories.value.current_page);
}

/* khóa / mở loại giao dịch */
async function toggleStatus(item) {
    const nextStatus = item.status === 1 ? 0 : 1;
    const label = nextStatus === 1 ? "mở" : "khóa";

    if (!confirm(`Xác nhận ${label} loại giao dịch "${item.name}"?`)) return;

    try {
        await axios.put(`/api/accountant/transaction-categories/${item.id}`, {
            status: nextStatus,
        });
        getData(categories.value.current_page);
    } catch (e) {
        alert(e.response?.data?.message ?? "Có lỗi xảy ra, vui lòng thử lại.");
    }
}

/* xóa - chỉ thành công nếu backend xác nhận chưa được sử dụng */
async function handleDelete(item) {
    if (!confirm(`Xác nhận xóa loại giao dịch "${item.name}"?`)) return;

    try {
        await axios.delete(`/api/accountant/transaction-categories/${item.id}`);
        getData(categories.value.current_page);
    } catch (e) {
        // Backend nên trả 422 kèm message rõ ràng khi category đã được dùng
        alert(
            e.response?.data?.message ??
                "Không thể xóa loại giao dịch này (có thể đã được sử dụng).",
        );
    }
}

/* INIT */
useRealtimeRefresh(reloadData);

onMounted(() => {
    getData(1);
});
</script>
