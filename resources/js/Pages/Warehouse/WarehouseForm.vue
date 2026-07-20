<template>
    <div
        class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 z-50"
    >
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">
                {{ warehouse ? "Cập nhật kho hàng" : "Thêm kho hàng" }}
            </h2>

            <button @click="$emit('close')">✕</button>
        </div>

        <form @submit.prevent="saveWarehouse" class="space-y-5">
            <!-- Tên kho -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tên kho
                    <span class="text-red">*</span>
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Nhập tên kho"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                />
                <p v-if="errors?.name?.length" class="text-red-500 text-sm mt-1">
                    {{ errors.name[0] }}
                </p>
            </div>

            <!-- Tỉnh + Xã -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tỉnh / Thành phố <span class="text-red">*</span>
                    </label>

                    <FormSelect
                        v-model="form.province_code"
                        :options="provinceOptions"
                        label=""
                        value-key="id"
                        placeholder="-- Chọn tỉnh thành --"
                        searchable
                    />

                    <p
                        v-if="errors?.province_code?.length"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ errors.province_code[0] }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Xã / Phường <span class="text-red">*</span>
                    </label>

                    <FormSelect
                        v-model="form.ward_code"
                        :options="wardOptions"
                        value-key="id"
                        placeholder="-- Chọn xã phường --"
                        searchable
                    />

                    <p
                        v-if="errors?.ward_code?.length"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ errors.ward_code[0] }}
                    </p>
                </div>
            </div>

            <!-- Địa chỉ -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Địa chỉ chi tiết <span class="text-red">*</span>
                </label>

                <input
                    v-model="form.address_detail"
                    type="text"
                    placeholder="Số nhà, đường, thôn..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                />
                <p
                    v-if="errors?.address_detail?.length"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ errors.address_detail[0] }}
                </p>
            </div>

            <!-- Giá trị tồn -->

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
                >
                    {{ warehouse ? "Cập nhật" : "Lưu kho" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, reactive, watch, onMounted, computed, nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import FormSelect from "../../components/FormSelect.vue";
const provinceOptions = computed(() =>
    (Array.isArray(provinces.value) ? provinces.value : []).filter(Boolean).map((item) => ({
        value: item.id,
        label: item.name ?? "",
    })),
);
const wardOptions = computed(() =>
    (Array.isArray(wards.value) ? wards.value : []).filter(Boolean).map((item) => ({
        value: item.id,
        label: item.name ?? "",
    })),
);
const provinces = ref([]);
const wards = ref([]);
const props = defineProps({
    warehouse: {
        type: Object,
        default: null,
    },
});
const loading = ref(false);
const hydratingWarehouse = ref(false);
const emit = defineEmits(["saved", "close"]);
const errors = ref({});
const form = reactive({
    id: null,
    name: "",
    province_code: "",
    ward_code: "",
    address_detail: "",
    total_inventory_value: 0,
});

watch(
    () => props.warehouse,
    async (warehouse) => {
        hydratingWarehouse.value = true;
        if (!warehouse) {
            form.name = "";
            form.province_code = "";
            form.ward_code = "";
            form.address_detail = "";
            form.total_inventory_value = 0;
            wards.value = [];
            await nextTick();
            hydratingWarehouse.value = false;
            return;
        }
        form.id = warehouse.id;
        form.name = warehouse.name;
        form.province_code = warehouse.province_code;
        form.ward_code = warehouse.ward_code;
        form.address_detail = warehouse.address_detail;
        form.total_inventory_value = warehouse.total_inventory_value;

        if (warehouse.province_code) {
            const res = await axios.get(
                `/api/provinces/${warehouse.province_code}/wards`,
            );
            wards.value = res.data;
        }
        await nextTick();
        hydratingWarehouse.value = false;
    },
    { immediate: true },
);

async function saveWarehouse() {
    loading.value = true;
    errors.value = {};

    try {
        const payload = {
            name: form.name,
            province_code: form.province_code,
            ward_code: form.ward_code,
            address_detail: form.address_detail,
            total_inventory_value: form.total_inventory_value,
        };

        let res;

        if (form.id) {
            res = await axios.put(`/api/warehouses/${form.id}`, payload);

            toast.success("Cập nhật kho thành công", {
                autoClose: 2000,
            });
        } else {
            res = await axios.post("/api/warehouses", payload);

            toast.success("Tạo kho thành công", {
                autoClose: 2000,
            });
        }

        // Trả warehouse về component cha
        emit("saved", res.data.data ?? res.data);

        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response?.data?.errors ?? {};

            toast.error("Vui lòng kiểm tra lại dữ liệu");
        } else {
            console.error(error);

            toast.error(error.response?.data?.message || "Có lỗi xảy ra");
        }
    } finally {
        loading.value = false;
    }
}
async function getProvinces() {
    try {
        const res = await axios.get("/api/provinces");

        provinces.value = Array.isArray(res.data) ? res.data : [];
    } catch (error) {
        console.error(error);
    }
}
async function getWards(provinceId) {
    if (!provinceId) {
        wards.value = [];
        return;
    }

    try {
        const res = await axios.get(`/api/provinces/${provinceId}/wards`);

        wards.value = Array.isArray(res.data) ? res.data : [];
    } catch (error) {
        console.error(error);
    }
}
watch(
    () => form.province_code,
    async (value) => {
        if (hydratingWarehouse.value) {
            return;
        }
        if (!value) {
            form.ward_code = "";
            wards.value = [];
            return;
        }

        form.ward_code = "";

        await getWards(value);
    },
);
onMounted(() => {
    getProvinces();
});
</script>
