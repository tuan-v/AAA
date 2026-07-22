<template>
    <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    {{ props.permission ? "Cập nhật quyền" : "Thêm quyền" }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">Khai báo mã quyền và nhóm chức năng trong hệ thống.</p>
            </div>
            <button type="button" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" @click="emit('close')">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form novalidate class="flex min-h-0 flex-1 flex-col" @submit.prevent="save">
            <div class="space-y-5 overflow-y-auto px-6 py-5">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tên quyền <span class="text-red-500">*</span></label>
                <input v-model.trim="form.name" required placeholder="Ví dụ: san_pham.xem" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" :class="form.errors?.name ? 'border-red-400' : ''" />
                <p v-if="form.errors?.name" class="text-red-500 text-xs mt-1">
                    {{ form.errors.name[0] }}
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nhóm quyền <span class="text-red-500">*</span></label>
                <input v-model.trim="form.group" required placeholder="Ví dụ: Sản phẩm" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" :class="form.errors?.group ? 'border-red-400' : ''" />
                <p v-if="form.errors?.group" class="text-red-500 text-xs mt-1">
                    {{ form.errors.group[0] }}
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Mô tả</label>
                <textarea v-model.trim="form.description" rows="3" placeholder="Mô tả ngắn mục đích của quyền" class="w-full resize-none rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" :class="form.errors?.description ? 'border-red-400' : ''"></textarea>
                <p
                    v-if="form.errors?.description"
                    class="text-red-500 text-xs mt-1"
                >
                    {{ form.errors.description[0] }}
                </p>
            </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                <button
                    type="button"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                    @click="emit('close')"
                >
                    Hủy
                </button>

                <button type="submit" :disabled="saving" class="inline-flex min-w-28 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    {{ saving ? "Đang lưu..." : "Lưu thay đổi" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, watch, defineProps, defineEmits } from "vue";
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
const saving = ref(false);

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

async function save() {
    form.errors = {}; // Reset lỗi trước khi submit
    saving.value = true;
    try {
        if (props.permission?.id) {
            await axios.put(`/api/permissions/${props.permission.id}`, form);
        } else {
            await axios.post("/api/permissions", form);
        }
        toast.success(props.permission?.id ? "Cập nhật quyền thành công!" : "Thêm quyền thành công!");
        emit("saved");
        emit("close");
    } catch (err) {
        form.errors = err.response?.data?.errors || {};
        if (!err.response?.data?.errors) {
            toast.error(err.response?.data?.message || "Không thể lưu quyền");
        }
    } finally {
        saving.value = false;
    }
}
</script>
