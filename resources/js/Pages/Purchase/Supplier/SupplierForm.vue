<template>
    <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-5xl max-h-[calc(100dvh-3rem)] overflow-hidden flex flex-col min-h-0"
    >
        <!-- Header -->
        <div
            class="flex flex-none items-center justify-between px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white"
        >
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">
                        {{
                            form.id
                                ? "Cập nhật nhà cung cấp"
                                : "Thêm nhà cung cấp mới"
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
                        Mã nhà cung cấp sẽ được tạo tự động
                    </p>
                </div>
            </div>

            <button
                @click="$emit('close')"
                type="button"
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
                <i class="ti ti-x text-xl">X</i>
            </button>
        </div>

        <div class="px-4 sm:px-6 py-5 sm:py-6 flex-1 min-h-0 overflow-y-auto overscroll-contain space-y-6">
            <!-- SECTION: THÔNG TIN CƠ BẢN -->
            <div>
                <h3
                    class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                >
                    <i class="ti ti-address-book text-base"></i>
                    Thông tin cơ bản
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Tên -->
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Tên nhà cung cấp <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <i
                                class="ti ti-building-store absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Nhập tên nhà cung cấp"
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

                    <!-- Phone -->
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

                    <!-- Email -->
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

                    <!-- Công nợ đầu kỳ -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Công nợ đầu kỳ
                        </label>
                        <div class="relative">
                            <i
                                class="ti ti-file-invoice absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                            <input
                                :value="formatMoneyInput(form.opening_debt)"
                                @input="handleOpeningBalance"
                                type="text"
                                class="w-full border rounded-lg px-3 py-2"
                            />
                        </div>
                        <p
                            v-if="errors.opening_debt"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.opening_debt[0] }}
                        </p>
                    </div>

                    <!-- Tạm ứng -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Tạm ứng đầu kỳ
                        </label>
                        <div class="relative">
                            <i
                                class="ti ti-cash absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                            <input
                                :value="formatMoneyInput(form.opening_advance)"
                                @input="handleOpeningBalanceAdvance"
                                type="text"
                                class="w-full border rounded-lg px-3 py-2"
                            />
                        </div>
                        <p
                            v-if="errors.opening_advance"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.opening_advance[0] }}
                        </p>
                    </div>

                    <!-- Tiền tệ -->
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Tiền tệ mặc định <span class="text-red-500">*</span>
                        </label>
                        <FormSelect
                            v-model="form.currency_id"
                            :options="currencyOptions"
                            placeholder="Tìm kiếm hoặc chọn loại tiền tệ..."
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

            <!-- SECTION: ĐỊA CHỈ TRỤ SỞ -->
            <div>
                <h3
                    class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                >
                    <i class="ti ti-map-pin text-base"></i>
                    Địa chỉ trụ sở
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tỉnh -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Tỉnh / Thành phố <span class="text-red-500">*</span>
                        </label>
                        <FormSelect
                            v-model="selectedProvince"
                            :options="provinceOptions"
                            placeholder="Chọn Tỉnh / Thành phố..."
                            searchable
                            @update:modelValue="onProvinceChange"
                        />
                        <p
                            v-if="errors.province_id"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.province_id[0] }}
                        </p>
                    </div>

                    <!-- Phường -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Phường / Xã <span class="text-red-500">*</span>
                        </label>
                        <FormSelect
                            v-model="selectedWard"
                            :options="wardOptions"
                            placeholder="Chọn Phường / Xã..."
                            searchable
                            :disabled="!selectedProvince"
                        />
                        <p
                            v-if="errors.ward_id"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.ward_id[0] }}
                        </p>
                    </div>

                    <!-- Địa chỉ chi tiết -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                        >
                            Địa chỉ chi tiết <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i
                                class="ti ti-map-2 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                            ></i>
                            <input
                                v-model="addressDetail"
                                type="text"
                                placeholder="Số nhà, ngõ, tên đường..."
                                class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
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
            </div>

            <!-- Ghi chú -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Ghi chú
                </label>
                <textarea
                    v-model="form.note"
                    rows="2"
                    placeholder="Ghi chú thông tin đặc biệt về nhà cung cấp này..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm transition-colors resize-none focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                />
            </div>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-none justify-end gap-3 px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/60"
        >
            <button
                @click="$emit('close')"
                type="button"
                class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
            >
                Hủy bỏ
            </button>
            <button
                @click="submit"
                type="button"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2"
            >
                <i class="ti ti-device-floppy text-base"></i>
                {{ form.id ? "Cập nhật" : "+ Nhà cung cấp" }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, watch, computed } from "vue";
import axios from "axios";
import FormSelect from "@/components/FormSelect.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { formatMoneyInput, unformatMoney } from "@/config/helpers";
const handleOpeningBalance = (e) => {
    form.opening_debt = unformatMoney(e.target.value);
};
const handleOpeningBalanceAdvance = (e) => {
    form.opening_advance = unformatMoney(e.target.value);
};
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
    code: "", // vẫn giữ để hiển thị ở header sau khi lưu, chỉ bỏ ô nhập trên form
    phone: "",
    email: "",
    currency_id: "", // Định dạng String giúp FormSelect tự động khớp và chọn giá trị cũ
    opening_debt: 0, // Công nợ đầu kỳ
    opening_advance: 0, // Tạm ứng
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
    (Array.isArray(provinces.value) ? provinces.value : []).filter(Boolean).map((p) => ({
        value: String(p.id),
        label: p.name ?? "",
    })),
);

const wardOptions = computed(() =>
    (Array.isArray(wards.value) ? wards.value : []).filter(Boolean).map((w) => ({
        value: String(w.id),
        label: w.name ?? "",
    })),
);

// ==================== THEO DÕI LOAD DỮ LIỆU SỬA ====================
watch(
    () => props.supplier,
    async (supplier) => {
        if (!supplier) {
            resetForm();
            return;
        }

        form.id = supplier.id;
        form.name = supplier.name;
        form.code = supplier.code;
        form.phone = supplier.phone;
        form.email = supplier.email;
        form.opening_debt = supplier.opening_debt ?? 0;
        form.opening_advance = supplier.opening_advance ?? 0;
        form.note = supplier.note;

        // FIX CHÍNH: Ép kiểu String cho dữ liệu v-model ban đầu để FormSelect hiển thị chính xác tên loại tiền tệ cũ
        form.currency_id = supplier.currency_id
            ? String(supplier.currency_id)
            : "";

        addressDetail.value = supplier.address_detail || "";

        if (!provinces.value.length) {
            await fetchProvinces();
        }
        const province = provinces.value.find(
            (item) => String(item.code) === String(supplier.province_code),
        );
        selectedProvince.value = province ? String(province.id) : "";

        if (selectedProvince.value) {
            await fetchWards(selectedProvince.value);
            const ward = wards.value.find(
                (item) => String(item.code) === String(supplier.ward_code),
            );
            selectedWard.value = ward ? String(ward.id) : "";
        }
    },
    { immediate: true },
);

// ==================== CÁC HÀM XỬ LÝ API ĐỊA CHÍ ====================
async function fetchProvinces() {
    try {
        const res = await axios.get("/api/provinces");
        provinces.value = Array.isArray(res.data) ? res.data : [];
    } catch (error) {
        console.error("Thất bại khi lấy danh sách tỉnh thành:", error);
    }
}

async function fetchWards(provinceId) {
    if (!provinceId) {
        wards.value = [];
        return;
    }
    try {
        const res = await axios.get(`/api/provinces/${provinceId}/wards`);
        wards.value = Array.isArray(res.data) ? res.data : [];
    } catch (error) {
        console.error("Thất bại khi lấy danh sách phường xã:", error);
    }
}

function onProvinceChange() {
    selectedWard.value = "";
    fetchWards(selectedProvince.value);
}

function resetForm() {
    form.id = null;
    form.name = "";
    form.code = "";
    form.phone = "";
    form.email = "";
    form.currency_id = "";
    form.opening_debt = 0;
    form.opening_advance = 0;
    form.note = "";
    selectedProvince.value = "";
    selectedWard.value = "";
    addressDetail.value = "";
    errors.value = {};
}

fetchProvinces();

// ==================== SUBMIT GỬI DỮ LIỆU LÊN SERVER ====================
async function submit() {
    errors.value = {};
    try {
        const provinceObj = provinces.value.find(
            (x) => String(x.id) === String(selectedProvince.value),
        );
        const wardObj = wards.value.find(
            (x) => String(x.id) === String(selectedWard.value),
        );

        const payload = {
            ...form,
            province_code: provinceObj?.code ?? null,
            province_name: provinceObj ? provinceObj.name : "",
            ward_code: wardObj?.code ?? null,
            ward_name: wardObj ? wardObj.name : "",
            address_detail: addressDetail.value || "",
            // Ép chuỗi ngược về Number để Database Laravel lưu trữ đúng kiểu số nguyên nguyên bản
            currency_id: form.currency_id ? Number(form.currency_id) : null,
            opening_debt: form.opening_debt || 0,
            opening_advance: form.opening_advance || 0,
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
            errors.value = error.response?.data?.errors ?? {};
            toast.error("Vui lòng kiểm tra lại dữ liệu các trường bắt buộc.");
            return;
        }
        console.error(error);
        toast.error("Lỗi hệ thống, không thể lưu dữ liệu.");
    }
}
</script>
