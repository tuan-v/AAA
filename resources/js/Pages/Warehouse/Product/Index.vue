<template>
    <Head title=""></Head>
    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Sản phẩm', link: null }]" />
        <div
            class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5"
        ></div>
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách sản phẩm</h2>
            <button
                v-if="can('product.create')"
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-red-500"
            >
                +
            </button>
        </div>
        <DataTable
            :columns="columns"
            :data="products"
            :showIndex="true"
            :actions="actions"
            emptyMessage="Không có sản phẩm"
        />
    </AdminLayout>
    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <ProductForm
                :product="selectedProduct"
                @saved="getData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>
<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import {
    LayoutDashboardIcon,
    UserGroupIcon,
    BoxIcon,
    BarChartIcon,
} from "@/icons";
import DataTable from "@/components/DataTable.vue";
import { ref, onMounted } from "vue";
// import EyeOn from "../icons/EyeOn.vue";
// import EyeOff from "../icons/EyeOff.vue";
import axios from "axios";
import Modal from "../../../components/Modal.vue";
import ProductForm from "./ProductForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
const categories = ref([]);
const permissions = usePage().props.auth.permissions;
const can = (permission) => {
    return permissions.includes(permission);
};
const selectedProduct = ref(null);
const showModal = ref(false);
const form = ref({
    ten: "",
    so_luong: "",
    mau_sac: "",
    gia: "",
    ten_the_loai: "",
});
const products = ref([]);
const columns = [
    { label: "Tên", key: "ten" },
    { label: "Số lượng", key: "so_luong", align: "text-right" },
    { label: "Màu sắc", key: "mau_sac" },
    { label: "Giá", key: "gia" },
    { label: "Danh mục", key: "ten_the_loai" },
];
const actions = [
    {
        icon: EditButtonIcon,
        onClick: (item) => {
            console.log("Edit", item);
            openEdit(item);
        },
    },
    {
        label: "Xóa",

        onClick: (item) => {
            deleteProduct(item.id);
        },
    },
];
function openCreate() {
    selectedProduct.value = null;
    showModal.value = true;
}
function openEdit(product) {
    console.log("Before:", showModal.value);

    selectedProduct.value = product;
    showModal.value = true;

    console.log("After:", showModal.value);
}
function getData() {
    axios
        .get("/api/warehouse/products")

        .then((response) => {
            products.value = response.data;
        })

        .catch((error) => {
            console.error(error);
        });
}

function deleteProduct(id) {
    if (!confirm("Bạn có chắc muốn xóa?")) {
        return;
    }

    axios.delete(`/api/products/${id}`).then(() => {
        getData();
    });
}

onMounted(() => {
    getData();
});
console.log(categories.value);
</script>
