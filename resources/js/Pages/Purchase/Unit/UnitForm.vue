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
                    {{ form.errors.name[0] }}
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
                    {{ form.errors.symbol[0] }}
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

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer">
                <input v-model="form.allow_decimal" type="checkbox" class="mt-1 h-4 w-4" />
                <span>
                    <span class="block text-sm font-medium">Cho phép nhập số lượng lẻ</span>
                    <span class="block text-xs text-gray-500">Ví dụ: kg, lít có thể nhập 1,5. Bỏ chọn với cái, hộp, chiếc.</span>
                </span>
            </label>

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
import { getValidationMessage } from "@/config/helpers";

const props = defineProps({
    apiBase: {
        type: String,
        default: "/api/purchase/units",
    },
    unit: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const form = useForm({
    name: "",
    symbol: "",
    allow_decimal: false,
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
        form.allow_decimal = Boolean(unit.allow_decimal);
        form.status = unit.status;
    },
    { immediate: true },
);

async function save() {
    try {
        form.clearErrors();

        // FIX: trước đây không lưu kết quả axios vào biến nào cả, nên
        // emit("saved") luôn bắn đi không kèm dữ liệu -> ProductForm nhận
        // "newUnit" là undefined -> newUnit.id lỗi -> không tự fill được.
        let res;

        if (form.id) {
            res = await axios.put(
                `${props.apiBase}/${form.id}`,
                form.data(),
            );
        } else {
            res = await axios.post(props.apiBase, form.data());
            toast.success("Thêm đơn vị thành công");
        }

        // API chuẩn của dự án trả về { success, message, data, meta }
        // -> object đơn vị vừa tạo/sửa nằm ở res.data.data
        emit("saved", res.data);
        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            form.setError(error.response.data.errors);
            toast.error(getValidationMessage(error));
        } else {
            toast.error(getValidationMessage(error, "Không thể lưu đơn vị tính."));
        }
    }
}
</script>
