<template>
    <div class="min-h-screen bg-gray-100 p-10">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-3xl font-bold text-center mb-8 text-blue-600">
                Chi tiết sản phẩm
            </h1>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-500 mb-1">Tên sản phẩm</p>

                    <div class="border rounded-lg p-3 bg-gray-50">
                        {{ product.ten }}
                    </div>
                </div>

                <div>
                    <p class="text-gray-500 mb-1">Giá</p>

                    <div class="border rounded-lg p-3 bg-gray-50">
                        {{ product.gia }}
                    </div>
                </div>

                <div>
                    <p class="text-gray-500 mb-1">Số lượng</p>

                    <div class="border rounded-lg p-3 bg-gray-50">
                        {{ product.so_luong }}
                    </div>
                </div>

                <div>
                    <p class="text-gray-500 mb-1">Màu sắc</p>

                    <div class="border rounded-lg p-3 bg-gray-50">
                        {{ product.mau_sac }}
                    </div>
                </div>

                <div class="col-span-2">
                    <p class="text-gray-500 mb-1">Thể loại</p>

                    <div class="border rounded-lg p-3 bg-gray-50">
                        {{ product.the_loai?.ten_the_loai }}
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button
                    @click="$inertia.visit('/product')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition"
                >
                    Quay lại
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";

const product = ref({});

const props = defineProps({
    id: Number,
});

function getDetail() {
    axios
        .get(`/api/index/${props.id}`)
        .then((response) => {
            product.value = response.data;
        })
        .catch((error) => {
            console.log(error);
        });
}

onMounted(() => {
    getDetail();
});
</script>
