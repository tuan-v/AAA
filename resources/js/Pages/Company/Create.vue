<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-8">
            <!-- HEADER -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">Tạo công ty</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Thiết lập thông tin doanh nghiệp của bạn
                </p>
            </div>

            <form @submit.prevent="saveCompany">
                <!-- LOGO -->
                <div class="flex flex-col items-center mb-6">
                    <label class="text-sm font-medium text-gray-700 mb-2">
                        Logo công ty
                    </label>

                    <img
                        v-if="preview"
                        :src="preview"
                        class="w-24 h-24 rounded-full object-cover border shadow"
                    />

                    <div
                        v-else
                        class="w-24 h-24 rounded-full bg-gray-100 flex items-center justify-center border"
                    >
                        <span class="text-gray-400 text-xs">No Logo</span>
                    </div>

                    <input
                        type="file"
                        accept="image/*"
                        @change="handleImage"
                        class="mt-3 text-sm"
                    />

                    <p v-if="errors.logo" class="text-red-500 text-xs mt-1">
                        {{ errors.logo[0] }}
                    </p>
                </div>

                <!-- GRID -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium"
                            >Tên công ty <span class="text-red">*</span></label
                        >
                        <input v-model="form.name" class="input" />
                        <p v-if="errors.name" class="text-red-500 text-xs">
                            {{ errors.name[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium"
                            >Mã số thuế <span class="text-red">*</span></label
                        >
                        <input v-model="form.tax_code" class="input" />
                        <p v-if="errors.tax_code" class="text-red-500 text-xs">
                            {{ errors.tax_code[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium"
                            >Email <span class="text-red">*</span></label
                        >
                        <input v-model="form.email" class="input" />
                        <p v-if="errors.email" class="text-red-500 text-xs">
                            {{ errors.email[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium"
                            >Số điện thoại
                            <span class="text-red">*</span></label
                        >
                        <input v-model="form.phone" class="input" />
                        <p v-if="errors.phone" class="text-red-500 text-xs">
                            {{ errors.phone[0] }}
                        </p>
                    </div>
                </div>

                <!-- ADDRESS DETAIL -->

                <!-- PROVINCE -->
                <div class="mt-4">
                    <label class="text-sm font-medium">
                        Tỉnh / Thành phố <span class="text-red">*</span>
                    </label>

                    <select
                        v-model="form.province_id"
                        @change="onProvinceChange"
                        class="input"
                    >
                        <option value="">-- Chọn tỉnh --</option>
                        <option
                            v-for="p in provinces"
                            :key="p.id"
                            :value="p.id"
                        >
                            {{ p.name }}
                        </option>
                    </select>

                    <p v-if="errors.province_id" class="text-red-500 text-xs">
                        {{ errors.province_id[0] }}
                    </p>
                </div>

                <!-- WARD -->
                <div class="mt-4">
                    <label class="text-sm font-medium">
                        Phường / Xã <span class="text-red">*</span></label
                    >

                    <select v-model="form.ward_id" class="input">
                        <option value="">-- Chọn phường/xã --</option>
                        <option v-for="w in wards" :key="w.id" :value="w.id">
                            {{ w.name }}
                        </option>
                    </select>

                    <p v-if="errors.ward_id" class="text-red-500 text-xs">
                        {{ errors.ward_id[0] }}
                    </p>
                </div>
                <div class="mt-4">
                    <label class="text-sm font-medium">
                        Số nhà / Đường <span class="text-red">*</span></label
                    >

                    <input
                        v-model="form.address_detail"
                        class="input"
                        placeholder="VD: 12 Nguyễn Trãi"
                    />

                    <p
                        v-if="errors.address_detail"
                        class="text-red-500 text-xs"
                    >
                        {{ errors.address_detail[0] }}
                    </p>
                </div>
                <!-- CURRENCY -->
                <div class="mt-4">
                    <label class="text-sm font-medium">Tiền tệ</label>

                    <select v-model="form.currency_id" class="input">
                        <option
                            v-for="c in currencies"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.symbol }} - {{ c.name }}
                        </option>
                    </select>

                    <p v-if="errors.currency_id" class="text-red-500 text-xs">
                        {{ errors.currency_id[0] }}
                    </p>
                </div>

                <!-- BUTTON -->
                <div class="mt-6">
                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition"
                    >
                        Hoàn tất tạo công ty
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    currencies: Array,
    defaultCurrencyId: Number,
});

const errors = ref({});
const file = ref(null);
const preview = ref(null);

const provinces = ref([]);
const wards = ref([]);

const form = reactive({
    name: "",
    tax_code: "",
    email: "",
    phone: "",
    address_detail: "",
    province_id: "",
    ward_id: "",
    currency_id: props.defaultCurrencyId || null,
});

function handleImage(e) {
    const f = e.target.files[0];
    if (!f) return;

    file.value = f;
    preview.value = URL.createObjectURL(f);
}

/* LOAD PROVINCES */
async function loadProvinces() {
    const res = await axios.get("/api/provinces");
    provinces.value = res.data || [];
}

/* LOAD WARDS */
async function loadWards(provinceId) {
    wards.value = [];

    if (!provinceId) return;

    const res = await axios.get(`/api/provinces/${provinceId}/wards`);

    wards.value = res.data || [];
}
function onProvinceChange() {
    form.ward_id = "";
    loadWards(form.province_id);
}

/* SAVE */
async function saveCompany() {
    errors.value = {};

    const data = new FormData();

    Object.keys(form).forEach((key) => {
        data.append(key, form[key]);
    });

    if (file.value) {
        data.append("logo", file.value);
    }

    try {
        await axios.post("/company", data, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        window.location.href = "/dashboard";
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert("Có lỗi xảy ra");
        }
    }
}

onMounted(() => {
    loadProvinces();
});
</script>

<style scoped>
.input {
    margin-top: 4px;
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 8px 12px;
    outline: none;
    transition: 0.2s;
}

.input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}
</style>
