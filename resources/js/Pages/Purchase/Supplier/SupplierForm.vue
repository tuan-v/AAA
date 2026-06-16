<template>
    <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-5xl p-6 z-50"
    >
        <!-- HEADER -->
        <div
            class="flex justify-between items-center border-b border-gray-100 pb-4 mb-5"
        >
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{
                        form.id ? "Cập nhật nhà cung cấp" : "Thêm nhà cung cấp"
                    }}
                </h2>
                <p class="text-sm text-gray-500">
                    Quản lý thông tin nhà cung cấp
                </p>
            </div>

            <button
                @click="$emit('close')"
                class="w-9 h-9 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-500"
            >
                ✕
            </button>
        </div>

        <!-- FORM -->
        <div class="grid grid-cols-3 gap-4">
            <!-- TÊN NCC -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Tên nhà cung cấp
                    <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Nhập tên nhà cung cấp"
                    class="w-full h-11 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.name" class="text-red-500 text-sm mt-1">
                    {{ errors.name[0] }}
                </p>
            </div>

            <!-- MÃ -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Mã nhà cung cấp
                </label>

                <div class="flex gap-2">
                    <input
                        v-model="form.code"
                        type="text"
                        placeholder="NCC0001"
                        class="flex-1 h-11 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />

                    <button
                        type="button"
                        @click="generateCode"
                        class="px-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                    >
                        Roll
                    </button>
                </div>

                <p v-if="errors.code" class="text-red-500 text-sm mt-1">
                    {{ errors.code[0] }}
                </p>
            </div>

            <!-- TIỀN TỆ -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Tiền tệ<span class="text-red">*</span>
                </label>

                <select
                    v-model="form.currency_id"
                    class="w-full h-11 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Chọn tiền tệ</option>

                    <option v-for="c in currencies" :key="c.id" :value="c.id">
                        {{ c.code }}
                    </option>
                </select>
                <p v-if="errors.currency_id" class="text-red-500 text-sm mt-1">
                    {{ errors.currency_id[0] }}
                </p>
            </div>

            <!-- PHONE -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Số điện thoại<span class="text-red">*</span>
                </label>

                <input
                    v-model="form.phone"
                    type="text"
                    placeholder="090xxxxxxx"
                    class="w-full h-11 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.phone" class="text-red-500 text-sm mt-1">
                    {{ errors.phone[0] }}
                </p>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Email<span class="text-red">*</span>
                </label>

                <input
                    v-model="form.email"
                    type="email"
                    placeholder="example@gmail.com"
                    class="w-full h-11 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.email" class="text-red-500 text-sm mt-1">
                    {{ errors.email[0] }}
                </p>
            </div>

            <div></div>

            <!-- TỈNH -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Tỉnh / Thành phố<span class="text-red">*</span>
                </label>

                <FormSelect
                    v-model="selectedProvince"
                    :options="provinceOptions"
                    placeholder="Chọn tỉnh..."
                    searchable
                    @change="onProvinceChange"
                />
                <p v-if="errors.province_id" class="text-red-500 text-sm mt-1">
                    {{ errors.province_id[0] }}
                </p>
            </div>

            <!-- PHƯỜNG -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Phường / Xã<span class="text-red">*</span>
                </label>

                <FormSelect
                    v-model="selectedWard"
                    :options="wardOptions"
                    placeholder="Chọn phường..."
                    searchable
                />
                <p v-if="errors.ward_id" class="text-red-500 text-sm mt-1">
                    {{ errors.ward_id[0] }}
                </p>
            </div>

            <div></div>

            <!-- ĐỊA CHỈ -->
            <div class="col-span-3">
                <label class="block text-sm font-medium mb-1">
                    Địa chỉ chi tiết<span class="text-red">*</span>
                </label>

                <textarea
                    rows="2"
                    v-model="addressDetail"
                    placeholder="Số nhà, tên đường..."
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <p
                    v-if="errors.address_detail"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ errors.address_detail[0] }}
                </p>
            </div>

            <!-- CÔNG NỢ -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Công nợ đầu kỳ
                </label>

                <input
                    :value="formatMoney(form.total_debts)"
                    @input="handleMoney($event, 'total_debts')"
                    class="w-full h-11 px-3 border border-gray-200 rounded-lg text-right font-medium focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="errors.total_debts" class="text-red-500 text-sm mt-1">
                    {{ errors.total_debts[0] }}
                </p>
            </div>

            <!-- TẠM ỨNG -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Tạm ứng đầu kỳ
                </label>

                <input
                    :value="formatMoney(form.total_advance)"
                    @input="handleMoney($event, 'total_advance')"
                    class="w-full h-11 px-3 border border-gray-200 rounded-lg text-right font-medium focus:ring-2 focus:ring-blue-500"
                />
                <p
                    v-if="errors.total_advance"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ errors.total_advance[0] }}
                </p>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button
                @click="$emit('close')"
                class="px-5 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                Hủy
            </button>

            <button
                @click="submit"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
            >
                Lưu nhà cung cấp
            </button>
        </div>
    </div>
    ```
</template>

<script setup>
import axios from "axios";
import { reactive, ref, watch, computed, onMounted } from "vue";
import { formatMoney, removeMoneyFormat } from "@/config/helpers";
import FormSelect from "@/components/FormSelect.vue";
const props = defineProps({
    supplier: Object,
    currencies: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["saved", "close"]);
const errors = ref({});
const provinces = ref([]);
const wards = ref([]);
const currencies = ref([]);

const selectedProvince = ref("");
const selectedWard = ref("");

const addressDetail = ref("");

const form = reactive({
    id: null,
    name: "",
    code: "",
    phone: "",
    email: "",
    currency_id: "",
    address: "",
    total_debts: 0,
    total_advance: 0,
    status: "active",
});

watch(
    () => props.supplier,
    async (supplier) => {
        if (!supplier) return;

        Object.assign(form, supplier);

        selectedProvince.value = supplier.province_code;
        addressDetail.value = supplier.address_detail;

        if (supplier.province_code) {
            await loadWards(supplier.province_code);

            selectedWard.value = supplier.ward_code;
        }
    },
    { immediate: true },
);

const provinceOptions = computed(() => {
    return provinces.value.map((p) => ({
        value: p.code,
        label: p.name,
    }));
});
const wardOptions = computed(() => {
    return wards.value.map((w) => ({
        value: w.code,
        label: w.name,
    }));
});
async function fetchProvinces() {
    const res = await axios.get("/api/provinces");
    provinces.value = res.data;
}
async function loadWards(provinceCode) {
    if (!provinceCode) {
        wards.value = [];
        return;
    }
    const res = await axios.get(`/api/provinces/${provinceCode}/wards`);
    wards.value = res.data;
}
const onProvinceChange = (value) => {
    selectedWard.value = "";
    loadWards(value);
};

function handleMoney(event, field) {
    form[field] = removeMoneyFormat(event.target.value);
}
function generateCode() {
    const random = Math.floor(Math.random() * 9999)
        .toString()
        .padStart(4, "0");

    form.code = `NCC${random}`;
}
async function submit() {
    errors.value = {};
    try {
        const province =
            provinces.value.find((x) => x.code == selectedProvince.value)
                ?.name || "";

        const payload = {
            ...form,
            province_id: selectedProvince.value,
            ward_id: selectedWard.value,
            address_detail: addressDetail.value,
        };

        let supplier;

        if (form.id) {
            const res = await axios.put(
                `/api/purchase/suppliers/${form.id}`,
                payload,
            );

            supplier = res.data.data ?? res.data;
        } else {
            const res = await axios.post("/api/purchase/suppliers", payload);

            supplier = res.data.data ?? res.data;
        }

        emit("saved", supplier);
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
            return;
        }

        console.error(error);
    }
}

onMounted(async () => {
    const res = await axios.get("/api/currencies");
    currencies.value = res.data || [];
});
onMounted(() => {
    fetchProvinces();
});
</script>
