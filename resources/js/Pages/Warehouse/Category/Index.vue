<template>
    <Head title="Danh mục" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Danh mục', link: null }]" />
        <div class="flex items-center border-b mb-5 gap-2">
            <Link
                href="/warehouse/products"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    $page.url.startsWith('/warehouse/products')
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Sản phẩm
            </Link>

            <Link
                href="/warehouse/categories"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    $page.url.startsWith('/warehouse/categories')
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Danh mục
            </Link>

            <Link
                href="/warehouse/units"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    $page.url.startsWith('/warehouse/units')
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Đơn vị
            </Link>
        </div>
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách danh mục</h2>

            <button
                v-if="can('purchase_category.create')"
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
                + Danh mục
            </button>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tìm  danh mục..."
                    class="border border-gray-300 px-3 py-2 rounded-lg w-80 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                />
            </div>
        </div>

        <DataTable
            :columns="columns"
            :data="categories.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(categories.current_page - 1) * categories.per_page"
            emptyMessage="Không có danh mục"
        />

        <Pagination
            :totalItems="categories.total"
            :itemsPerPage="categories.per_page"
            :currentPage="categories.current_page"
            :doingShow="categories.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <CategoryForm
                :category="selectedCategory"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, watch, h, computed } from "vue";
import axios from "axios";
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import CategoryForm from "./CategoryForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import { usePermission } from "@/composables/usePermission";
const { can, canAny } = usePermission();
const page = ref(1);
const search = ref("");
const categories = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const showModal = ref(false);
const selectedCategory = ref(null);

const columns = [
    {
        label: "Tên",
        key: "name",
        align: "text-start",
    },
    {
        label: "Mã",
        key: "code",
        align: "text-start",
    },
    {
        label: "Trạng thái",
        key: "status",

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
        hidden: () => !can("purchase_category.update"),
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
            item.status === "active"
                ? !can("purchase_category.lock")
                : !can("purchase_category.unlock"),
        onClick: (item) => toggleStatus(item),
    },
    // {
    //     icon: DetailButtonIcon,
    //     type: "view",
    //     hidden: () => !can("purchase_category.detail"),
    //     onClick: (item) => openDetail(item),
    //     tooltip: "Xem chi tiết",
    // },
]);

function openCreate() {
    selectedCategory.value = null;
    showModal.value = true;
}

function openEdit(category) {
    selectedCategory.value = {
        ...category,
    };
    showModal.value = true;
}
function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}
const fetchCategories = async () => {
    const res = await axios.get("/api/warehouse/categories", {
        params: {
            page: page.value,
            search: search.value,
        },
    });

    categories.value = res.data;
};
const getData = debounce(() => {
    page.value = 1; // reset page khi search
    fetchCategories();
}, 300);
watch(search, () => {
    getData();
});
// async function getData(page = 1) {
//     const res = await axios.get(`/api/warehouse/categories?page=${page}`);

//     categories.value = res.data;
// }

function handlePageChange(p) {
    page.value = p;
    fetchCategories();
}

function reloadData() {
    fetchCategories();
    showModal.value = false;
}

async function toggleStatus(category) {
    try {
        await axios.patch(`/api/warehouse/categories/${category.id}/status`);

        await getData(categories.value.current_page);
    } catch (error) {
        console.error(error);
        alert("Không thể đổi trạng thái");
    }
}

onMounted(() => {
    fetchCategories();
});
</script>
