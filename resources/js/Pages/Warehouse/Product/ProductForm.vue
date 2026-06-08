<template>
    <div
        class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 z-50"
    >
        <!-- Header -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-xl font-bold">
                {{ form.id ? "Cập nhật sản phẩm" : "Thêm sản phẩm" }}
            </h2>

            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-black"
            >
                ✕
            </button>
        </div>

        <form @submit.prevent="saveProduct" class="space-y-5">
            <!-- Tên + Số lượng -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tên sản phẩm -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tên sản phẩm <span class="text-red">*</span>
                    </label>

                    <input
                        v-model="form.ten"
                        type="text"
                        placeholder="Nhập tên sản phẩm"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    />

                    <p v-if="form.errors.ten" class="text-red-500 text-sm mt-1">
                        {{ form.errors.ten }}
                    </p>
                </div>

                <!-- Số lượng -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Số lượng
                    </label>

                    <input
                        v-model="form.so_luong"
                        type="number"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    />

                    <p
                        v-if="form.errors.so_luong"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ form.errors.so_luong }}
                    </p>
                </div>
            </div>

            <!-- Màu + Giá -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Màu sắc -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Màu sắc
                    </label>

                    <input
                        v-model="form.mau_sac"
                        type="text"
                        placeholder="VD: Đỏ, xanh..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    />
                </div>

                <!-- Giá -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Giá
                    </label>

                    <input
                        v-model="form.gia"
                        type="number"
                        placeholder="0"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    />
                </div>
            </div>

            <!-- Thể loại -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Thể loại sản phẩm <span class="text-red">*</span>
                </label>

                <select
                    v-model="form.id_the_loai"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                >
                    <option value="">-- Chọn thể loại --</option>

                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.ten_the_loai }}
                    </option>
                </select>

                <p
                    v-if="form.errors.id_the_loai"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ form.errors.id_the_loai }}
                </p>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                >
                    {{ form.id ? "Cập nhật" : "Lưu" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, watch, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const categories = ref([]);

/**
 * Inertia form (GIỐNG warehouseForm)
 */
const form = useForm({
    ten: "",
    so_luong: "",
    mau_sac: "",
    gia: "",
    id_the_loai: "",
});

/**
 * Watch edit data
 */
watch(
    () => props.product,
    (product) => {
        if (!product) {
            form.reset();
            return;
        }
        form.id = product.id;
        form.ten = product.ten;
        form.so_luong = product.so_luong;
        form.mau_sac = product.mau_sac;
        form.gia = product.gia;
        form.id_the_loai = product.id_the_loai;
    },
    { immediate: true },
);

/**
 * Save
 */
function saveProduct() {
    const request = form.id
        ? axios.put(`/api/warehouse/products/${form.id}`, form.data)
        : axios.post("/api/warehouse/products", form.data);

    request.then(() => {
        emit("saved");
        emit("close");
    });
}

/**
 * Load categories
 */
function getCategories() {
    axios.get("/api/theloai").then((res) => {
        categories.value = res.data;
    });
}
onMounted(() => {
    getCategories();
});
console.log("FORM DATA:", form.data());
</script>
