<template>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl p-6 z-50">
        <div class="border-b pb-4 mb-5">
            <h2 class="text-xl font-bold">
                {{ bank?.id ? "Cập nhật ngân hàng" : "Thêm ngân hàng" }}
            </h2>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <!-- LOGO -->
                <div class="flex flex-col items-center mb-6">
                    <label class="text-sm font-medium mb-2"
                        >Logo ngân hàng</label
                    >

                    <img
                        v-if="preview"
                        :src="preview"
                        class="w-20 h-20 rounded-full object-cover border"
                    />

                    <div
                        v-else
                        class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border"
                    >
                        <span class="text-xs text-gray-400">No Logo</span>
                    </div>

                    <input
                        type="file"
                        accept="image/*"
                        @change="handleImage"
                        class="mt-3"
                    />
                </div>

                <div>
                    <label> Tên viết tắt </label>

                    <input
                        v-model="form.short_name"
                        class="w-full border rounded-lg px-3 py-2"
                    />
                </div>

                <div class="col-span-2">
                    <label> Tên ngân hàng </label>

                    <input
                        v-model="form.name"
                        class="w-full border rounded-lg px-3 py-2"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 border rounded-lg"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg"
                >
                    Lưu
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, watch, ref } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const props = defineProps({
    bank: Object,
});

const emit = defineEmits(["saved", "close"]);

const form = reactive({
    code: "",
    name: "",
    short_name: "",
});

const file = ref(null);
const preview = ref(null);

/* IMAGE HANDLER */
function handleImage(e) {
    const f = e.target.files[0];
    if (!f) return;

    file.value = f;
    preview.value = URL.createObjectURL(f);
}

/* LOAD EDIT DATA */
watch(
    () => props.bank,
    (val) => {
        if (!val) return;

        Object.assign(form, {
            code: val.code,
            name: val.name,
            short_name: val.short_name,
        });

        preview.value = val.logo ? `/storage/${val.logo}` : null;
    },
    { immediate: true },
);

/* SUBMIT */
async function submit() {
    const data = new FormData();

    Object.keys(form).forEach((key) => {
        data.append(key, form[key] || "");
    });

    if (file.value) {
        data.append("logo", file.value);
    }

    if (props.bank?.id) {
        await axios.post(
            `/api/accountant/banks/${props.bank.id}?_method=PUT`,
            data,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            },
        );
    } else {
        await axios.post("/api/accountant/banks", data, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
    }
    toast.success("Lưu ngân hàng thành công!", {
        position: "top-right",
        autoClose: 3000,
    });
    emit("saved");
}
</script>
