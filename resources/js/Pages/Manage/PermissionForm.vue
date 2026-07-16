<template>
    <div class="bg-white rounded-xl p-6 w-[600px] z-50">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold mb-5">
                {{ props.permission ? "Sửa quyền" : "Thêm quyền" }}
            </h2>
            <button @click="emit('close')">✕</button>
        </div>

        <form @submit.prevent="save">
            <div class="mb-3">
                <label class="block mb-1">Tên quyền</label>
                <input v-model="form.name" class="border p-2 w-full" />
                <p v-if="form.errors?.name" class="text-red-500 text-xs mt-1">
                    {{ form.errors.name[0] }}
                </p>
            </div>

            <div class="mb-5">
                <label class="block mb-1">Nhóm</label>
                <input v-model="form.group" class="border p-2 w-full" />
                <p v-if="form.errors?.group" class="text-red-500 text-xs mt-1">
                    {{ form.errors.group[0] }}
                </p>
            </div>

            <div class="mb-5">
                <label class="block mb-1">Mô tả</label>
                <input v-model="form.description" class="border p-2 w-full" />
                <p
                    v-if="form.errors?.description"
                    class="text-red-500 text-xs mt-1"
                >
                    {{ form.errors.description[0] }}
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="border px-4 py-2"
                    @click="emit('close')"
                >
                    Hủy
                </button>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2">
                    Lưu
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, watch, defineProps, defineEmits } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const props = defineProps({
    permission: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const form = reactive({
    name: "",
    group: "",
    description: "",
    errors: {}, // ← Đã thêm để tránh lỗi
});

// Watch để cập nhật form khi mở modal sửa
watch(
    () => props.permission,
    (permission) => {
        if (permission) {
            form.name = permission.name || "";
            form.group = permission.group || "";
            form.description = permission.description || "";
            form.errors = {};
        } else {
            form.name = "";
            form.group = "";
            form.description = "";
            form.errors = {};
        }
    },
    { immediate: true },
);

function save() {
    form.errors = {}; // Reset lỗi trước khi submit

    if (props.permission?.id) {
        // Sửa quyền
        axios
            .put(`/api/permissions/${props.permission.id}`, form)
            .then(() => {
                toast.success("Cập nhật quyền thành công!", {
                    position: "top-right",
                    autoClose: 3000,
                });
                emit("saved");
                emit("close");
            })
            .catch((err) => {
                if (err.response?.data?.errors) {
                    form.errors = err.response.data.errors;
                }
            });
    } else {
        // Thêm mới quyền
        axios
            .post("/api/permissions", form)
            .then(() => {
                toast.success("Thêm quyền thành công!", {
                    position: "top-right",
                    autoClose: 3000,
                });
                emit("saved");
                emit("close");
            })
            .catch((err) => {
                if (err.response?.data?.errors) {
                    form.errors = err.response.data.errors;
                }
            });
    }
}
</script>
