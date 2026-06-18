<template>
    <Head title="Đơn vị" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Đơn vị', link: null }]" />
        <div class="flex items-center border-b mb-5 gap-2">
            <Link
                href="/purchase/products"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    $page.url.startsWith('/purchase/products')
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Sản phẩm
            </Link>

            <Link
                href="/purchase/categories"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    $page.url.startsWith('/purchase/categories')
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Danh mục
            </Link>

            <Link
                href="/purchase/units"
                class="px-4 py-2 text-sm font-medium border-b-2 transition"
                :class="
                    $page.url.startsWith('/purchase/units')
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-blue-500'
                "
            >
                Đơn vị
            </Link>
        </div>
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách đơn vị</h2>

            <button
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
                + Đơn vị
            </button>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tìm đơn vị.."
                    class="border border-gray-300 px-3 py-2 rounded-lg w-80 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                />
            </div>
        </div>
        <DataTable
            :columns="columns"
            :data="units.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(units.current_page - 1) * units.per_page"
            emptyMessage="Không có đơn vị"
        />

        <Pagination
            :totalItems="units.total"
            :itemsPerPage="units.per_page"
            :currentPage="units.current_page"
            :doingShow="units.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <UnitForm
                :unit="selectedUnit"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, watch, h } from "vue";
import axios from "axios";
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import UnitForm from "./UnitForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";

const search = ref("");
const units = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
});

const showModal = ref(false);
const selectedUnit = ref(null);

const columns = [
    {
        label: "Tên ",
        key: "name",
    },
    {
        label: "Ký hiệu",
        key: "symbol",
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
function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}
const fetchUnits = async (page = 1) => {
    const res = await axios.get("/api/warehouse/units", {
        params: {
            page,
            search: search.value,
        },
    });

    units.value = res.data;
};
const getData = debounce(() => {
    fetchUnits(1);
}, 300);
watch(search, () => {
    getData();
});
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
];

function openCreate() {
    selectedUnit.value = null;
    showModal.value = true;
}

function openEdit(unit) {
    selectedUnit.value = {
        ...unit,
    };
    showModal.value = true;
}

// async function getData(page = 1) {
//     const res = await axios.get(`/api/warehouse/units?page=${page}`);

//     units.value = res.data;
// }

function handlePageChange(page) {
    getData(page);
}

function reloadData() {
    getData(units.value.current_page);
    showModal.value = false;
}

async function toggleStatus(category) {
    await axios.patch(`/api/warehouse/units/${category.id}/status`);

    reloadData();
}

onMounted(() => {
    getData();
});
</script>
