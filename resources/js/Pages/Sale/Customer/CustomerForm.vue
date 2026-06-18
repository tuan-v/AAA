<template>
    <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-5xl p-6 z-50"
    >
        <div class="flex justify-between items-center border-b pb-4 mb-5">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ form.id ? "Cập nhật khách hàng" : "Thêm khách hàng" }}
                </h2>
                <p v-if="form.code" class="text-sm text-blue-600 font-semibold">
                    Mã khách hàng: {{ form.code }}
                </p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <!-- TÊN -->

            <div>
                <label class="block text-sm font-medium mb-1">
                    Tên khách hàng <span class="text-red">*</span>
                </label>

                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border rounded-lg px-3 py-2"
                />

                <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                    {{ errors.name[0] }}
                </p>
            </div>

            <!-- PHONE -->

            <div>
                <label class="block text-sm font-medium mb-1">
                    Số điện thoại <span class="text-red">*</span>
                </label>

                <input
                    v-model="form.phone"
                    type="text"
                    class="w-full border rounded-lg px-3 py-2"
                />

                <p v-if="errors.phone" class="text-red-500 text-xs mt-1">
                    {{ errors.phone[0] }}
                </p>
            </div>

            <!-- EMAIL -->

            <div>
                <label class="block text-sm font-medium mb-1">
                    Email <span class="text-red">*</span>
                </label>

                <input
                    v-model="form.email"
                    type="email"
                    class="w-full border rounded-lg px-3 py-2"
                />
            </div>

            <!-- TIỀN TỆ -->

            <div>
                <label class="block text-sm font-medium mb-1">
                    Tiền tệ <span class="text-red">*</span>
                </label>

                <select
                    v-model="form.currency_id"
                    class="w-full border rounded-lg px-3 py-2"
                >
                    <option value="">Chọn tiền tệ</option>

                    <option
                        v-for="currency in currencies"
                        :key="currency.id"
                        :value="currency.id"
                    >
                        {{ currency.code }}
                    </option>
                </select>
            </div>

            <!-- TỈNH -->

            <div>
                <label class="block text-sm font-medium mb-1">
                    Tỉnh / Thành phố <span class="text-red">*</span>
                </label>

                <FormSelect
                    v-model="form.province_id"
                    :options="provinceOptions"
                    searchable
                    placeholder="Chọn tỉnh"
                />
            </div>

            <!-- PHƯỜNG -->

            <div>
                <label class="block text-sm font-medium mb-1">
                    Xã / Phường <span class="text-red">*</span>
                </label>

                <FormSelect
                    v-model="form.ward_id"
                    :options="wardOptions"
                    searchable
                    placeholder="Chọn xã/phường"
                />
            </div>

            <!-- ĐỊA CHỈ -->

            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">
                    Địa chỉ chi tiết <span class="text-red">*</span>
                </label>

                <textarea
                    v-model="form.address_detail"
                    rows="3"
                    class="w-full border rounded-lg px-3 py-2"
                />
            </div>

            <!-- CÔNG NỢ -->

            <div>
                <label class="block text-sm font-medium mb-1">
                    Công nợ đầu kỳ
                </label>

                <input
                    v-model="form.opening_debt"
                    type="number"
                    min="0"
                    class="w-full border rounded-lg px-3 py-2"
                />
            </div>

            <!-- TRẠNG THÁI -->
        </div>

        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
            <button @click="$emit('close')" class="px-4 py-2 border rounded-lg">
                Đóng
            </button>

            <button
                @click="save"
                :disabled="loading"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg"
            >
                {{ loading ? "Đang lưu..." : "Lưu" }}
            </button>
        </div>
    </div>
</template>
<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import axios from "axios";
import { computed } from "vue";
import FormSelect from "@/components/FormSelect.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const props = defineProps({
    customer: {
        type: Object,
        default: null,
    },

    currencies: {
        type: Array,
        default: () => [],
    },
});
const provinceOptions = computed(() =>
    provinces.value.map((item) => ({
        value: item.id,
        label: item.name,
    })),
);

const wardOptions = computed(() =>
    wards.value.map((item) => ({
        value: item.id,
        label: item.name,
    })),
);
const emit = defineEmits(["saved", "close"]);

const loading = ref(false);

const errors = ref({});

const provinces = ref([]);
const wards = ref([]);

const form = reactive({
    id: null,
    code: "",
    name: "",
    email: "",
    phone: "",

    currency_id: "",

    opening_debt: 0,

    province_id: "",
    ward_id: "",

    address_detail: "",

    status: "active",
});
function resetForm() {
    form.id = null;
    form.code = "";
    form.name = "";
    form.email = "";
    form.phone = "";
    form.currency_id = "";
    form.opening_debt = 0;
    form.province_id = "";
    form.ward_id = "";
    form.address_detail = "";
    form.status = "active";
}
watch(
    () => props.customer,
    async (customer) => {
        if (!customer) {
            resetForm();
            fetchNextCode(); // 👈 thêm cái này
            return;
        }

        form.id = customer.id;
        form.code = customer.code ?? "";
        form.name = customer.name ?? "";
        form.email = customer.email ?? "";
        form.phone = customer.phone ?? "";

        form.currency_id = customer.currency_id ?? "";

        form.opening_debt = customer.opening_debt ?? 0;

        form.province_id = customer.province_id ?? "";
        form.ward_id = customer.ward_id ?? "";

        form.address_detail = customer.address_detail ?? "";

        form.status = customer.status;

        if (form.province_id) {
            await fetchWards(form.province_id);
        }
    },
    { immediate: true },
);

async function fetchProvinces() {
    try {
        const res = await axios.get("/api/provinces");

        provinces.value = res.data;
    } catch (error) {
        console.error(error);
    }
}

async function fetchWards(provinceId) {
    if (!provinceId) {
        wards.value = [];
        return;
    }

    try {
        const res = await axios.get(`/api/provinces/${provinceId}/wards`);

        wards.value = res.data;
    } catch (error) {
        console.error(error);
    }
}

watch(
    () => form.province_id,
    async (value) => {
        if (!value) {
            form.ward_id = "";
            wards.value = [];
            return;
        }

        form.ward_id = "";

        await fetchWards(value);
    },
);
async function fetchNextCode() {
    const res = await axios.get("/api/sale/customers/next-code");
    form.code = res.data.code;
}
async function save() {
    loading.value = true;

    errors.value = {};

    try {
        const payload = {
            name: form.name,
            email: form.email,
            phone: form.phone,

            currency_id: form.currency_id,

            opening_debt: form.opening_debt,

            province_id: form.province_id,
            ward_id: form.ward_id,

            address_detail: form.address_detail,

            status: form.status,
        };

        if (form.id) {
            await axios.put(`/api/sale/customers/${form.id}`, payload);
            emit("saved");
            toast.success("Sửa khách hàng thành công", {
                position: "top-right",
            });
        } else {
            await axios.post("/api/sale/customers", payload);
            toast.success("Thêm khách hàng thành công", {
                position: "top-right",
                zIndex: 99999,
            });
        }

        emit("saved");
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await fetchProvinces();
});
</script>
