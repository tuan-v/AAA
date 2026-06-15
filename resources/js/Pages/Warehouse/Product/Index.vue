<template>
    <Head title="Sản phẩm" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Sản phẩm', link: null }]" />
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
            <h2 class="text-2xl font-bold">Danh sách sản phẩm</h2>

            <button
                v-if="can('product.create')"
                @click="openCreate"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
                <span class="text-lg">+ Sản phẩm</span>
            </button>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <!-- Filter -->
            <div
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5"
            >
                <SearchPage
                    :filters="filters"
                    @filter="handleFilter"
                    :defaultParams="{ stock: 'all' }"
                />
            </div>
        </div>
        <DataTable
            :columns="columns"
            :data="products.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(products.current_page - 1) * products.per_page"
            emptyMessage="Không có sản phẩm"
        >
            <template #cell-quantity="{ item }">
                {{ item.quantity || "0" }} {{ item.unit_name || " " }}
            </template>
        </DataTable>

        <Pagination
            :totalItems="products.total"
            :itemsPerPage="products.per_page"
            :currentPage="products.current_page"
            :doingShow="products.data.length"
            @page-change="handlePageChange"
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <ProductForm
                :product="selectedProduct"
                @saved="reloadData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { ref, onMounted, h, watch } from "vue";
import axios from "axios";
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";
import ProductForm from "./ProductForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import { formatMoney } from "@/config/helpers";
import SearchPage from "@/components/SearchPage.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const filters = [
    {
        name: "search",
        type: "text",
        placeholder: "Tìm sản phẩm / danh mục...",
    },
    {
        name: "stock",
        type: "select",
        placeholder: "Trạng thái kho",
        options: [
            { value: "all", label: "Tất cả" },
            { value: "in_stock", label: "Còn hàng" },
            { value: "out_stock", label: "Hết hàng" },
        ],
    },
];
const permissions = usePage().props.auth.permissions || [];

const can = (permission) => {
    return permissions.includes(permission);
};

const showModal = ref(false);
const selectedProduct = ref(null);

const products = ref({
    data: [],
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const columns = [
    {
        label: "Ảnh",
        key: "image",
        align: "text-center",
        render: (row) =>
            h("img", {
                src: row.image,
                class: "w-12 h-12 object-cover rounded",
            }),
    },
    {
        label: "Tên",
        key: "name",
        align: "text-start",
    },
    {
        label: "Tồn kho",
        key: "quantity",
        align: "text-right",
    },

    {
        label: "Giá nhập",
        key: "purchase_price",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                { class: "font-medium" },
                `${formatMoney(row.purchase_price)} ₫`,
            ),
    },
    {
        label: "Giá bán",
        key: "sell_price",
        align: "text-right",
        render: (row) =>
            h(
                "span",
                { class: "font-medium " },
                `${formatMoney(row.sell_price)} ₫`,
            ),
    },

    {
        label: "Danh mục",
        key: "category_name",
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
                    class:
                        row.status === "active"
                            ? "bg-green-100 text-green-700 px-2 py-1 rounded"
                            : "bg-red-100 text-red-700 px-2 py-1 rounded",
                },
                row.status === "active" ? "Đang hoạt động" : "Ngừng hoạt động",
            ),
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
];
function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}
const fetchData = async (page = 1, params = {}) => {
    try {
        const response = await axios.get(`/api/warehouse/products`, {
            params: {
                page,
                search: params.search || "",
                stock: params.stock || "all",
            },
        });

        products.value = response.data;
    } catch (error) {
        console.error(error);
    }
};
const getData = (page = 1, params = {}) => {
    fetchData(page, params);
};

function openCreate() {
    selectedProduct.value = null;
    showModal.value = true;
}
function handleFilter(params) {
    fetchData(1, params);
}
function openEdit(product) {
    selectedProduct.value = {
        id: product.id,
        name: product.name || "",
        sku: product.sku || "",
        category_id: product.category_id || "",
        unit_id: product.unit_id || "",
        type: product.type || "hang_hoa",
        purchase_price: Number(product.purchase_price || 0),
        sell_price: Number(product.sell_price || 0),
        quantity: Number(product.quantity || 0),
        status: product.status || "active",
        description: product.description || "",
        image: product.image || null,
        // Thêm các field khác nếu có
    };
    showModal.value = true;
}

const handlePageChange = (page) => {
    getData(page);
};

// const getData = async (page = 1) => {
//     try {
//         const response = await axios.get(`/api/warehouse/products`, {
//             params: {
//                 page,
//                 stock: stockFilter.value,
//                 search: search.value,
//             },
//         });

//         products.value = response.data;
//     } catch (error) {
//         console.error(error);
//     }
// };

const reloadData = () => {
    getData(products.value.current_page);
    showModal.value = false;
};

async function deleteProduct(id) {
    if (!confirm("Bạn có chắc muốn xóa sản phẩm này?")) {
        return;
    }

    try {
        await axios.delete(`/api/warehouse/products/${id}`);

        getData(products.value.current_page);
    } catch (error) {
        console.error(error);
        alert("Xóa sản phẩm thất bại");
    }
}
async function toggleStatus(product) {
    const newStatus = product.status === "active" ? "inactive" : "active";

    try {
        await axios.patch(`/api/warehouse/products/${product.id}/status`);

        const index = products.value.data.findIndex((p) => p.id === product.id);

        if (index !== -1) {
            products.value.data[index].status = newStatus;
        }
    } catch (error) {
        console.error(error);
    }
}

onMounted(() => {
    getData();
});
</script>
