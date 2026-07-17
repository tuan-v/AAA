<template>
    <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-5xl overflow-hidden z-50"
    >
        <!-- HEADER -->
        <div
            class="flex justify-between items-start px-7 py-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white"
        >
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">
                        {{
                            form.id ? "Cập nhật khách hàng" : "Thêm khách hàng"
                        }}
                    </h2>
                    <p
                        v-if="form.code"
                        class="text-sm text-blue-600 font-semibold mt-0.5 flex items-center gap-1"
                    >
                        <i class="ti ti-id-badge-2 text-base"></i>
                        {{ form.code }}
                    </p>
                    <p v-else class="text-sm text-gray-400 mt-0.5">
                        Mã khách hàng sẽ được tạo tự động
                    </p>
                </div>
            </div>

            <button
                @click="$emit('close')"
                type="button"
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <!-- BODY -->
        <div class="px-7 py-6 max-h-[75vh] overflow-y-auto">
            <!-- SECTION: THÔNG TIN CHUNG -->
            <div class="mb-6">
                <h3
                    class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                >
                    <i class="ti ti-address-book text-base"></i>
                    Thông tin chung
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <!-- TÊN -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Tên khách hàng <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <i
                                class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Nhập tên khách hàng"
                                class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                                :class="errors.name ? 'border-red-300' : ''"
                            />
                        </div>

                        <p
                            v-if="errors.name"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.name[0] }}
                        </p>
                    </div>

                    <!-- PHONE -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <i
                                class="ti ti-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                            <input
                                v-model="form.phone"
                                type="text"
                                placeholder="Nhập số điện thoại"
                                class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                                :class="errors.phone ? 'border-red-300' : ''"
                            />
                        </div>

                        <p
                            v-if="errors.phone"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.phone[0] }}
                        </p>
                    </div>

                    <!-- EMAIL -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Email <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <i
                                class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="ten@congty.com"
                                class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                                :class="errors.email ? 'border-red-300' : ''"
                            />
                        </div>

                        <p
                            v-if="errors.email"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.email[0] }}
                        </p>
                    </div>

                    <!-- TIỀN TỆ -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Tiền tệ mặc định <span class="text-red-500">*</span>
                        </label>

                        <FormSelect
                            v-model="form.currency_id"
                            :options="currencyOptions"
                            placeholder="Tìm và chọn loại tiền tệ..."
                            searchable
                        />
                        <p
                            v-if="errors.currency_id"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.currency_id[0] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECTION: ĐỊA CHỈ -->
            <div class="mb-6">
                <h3
                    class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                >
                    <i class="ti ti-map-pin text-base"></i>
                    Địa chỉ
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <!-- TỈNH -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Tỉnh / Thành phố <span class="text-red-500">*</span>
                        </label>

                        <FormSelect
                            v-model="form.province_id"
                            :options="provinceOptions"
                            searchable
                            placeholder="Chọn tỉnh"
                        />
                        <p
                            v-if="errors.province_id"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.province_id[0] }}
                        </p>
                    </div>

                    <!-- PHƯỜNG -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Xã / Phường <span class="text-red-500">*</span>
                        </label>

                        <FormSelect
                            v-model="form.ward_id"
                            :options="wardOptions"
                            searchable
                            placeholder="Chọn xã/phường"
                            :disabled="!form.province_id"
                        />
                        <p
                            v-if="errors.ward_id"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.ward_id[0] }}
                        </p>
                    </div>

                    <!-- ĐỊA CHỈ -->
                    <div class="col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Địa chỉ chi tiết <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            v-model="form.address_detail"
                            rows="3"
                            placeholder="Số nhà, tên đường, khu vực..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm transition-colors resize-none focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                        />
                        <p
                            v-if="errors.address_detail"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.address_detail[0] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECTION: TÀI CHÍNH -->
            <div>
                <h3
                    class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                >
                    <i class="ti ti-cash text-base"></i>
                    Tài chính
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <!-- CÔNG NỢ -->
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Công nợ đầu kì
                        </label>

                        <input
                            :value="formatMoneyInput(form.opening_debt)"
                            @input="handleOpeningBalance"
                            type="text"
                            class="w-full border rounded-lg px-3 py-2"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div
            class="flex justify-end gap-3 px-7 py-4 border-t border-gray-100 bg-gray-50/60"
        >
            <button
                @click="$emit('close')"
                class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
            >
                Đóng
            </button>

            <button
                @click="save"
                :disabled="loading"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
            >
                <i
                    v-if="loading"
                    class="ti ti-loader-2 animate-spin text-base"
                ></i>
                <i v-else class="ti ti-device-floppy text-base"></i>
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
import { formatMoneyInput, unformatMoney } from "@/config/helpers";
const handleOpeningBalance = (e) => {
    form.opening_debt = unformatMoney(e.target.value);
};
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
// ==================== CẤU TRÚC OPTIONS CHUẨN HOÁ CHO FORMSELECT ====================
const currencyOptions = computed(() => {
    // 1. Kiểm tra nếu không có dữ liệu thì trả về mảng rỗng chống lỗi crash
    if (!props.currencies) return [];

    // 2. Dự phòng trường hợp props.currencies truyền sang là Object dạng pagination { data: [...] }
    const currencyArray = Array.isArray(props.currencies)
        ? props.currencies
        : props.currencies.data || [];

    // 3. Nếu vẫn không tìm ra mảng hợp lệ, trả về mảng trống
    if (!Array.isArray(currencyArray)) return [];

    return currencyArray.map((c) => ({
        value: String(c.id),
        label: `${c.code} - ${c.name}`,
    }));
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
