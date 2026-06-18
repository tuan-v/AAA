<template>
    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-xl p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                {{ form.id ? "Cập nhật đơn vị" : "Thêm đơn vị" }}
            </h2>

            <button
                @click="$emit('close')"
                class="text-gray-400 hover:text-red-500 text-lg"
            >
                ✕
            </button>
        </div>

        <form @submit.prevent="save" class="space-y-5">
            <!-- Tên đơn vị -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Tên đơn vị
                    <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Nhập tên đơn vị"
                    :class="[
                        'w-full rounded-lg border px-4 py-2.5 outline-none transition',
                        form.errors.name
                            ? 'border-red-500'
                            : 'border-gray-300 focus:border-blue-500',
                    ]"
                />

                <p v-if="form.errors.name" class="text-red-500 text-xs mt-2">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Mã  -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Ký hiệu đơn vị
                </label>

                <input
                    v-model="form.symbol"
                    type="text"
                    placeholder="VD:kg,m..."
                    :class="[
                        'w-full rounded-lg border px-4 py-2.5 outline-none transition',
                        form.errors.symbol
                            ? 'border-red-500'
                            : 'border-gray-300 focus:border-blue-500',
                    ]"
                />

                <p v-if="form.errors.symbol" class="text-red-500 text-xs mt-2">
                    {{ form.errors.symbol }}
                </p>
            </div>

            <!-- Mô tả -->
            <!-- <div>
                <label class="block text-sm font-medium mb-2"> Mô tả </label>

                <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Nhập mô tả..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 outline-none focus:border-blue-500"
                />
            </div> -->

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
                    {{ form.errors.status }}
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

const props = defineProps({
    unit: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const form = useForm({
    name: "",
    symbol: "",
    status: "active",
});

watch(
    () => props.unit,
    (unit) => {
        if (!unit) {
            form.reset();
            form.clearErrors();
            return;
        }
        form.id = unit.id;
        form.name = unit.name;
        form.symbol = unit.symbol;
        form.status = unit.status;
    },
    { immediate: true },
);

async function save() {
    try {
        form.clearErrors();

        if (form.id) {
            await axios.put(`/api/warehouse/units/${form.id}`, form.data());
        } else {
            await axios.post("/api/warehouse/units", form.data());
        }

        emit("saved");
        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            form.setError(error.response.data.errors);
        }
    }
}
</script>
