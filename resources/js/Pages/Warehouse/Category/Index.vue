<template>
    <Head title="Danh mục" />

    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Danh mục', link: null }]" />

        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold">Danh sách danh mục</h2>

            <button
                @click="openCreate"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
                + Thêm danh mục
            </button>
        </div>

        <DataTable
            :columns="columns"
            :data="categories"
            :showIndex="true"
            :actions="actions"
            emptyMessage="Không có danh mục"
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <CategoryForm
                :category="selectedCategory"
                @saved="getData"
                @close="showModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import CategoryForm from "./CategoryForm.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import Lock from "@/icons/Lock.vue";
import Unlock from "@/icons/Unlock.vue";

const categories = ref([]);
const showModal = ref(false);
const selectedCategory = ref(null);

const columns = [
    { label: "ID", key: "id", width: "80px", align: "text-right" },
    { label: "Tên danh mục", key: "name" },
    { label: "Mô tả", key: "description" },
    { label: "Trạng thái", key: "status_text" },
];

const actions = [
    {
        icon: EditButtonIcon,
        type: "edit",
        onClick: (item) => openEdit(item),
    },
    {
        icon: (item) => (item.status ? Lock : Unlock),
        type: "status",
        onClick: (item) => toggleStatus(item),
    },
];

function openCreate() {
    selectedCategory.value = null;
    showModal.value = true;
}

function openEdit(item) {
    selectedCategory.value = { ...item };
    showModal.value = true;
}

function getData() {
    axios.get("/api/warehouse/categories").then((res) => {
        categories.value = res.data;
    });
}

function toggleStatus(item) {
    axios.patch(`/api/categories/${item.id}/status`).then(() => {
        getData();
    });
}

onMounted(getData);
</script>
