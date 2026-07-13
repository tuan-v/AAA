<template>
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-5xl relative z-50 overflow-hidden"
    >
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 flex justify-between items-center"
        >
            <div>
                <h2 class="text-xl font-bold text-white">
                    {{
                        form.id
                            ? "Cập nhật nhà cung cấp"
                            : "Thêm nhà cung cấp mới"
                    }}
                </h2>
                <p class="text-sm text-blue-200 mt-0.5">
                    Quản lý và thiết lập thông tin đối tác nhà cung cấp hệ thống
                </p>
            </div>

            <button
                @click="$emit('close')"
                class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition"
            >
                ✕
            </button>
        </div>

        <div class="p-6 space-y-5">
            <div class="border border-gray-100 rounded-xl p-5 bg-gray-50/60">
                <h3
                    class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2"
                >
                    <span
                        class="w-1 h-4 bg-blue-500 rounded-full inline-block"
                    ></span>
                    Thông tin cơ bản
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                        >
                            Tên nhà cung cấp <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent h-[38px] transition"
                            placeholder="Nhập tên nhà cung cấp..."
                        />
                        <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                            {{ errors.name[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                        >
                            Mã nhà cung cấp
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.code"
                                type="text"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent h-[38px] transition"
                                :class="
                                    errors.code
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-200'
                                "
                                placeholder="Mã tự động..."
                            />
                            <button
                                @click="generateCode"
                                type="button"
                                class="bg-white hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap border border-gray-200 shadow-sm"
                            >
                                Tự sinh mã
                            </button>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Số điện thoại</label
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent h-[38px]"
                            placeholder="Nhập số điện thoại..."
                        />
                        <p
                            v-if="errors.phone"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.phone[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Email</label
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent h-[38px]"
                            placeholder="example@domain.com"
                        />
                        <p
                            v-if="errors.email"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.email[0] }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                        >
                            Tiền tệ mặc định
                        </label>
                        <div>
                            <FormSelect
                                v-model="form.currency_id"
                                :options="currencyOptions"
                                placeholder="Tìm kiếm hoặc chọn loại tiền tệ..."
                                searchable
                            />
                        </div>
                        <p
                            v-if="errors.currency_id"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.currency_id[0] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border border-gray-100 rounded-xl p-5 bg-gray-50/60">
                <h3
                    class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2"
                >
                    <span
                        class="w-1 h-4 bg-indigo-500 rounded-full inline-block"
                    ></span>
                    Thông tin địa chỉ trụ sở
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Tỉnh / Thành phố</label
                        >
                        <FormSelect
                            v-model="selectedProvince"
                            :options="provinceOptions"
                            placeholder="Chọn Tỉnh / Thành phố..."
                            searchable
                            @update:modelValue="onProvinceChange"
                        />
                        <p
                            v-if="errors.province_id"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.province_id[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Phường / Xã</label
                        >
                        <FormSelect
                            v-model="selectedWard"
                            :options="wardOptions"
                            placeholder="Chọn Phường / Xã..."
                            searchable
                            :disabled="!selectedProvince"
                        />
                        <p
                            v-if="errors.ward_id"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.ward_id[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Địa chỉ chi tiết</label
                        >
                        <input
                            v-model="addressDetail"
                            type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent h-[38px]"
                            placeholder="Số nhà, ngõ, tên đường..."
                        />
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"
                    >Ghi chú
                </label>
                <textarea
                    v-model="form.note"
                    rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none transition"
                    placeholder="Ghi chú thông tin đặc biệt về nhà cung cấp này..."
                />
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button
                    @click="$emit('close')"
                    class="px-5 py-2.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition font-medium"
                >
                    Hủy bỏ
                </button>
                <button
                    @click="submit"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 text-sm font-medium rounded-lg transition shadow-sm hover:shadow-md"
                >
                    {{ form.id ? "Cập nhật ngay" : "Thêm nhà cung cấp" }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted, computed } from "vue";
import axios from "axios";
import FormSelect from "@/components/FormSelect.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const props = defineProps({
    supplier: { type: Object, default: null },
    currencies: { type: Array, default: () => [] }, // Luôn nhận danh sách từ Index.vue trang cha
});

const emit = defineEmits(["saved", "close"]);

const errors = ref({});
const provinces = ref([]);
const wards = ref([]);

const selectedProvince = ref("");
const selectedWard = ref("");
const addressDetail = ref("");

const form = reactive({
    id: null,
    name: "",
    code: "",
    phone: "",
    email: "",
    currency_id: "", // Định dạng String giúp FormSelect tự động khớp và chọn giá trị cũ
    note: "",
});

// ==================== CHUYỂN ĐỔI CURRENCIES SANG CẤU TRÚC FORMSELECT ====================
const currencyOptions = computed(() =>
    props.currencies.map((c) => ({
        value: String(c.id), // Ép ID về dạng String đồng bộ
        label: `${c.code} - ${c.name}`,
    })),
);

const provinceOptions = computed(() =>
    provinces.value.map((p) => ({
        value: String(p.code),
        label: p.name,
    })),
);

const wardOptions = computed(() =>
    wards.value.map((w) => ({
        value: String(w.code),
        label: w.name,
    })),
);

// ==================== THEO DÕI LOAD DỮ LIỆU SỬA ====================
watch(
    () => props.supplier,
    (supplier) => {
        if (!supplier) {
            resetForm();
            return;
        }

        form.id = supplier.id;
        form.name = supplier.name;
        form.code = supplier.code;
        form.phone = supplier.phone;
        form.email = supplier.email;
        form.note = supplier.note;

        // FIX CHÍNH: Ép kiểu String cho dữ liệu v-model ban đầu để FormSelect hiển thị chính xác tên loại tiền tệ cũ
        form.currency_id = supplier.currency_id
            ? String(supplier.currency_id)
            : "";

        selectedProvince.value = supplier.province_code
            ? String(supplier.province_code)
            : "";
        selectedWard.value = supplier.ward_code
            ? String(supplier.ward_code)
            : "";
        addressDetail.value = supplier.address_detail || "";

        if (selectedProvince.value) {
            fetchWards(selectedProvince.value);
        }
    },
    { immediate: true },
);

// ==================== CÁC HÀM XỬ LÝ API ĐỊA CHÍ ====================
async function fetchProvinces() {
    try {
        const res = await axios.get("/api/provinces");
        provinces.value = res.data;
    } catch (error) {
        console.error("Thất bại khi lấy danh sách tỉnh thành:", error);
    }
}

async function fetchWards(provinceCode) {
    if (!provinceCode) {
        wards.value = [];
        return;
    }
    try {
        const res = await axios.get(`/api/provinces/${provinceCode}/wards`);
        wards.value = res.data;
    } catch (error) {
        console.error("Thất bại khi lấy danh sách phường xã:", error);
    }
}

function onProvinceChange() {
    selectedWard.value = "";
    fetchWards(selectedProvince.value);
}

function generateCode() {
    const random = Math.floor(Math.random() * 9999)
        .toString()
        .padStart(4, "0");
    form.code = `NCC${random}`;
}

function resetForm() {
    form.id = null;
    form.name = "";
    form.code = "";
    form.phone = "";
    form.email = "";
    form.currency_id = "";
    form.note = "";
    selectedProvince.value = "";
    selectedWard.value = "";
    addressDetail.value = "";
    errors.value = {};
}

onMounted(() => {
    fetchProvinces();
});
watch(
    () => props.currencies,
    (val) => {
        console.log("currencies", val);
        console.log("options", currencyOptions.value);
    },
    { immediate: true },
);
console.log(currencyOptions.value);
// ==================== SUBMIT GỬI DỮ LIỆU LÊN SERVER ====================
async function submit() {
    errors.value = {};
    try {
        const provinceObj = provinces.value.find(
            (x) => x.code == selectedProvince.value,
        );
        const wardObj = wards.value.find((x) => x.code == selectedWard.value);

        const payload = {
            ...form,
            province_code: selectedProvince.value || null,
            province_name: provinceObj ? provinceObj.name : "",
            ward_code: selectedWard.value || null,
            ward_name: wardObj ? wardObj.name : "",
            address_detail: addressDetail.value || "",
            // Ép chuỗi ngược về Number để Database Laravel lưu trữ đúng kiểu số nguyên nguyên bản
            currency_id: form.currency_id ? Number(form.currency_id) : null,
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

        toast.success("Lưu thông tin nhà cung cấp thành công!");
        emit("saved", supplier);
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
            toast.error("Vui lòng kiểm tra lại dữ liệu các trường bắt buộc.");
            return;
        }
        console.error(error);
        toast.error("Lỗi hệ thống, không thể lưu dữ liệu.");
    }
}
</script>
