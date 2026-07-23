<template>
    <Head title="Ngân hàng" />

    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[
                {
                    text: 'Ngân hàng',
                    link: null,
                },
            ]"
        />

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách ngân hàng</h2>

            <button
                v-if="can('ngan_hang.them')"
                @click="openCreate"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
            >
                + Ngân hàng
            </button>
        </div>

        <!-- FILTER -->
        <div
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
        >
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <!-- TABLE -->
        <DataTable
            :columns="columns"
            :data="banks.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(banks.current_page - 1) * banks.per_page"
            emptyMessage="Không có dữ liệu"
        />

        <!-- PAGINATION -->
        <Pagination
            :totalItems="banks.total"
            :itemsPerPage="banks.per_page"
            :currentPage="banks.current_page"
            :doingShow="banks.data.length"
            @page-change="handlePageChange"
            @items-per-page-change="handlePerPageChange"
        />
    </AdminLayout>

    <!-- FORM -->
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <BankForm
                :bank="selectedBank"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, h, onMounted } from "vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import SearchPage from "@/components/SearchPage.vue";
import Modal from "@/components/Modal.vue";
import BankForm from "./BankForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import { usePermission } from "@/composables/usePermission";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";
const { can } = usePermission();
const perPage = ref(10);
const banks = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
});

const showModal = ref(false);

const selectedBank = ref(null);

const filterParams = ref({});

const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Mã NH / Tên NH",
    },
    {
        name: "status",
        type: "select",
        placeholder: "Trạng thái",
        options: [
            {
                value: 1,
                label: "Hoạt động",
            },
            {
                value: 0,
                label: "Khóa",
            },
        ],
    },
];

function handleFilter(params) {
    filterParams.value = params;

    getData(1);
}

const columns = [
    {
        label: "Ảnh",
        key: "logo",
        align: "text-center",
        render: (row) =>
            h("img", {
                src: row.logo
                    ? `/storage/${row.logo}`
                    : "/images/default-bank.png",
                class: "w-10 h-10 object-cover rounded-full mx-auto",
            }),
    },

    {
        label: "Mã NH",
        key: "code",
        align: "text-start",
    },

    {
        label: "Tên ngân hàng",
        key: "name",
        align: "text-start",
    },

    {
        label: "Tên viết tắt",
        key: "short_name",
        align: "text-start",
    },

    {
        label: "Trạng thái",
        key: "status",
        align: "text-start",
        render: (row) =>
            h(
                "span",
                {
                    class: row.status
                        ? "bg-green-100 text-green-700 px-2 py-1 rounded"
                        : "bg-red-100 text-red-700 px-2 py-1 rounded",
                },
                row.status ? "Đang hoạt động" : "Ngừng hoạt động",
            ),
    },
];

const actions = [
    {
        icon: EditButtonIcon,

        hidden: (row) => row.is_used,

        onClick: (item) => openEdit(item),
    },

    {
        icon: Lock,

        onClick: (item) => toggleStatus(item),
    },
];

function openCreate() {
    selectedBank.value = null;

    showModal.value = true;
}

function openEdit(item) {
    selectedBank.value = item;

    showModal.value = true;
}

async function toggleStatus(item) {
    await axios.patch(`/api/accountant/banks/${item.id}/toggle-status`);

    getData(banks.value.current_page);
}

async function fetchData(page = 1) {
    const res = await axios.get("/api/accountant/banks", {
        params: {
            page,
            per_page: perPage.value,
            ...filterParams.value,
        },
    });

    banks.value = res.data;
}
const handlePerPageChange = (value) => {
    perPage.value = value;
    getData(1);
};
function getData(page = 1) {
    fetchData(page);
}

function handlePageChange(page) {
    getData(page);
}

function reloadData() {
    showModal.value = false;

    getData(banks.value.current_page);
}

useRealtimeRefresh(reloadData);

onMounted(() => {
    getData();
});
</script>
