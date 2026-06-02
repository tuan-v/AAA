<template>
    <div class="bg-white rounded-xl p-6 w-[600px] z-50">
        <h2 class="text-xl font-bold mb-5">
            {{ isEdit ? "Sửa Permission" : "Thêm Permission" }}
        </h2>

        <form @submit.prevent="save">
            <div class="mb-3">
                <label class="block mb-1">Tên Permission</label>
                <input v-model="form.name" class="border p-2 w-full" />
            </div>

            <div class="mb-5">
                <label class="block mb-1">Nhóm</label>
                <input v-model="form.group" class="border p-2 w-full" />
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
import { reactive, watch, computed } from "vue";
import axios from "axios";

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
});

const isEdit = computed(() => !!props.permission);

// ✅ QUAN TRỌNG: watch để cập nhật form khi mở modal
watch(
    () => props.permission,
    (val) => {
        if (val) {
            form.name = val.name ?? "";
            form.group = val.group ?? "";
        } else {
            form.name = "";
            form.group = "";
        }
    },
    { immediate: true },
);

async function save() {
    try {
        if (isEdit.value) {
            await axios.put(`/api/permissions/${props.permission.id}`, form);
        } else {
            await axios.post("/api/permissions", form);
        }

        emit("saved");
        emit("close");
    } catch (error) {
        console.error(error);
    }
}
</script>
