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
                <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Tỉnh + Xã -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tỉnh / Thành phố <span class="text-red">*</span>
                    </label>

                    <select
                        v-model="form.province_id"
                        @change="onProvinceChange"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                    >
                        <option value="">-- Chọn tỉnh thành --</option>

                        <option
                            v-for="province in provinces"
                            :key="province.id"
                            :value="province.id"
                        >
                            {{ province.name }}
                        </option>
                        <p
                            v-if="form.errors.name"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.name }}
                        </p>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Xã / Phường <span class="text-red">*</span>
                    </label>

                    <select
                        v-model="form.ward_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                    >
                        <option value="">-- Chọn xã phường --</option>

                        <option
                            v-for="ward in wards"
                            :key="ward.id"
                            :value="ward.id"
                        >
                            {{ ward.name }}
                        </option>
                    </select>
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
import { ref, reactive, watch, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
const provinces = ref([]);
const wards = ref([]);
const props = defineProps({
    warehouse: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const form = useForm({
    name: "",
    province_id: "",
    ward_id: "",
    address_detail: "",
    total_inventory_value: 0,
});

watch(
    () => props.warehouse,
    async (warehouse) => {
        if (!warehouse) {
            form.name = "";
            form.province_id = "";
            form.ward_id = "";
            form.address_detail = "";
            form.total_inventory_value = 0;
            wards.value = [];
            return;
        }

        form.name = warehouse.name;
        form.province_id = warehouse.province_id;
        form.ward_id = warehouse.ward_id;
        form.address_detail = warehouse.address_detail;
        form.total_inventory_value = warehouse.total_inventory_value;

        if (warehouse.province_id) {
            const res = await axios.get(
                `/api/provinces/${warehouse.province_id}/wards`,
            );
            wards.value = res.data;
        }
    },
    { immediate: true },
);

function saveWarehouse() {
    if (props.warehouse?.id) {
        form.put(`/api/warehouses/${props.warehouse.id}`, {
            onSuccess: () => {
                emit("saved");
                emit("close");
            },
        });
    } else {
        form.post("/api/warehouses", {
            onSuccess: () => {
                emit("saved");
                emit("close");
            },
        });
    }
}
function getProvinces() {
    axios.get("/api/provinces").then((response) => {
        provinces.value = response.data;
    });
}
function onProvinceChange() {
    axios.get(`/api/provinces/${form.province_id}/wards`).then((res) => {
        wards.value = res.data;
    });
}
function getWards() {
    if (!form.province_id) {
        wards.value = [];
        return;
    }

    axios.get(`/api/provinces/${form.province_id}/wards`).then((response) => {
        wards.value = response.data;
    });
}
onMounted(() => {
    getProvinces();
});
</script>
