<template>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-xl p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                {{ form.id ? "Cập nhật danh mục" : "Thêm danh mục" }}
            </h2>

            <button
                @click="$emit('close')"
                class="text-gray-400 hover:text-red-500 text-lg"
            >
                ✕
            </button>
        </div>

        <form @submit.prevent="save" class="space-y-5">
            <!-- Tên danh mục -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Tên danh mục
                    <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Nhập tên danh mục"
                    :class="[
                        'w-full rounded-lg border px-4 py-2.5 outline-none transition',
                        form.errors.name
                            ? 'border-red-500'
                            : 'border-gray-300 focus:border-blue-500',
                    ]"
                />

                <p v-if="form.errors.name" class="text-red-500 text-xs mt-2">
                    {{ form.errors.name[0] }}
                </p>
            </div>

            <!-- Mô tả -->
            <div>
                <label class="block text-sm font-medium mb-2"> Mô tả </label>

                <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Nhập mô tả..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 outline-none focus:border-blue-500"
                />
            </div>

            <!-- Trạng thái -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Trạng thái
                    <span class="text-red-500">*</span>
                </label>

                <select
                    v-model="form.status"
                    :class="[
                        'w-full rounded-lg border px-4 py-2.5 outline-none',
                        form.errors.status
                            ? 'border-red-500'
                            : 'border-gray-300 focus:border-blue-500',
                    ]"
                >
                    <option value="active">Hoạt động</option>

                    <option value="inactive">Ngừng hoạt động</option>
                </select>

                <p v-if="form.errors.status" class="text-red-500 text-xs mt-2">
                    {{ form.errors.status[0] }}
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
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const props = defineProps({
    category: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const form = useForm({
    name: "",
    code: "",
    description: "",
    status: "active",
});

watch(
    () => props.category,
    (category) => {
        if (!category) {
            form.reset();
            form.clearErrors();
            return;
        }

        form.id = category.id;
        form.name = category.name;
        form.code = category.code;
        form.description = category.description;
        form.status = category.status;
    },
    { immediate: true },
);

async function save() {
    try {
        form.clearErrors();

        let res;

        if (form.id) {
            res = await axios.put(
                `/api/warehouse/categories/${form.id}`,
                form.data(),
            );

            toast.success("Cập nhật danh mục thành công");
        } else {
            res = await axios.post("/api/warehouse/categories", form.data());

            toast.success("Thêm danh mục thành công");
        }
        emit("saved", res.data); // hoặc res.data nếu API trả trực tiếp object
        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            form.setError(error.response.data.errors);
        } else {
            toast.error("Có lỗi xảy ra");
        }
    }
}
</script>
