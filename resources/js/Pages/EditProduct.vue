<template>
    <div class="p-10 bg-gray-100 min-h-screen">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow">
            <h1 class="text-3xl font-bold mb-6 text-center">Sửa sản phẩm</h1>

            <div class="mb-4">
                <label>Tên sản phẩm</label>

                <input
                    v-model="form.ten"
                    type="text"
                    class="border w-full p-3 rounded"
                />
                <p class="text-red-500">{{ errors.ten?.[0] }}</p>
            </div>

            <div class="mb-4">
                <label>Giá</label>

                <input
                    v-model="form.gia"
                    type="number"
                    class="border w-full p-3 rounded"
                />
                <p class="text-red-500">{{ errors.gia?.[0] }}</p>
            </div>

            <div class="mb-4">
                <label>Số lượng</label>

                <input
                    v-model="form.so_luong"
                    type="number"
                    class="border w-full p-3 rounded"
                />
                <p class="text-red-500">{{ errors.so_luong?.[0] }}</p>
            </div>

            <div class="mb-4">
                <label>Màu sắc</label>

                <select
                    v-model="form.mau_sac"
                    class="border w-full p-3 rounded"
                >
                    <option value="Đỏ">Đỏ</option>
                    <option value="Xanh">Xanh</option>
                    <option value="Tím">Tím</option>
                </select>
                <p class="text-red-500">{{ errors.mau_sac?.[0] }}</p>
            </div>

            <div class="mb-4">
                <label>Thể loại</label>

                <select
                    v-model="form.id_the_loai"
                    class="border w-full p-3 rounded"
                >
                    <option
                        v-for="item in categories"
                        :key="item.id"
                        :value="item.id"
                    >
                        {{ item.ten_the_loai }}
                    </option>
                </select>
            </div>

            <button
                @click="updateProduct"
                :disabled="loading"
                class="bg-blue-500 hover:bg-blue-600 text-white rounded d-inline p-2 rounded-lg"
            >
                {{ loading ? "Dang cap nhat..." : "Cap nhat" }}
            </button>
            <a
                href="/product"
                class="d-inline p-2 bg-red-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition"
            >
                Thoát
            </a>
        </div>
    </div>
</template>

<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { reactive, ref } from "vue";
import axios from "axios";
import { onMounted } from "vue";

const loading = ref(false);
const errors = ref({});
const page = usePage();
const categories = ref([]);
const form = reactive({
    ten: "",
    so_luong: "",
    mau_sac: "",
    gia: "",
    id_the_loai: "",
});
function getDetail() {
    axios.get(`/api/product/${page.props.id}`).then((response) => {
        form.ten = response.data.ten;
        form.gia = response.data.gia;
        form.so_luong = response.data.so_luong;
        form.mau_sac = response.data.mau_sac;
        form.id_the_loai = response.data.id_the_loai;
    });
}
function updateProduct() {
    loading.value = true;
    axios
        .put(`/api/product/${page.props.id}`, form)
        .then(() => {
            alert("Cap nhat thanh cong");
            router.visit("/product");
        })
        .catch((error) => {
            errors.value = error.response.data.errors;
        })
        .finally(() => {
            loading.value = false;
        });
}
function getCategories() {
    axios.get(`/api/theloai`).then((response) => {
        categories.value = response.data;
    });
}
onMounted(() => {
    getDetail();
    getCategories();
});
</script>
