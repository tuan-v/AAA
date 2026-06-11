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
            <select
                v-model="stockFilter"
                @change="getData(1)"
                class="border border-gray-300 px-3 py-2 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
            >
                <option value="all">Tất cả</option>
                <option value="in_stock">Còn hàng</option>
                <option value="out_stock">Hết hàng</option>
            </select>

            <!-- Search -->
            <div class="flex items-center gap-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Tìm sản phẩm / danh mục..."
                    class="border border-gray-300 px-3 py-2 rounded-lg w-80 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                />

                <!-- <button
                    @click="getData(1)"
                    class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg transition"
                >
                    Tìm
                </button> -->
            </div>
        </div>
        <DataTable
            :columns="columns"
            :data="products.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(products.current_page - 1) * products.per_page"
            emptyMessage="Không có sản phẩm"
        />

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

const search = ref("");
const stockFilter = ref("all");
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
        align: "text-center",
    },
    {
        label: "Tồn kho",
        key: "quantity",
        align: "text-center",
        render: (row) =>
            h(
                "span",
                {
                    class:
                        row.quantity > 0
                            ? "px-2 py-1 rounded bg-green-100 text-green-700"
                            : "px-2 py-1 rounded bg-red-100 text-red-700",
                },
                row.quantity > 0 ? "Còn hàng" : "Hết hàng",
            ),
    },
    {
        label: "Giá nhập",
        key: "purchase_price",
        align: "text-center",
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
        align: "text-center",
        render: (row) =>
            h(
                "span",
                { class: "font-medium " },
                `${formatMoney(row.sell_price)} ₫`,
            ),
    },
    {
        label: "Đơn vị",
        key: "unit_name",
        align: "text-center",
    },
    {
        label: "Danh mục",
        key: "category_name",
        align: "text-center",
    },
    {
        label: "Trạng thái",
        key: "status",
        align: "text-center",
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
const fetchData = async (page = 1) => {
    try {
        const response = await axios.get(`/api/warehouse/products`, {
            params: {
                page,
                stock: stockFilter.value,
                search: search.value,
            },
        });

        products.value = response.data;
    } catch (error) {
        console.error(error);
    }
};
const getData = debounce((page = 1) => {
    fetchData(page);
}, 300);

function openCreate() {
    selectedProduct.value = null;
    showModal.value = true;
}

watch(search, () => {
    getData(1);
});
watch(stockFilter, () => {
    getData(1);
});
function openEdit(product) {
    selectedProduct.value = { ...product };
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
