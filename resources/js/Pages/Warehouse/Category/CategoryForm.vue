<template>
    <div class="bg-white p-6 rounded-lg w-full max-w-xl">
        <h2 class="text-xl font-bold mb-4">
            {{ category ? "Cập nhật danh mục" : "Thêm danh mục" }}
        </h2>

        <form @submit.prevent="save">
            <div class="mb-4">
                <label class="block mb-2">Tên danh mục</label>
                <input v-model="form.name" class="w-full border p-2 rounded" />
            </div>

            <div class="mb-4">
                <label class="block mb-2">Mô tả</label>
                <textarea
                    v-model="form.description"
                    class="w-full border p-2 rounded"
                ></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="$emit('close')">Hủy</button>

                <button class="bg-blue-600 text-white px-4 py-2 rounded">
                    {{ category ? "Cập nhật" : "Lưu" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, watch } from "vue";
import axios from "axios";

const props = defineProps({
    category: Object,
});

const emit = defineEmits(["saved", "close"]);

const form = reactive({
    name: "",
    description: "",
});

watch(
    () => props.category,
    (val) => {
        if (val) {
            form.name = val.name;
            form.description = val.description;
        } else {
            form.name = "";
            form.description = "";
        }
    },
    { immediate: true },
);

function save() {
    if (props.category?.id) {
        axios.put(`/api/categories/${props.category.id}`, form).then(() => {
            emit("saved");
            emit("close");
        });
    } else {
        axios.post("/api/categories", form).then(() => {
            emit("saved");
            emit("close");
        });
    }
}
</script>
