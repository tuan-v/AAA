<template>
    <div
        class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 z-50"
    >
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">
                {{ props.product ? "Cập nhật sản phẩm" : "Thêm sản phẩm" }}
            </h2>

            <button @click="$emit('close')">✕</button>
        </div>

        <form @submit.prevent="saveProduct">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Tên sản phẩm</label>
                    <input
                        v-model="form.ten"
                        class="w-full border rounded p-2"
                    />
                </div>

                <div>
                    <label>Số lượng</label>
                    <input
                        v-model="form.so_luong"
                        type="number"
                        class="w-full border rounded p-2"
                    />
                </div>

                <div>
                    <label>Màu sắc</label>
                    <input
                        v-model="form.mau_sac"
                        class="w-full border rounded p-2"
                    />
                </div>

                <div>
                    <label>Giá</label>
                    <input
                        v-model="form.gia"
                        type="number"
                        class="w-full border rounded p-2"
                    />
                </div>
                <div>
                    <label>Thể loại</label>

                    <select
                        v-model="form.id_the_loai"
                        class="w-full border rounded p-2"
                    >
                        <option value="">Chọn thể loại</option>

                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.ten_the_loai }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="border px-4 py-2 rounded"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded"
                >
                    Lưu
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { reactive, watch, ref, onMounted } from "vue";

const categories = ref([]);
const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const form = reactive({
    ten: "",
    so_luong: "",
    mau_sac: "",
    gia: "",
    id_the_loai: "",
});

watch(
    () => props.product,
    (product) => {
        if (product) {
            form.ten = product.ten;
            form.so_luong = product.so_luong;
            form.mau_sac = product.mau_sac;
            form.gia = product.gia;
            form.id;
            form.id_the_loai = product.id_the_loai;
        } else {
            form.ten = "";
            form.so_luong = "";
            form.mau_sac = "";
            form.gia = "";
            form.id_the_loai = "";
        }
    },
    { immediate: true },
);

function saveProduct() {
    if (props.product?.id) {
        axios.put(`/api/index/${props.product.id}`, form).then(() => {
            emit("saved");
            emit("close");
        });
    } else {
        axios.post("/api/index", form).then(() => {
            emit("saved");
            emit("close");
        });
    }
}
function getCategories() {
    axios.get("/api/theloai").then((response) => {
        categories.value = response.data;
    });
}

onMounted(() => {
    getCategories();
});
</script>
