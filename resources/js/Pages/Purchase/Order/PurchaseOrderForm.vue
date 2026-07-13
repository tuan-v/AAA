<template>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-6xl relative z-50">
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-2xl px-6 py-5 flex justify-between items-center"
        >
            <div>
                <h2 class="text-xl font-bold text-white">
                    {{ form.id ? "Cập nhật đơn mua" : "Tạo đơn mua hàng" }}
                </h2>
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
                    Thông tin đơn mua
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div>
                            <FormSelect
                                v-model="form.supplier_id"
                                :options="supplierOptions"
                                label="Nhà cung cấp"
                                placeholder="Tìm hoặc chọn nhà cung cấp..."
                                searchable
                                allow-create
                                add-new-text="Thêm nhà cung cấp mới"
                                :required="true"
                                @add-new="openSupplierModal"
                            />
                        </div>
                        <p
                            v-if="errors.supplier_id"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.supplier_id[0] }}
                        </p>
                    </div>

                    <div>
                        <div>
                            <FormSelect
                                v-model="form.currency_id"
                                :options="currencyOptions"
                                label="Tiền tệ"
                                placeholder="Tìm hoặc chọn tiền tệ..."
                                searchable
                            />
                        </div>
                        <p
                            v-if="errors.currency_id"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.currency_id[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                        >
                            Dự kiến nhận<span class="text-red-500">*</span>
                        </label>
                        <InputDate
                            v-model="form.expected_received_date"
                            placeholder="Chọn ngày"
                            :clearable="true"
                            :config="{ minDate: 'today' }"
                        />
                        <p
                            v-if="errors.expected_received_date"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.expected_received_date[0] }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Ghi chú</label
                        >
                        <textarea
                            rows="2"
                            v-model="form.note"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            :class="
                                errors.note
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-gray-200'
                            "
                            placeholder="Nhập ghi chú đơn mua hàng..."
                        />
                        <p v-if="errors.note" class="text-red-500 text-sm mt-1">
                            {{ errors.note[0] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border border-gray-100 rounded-xl p-5 overflow-visible">
                <div class="flex justify-between items-center mb-4">
                    <h3
                        class="text-sm font-semibold text-gray-700 flex items-center gap-2"
                    >
                        <span
                            class="w-1 h-4 bg-indigo-500 rounded-full inline-block"
                        ></span>
                        Danh sách sản phẩm mua
                    </h3>
                    <button
                        @click="addItem"
                        class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                    >
                        + Sản phẩm
                    </button>
                </div>

                <p
                    v-if="errors.items"
                    class="text-red-500 text-sm mb-3 font-semibold"
                >
                    {{ errors.items[0] }}
                </p>

                <div
                    class="overflow-x-auto rounded-lg border border-gray-100 style-scroll-visible pb-32 -mb-32"
                >
                    <table
                        class="w-full table-auto min-w-[800px] text-sm table-layout-fixed"
                    >
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Sản phẩm
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-32"
                                >
                                    SL
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide w-44"
                                >
                                    Đơn giá
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide w-44"
                                >
                                    Thành tiền
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 w-16"
                                ></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="hover:bg-blue-50/30 transition relative"
                                :style="{
                                    zIndex: form.items.length + 10 - index,
                                }"
                            >
                                <td class="px-3 py-2 overflow-visible">
                                    <div
                                        :class="{
                                            'rounded-lg border border-red-500':
                                                errors[
                                                    `items.${index}.product_id`
                                                ],
                                        }"
                                    >
                                        <FormSelect
                                            v-model="item.product_id"
                                            :options="productOptions"
                                            placeholder="Chọn sản phẩm..."
                                            searchable
                                            class="w-full"
                                            @update:modelValue="
                                                () => onSelectProduct(item)
                                            "
                                        />
                                    </div>
                                    <p
                                        v-if="
                                            errors[`items.${index}.product_id`]
                                        "
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{
                                            errors[
                                                `items.${index}.product_id`
                                            ][0]
                                        }}
                                    </p>
                                </td>

                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        min="1"
                                        v-model="item.quantity"
                                        class="w-full border rounded-lg px-2 py-1.5 text-center text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="
                                            errors[`items.${index}.quantity`]
                                                ? 'border-red-500 bg-red-50'
                                                : 'border-gray-200'
                                        "
                                    />
                                    <p
                                        v-if="errors[`items.${index}.quantity`]"
                                        class="text-red-500 text-xs mt-1 text-center"
                                    >
                                        {{
                                            errors[`items.${index}.quantity`][0]
                                        }}
                                    </p>
                                </td>

                                <td class="px-3 py-2">
                                    <input
                                        :value="formatNumber(item.price)"
                                        @input="updatePrice(item, $event)"
                                        class="w-full border rounded-lg px-2 py-1.5 text-right text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="
                                            errors[`items.${index}.price`]
                                                ? 'border-red-500 bg-red-50'
                                                : 'border-gray-200'
                                        "
                                    />
                                    <p
                                        v-if="errors[`items.${index}.price`]"
                                        class="text-red-500 text-xs mt-1 text-right"
                                    >
                                        {{ errors[`items.${index}.price`][0] }}
                                    </p>
                                </td>

                                <td
                                    class="px-3 py-2 text-right font-semibold text-green-600"
                                >
                                    {{
                                        formatMoney(
                                            (Number(item.price) || 0) *
                                                (Number(item.quantity) || 0),
                                            currentCurrency,
                                        )
                                    }}
                                </td>

                                <td class="px-3 py-2 text-center">
                                    <button
                                        @click="removeItem(index)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition mx-auto"
                                        title="Xóa sản phẩm"
                                    >
                                        <DeleteIcon class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-40">
                    <div
                        class="bg-blue-50 border border-blue-100 rounded-xl p-4 min-w-[280px] flex justify-between items-center relative z-10"
                    >
                        <span class="text-sm font-medium text-gray-600"
                            >Tổng tiền:</span
                        >
                        <span class="font-bold text-xl text-blue-700">
                            {{ formatMoney(totalAmount, currentCurrency) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-1">
                <button
                    @click="$emit('close')"
                    class="px-5 py-2.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition"
                >
                    Hủy
                </button>
                <button
                    @click="submit"
                    :disabled="loading"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 text-sm font-medium rounded-lg disabled:opacity-50 transition"
                >
                    <svg
                        v-if="loading"
                        class="w-4 h-4 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v8z"
                        />
                    </svg>
                    {{
                        loading
                            ? "Đang lưu..."
                            : form.id
                              ? "Cập nhật đơn mua"
                              : "Lưu đơn hàng"
                    }}
                </button>
            </div>
        </div>
    </div>

    <Modal v-if="showSupplierModal" @close="showSupplierModal = false">
        <template #body>
            <SupplierForm
                :currencies="currencies"
                @saved="onSupplierCreated"
                @close="showSupplierModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import axios from "axios";
import { reactive, computed, watch, ref, onMounted } from "vue";
import { formatMoney } from "@/config/helpers";
import FormSelect from "@/components/FormSelect.vue";
import SupplierForm from "@/Pages/Purchase/Supplier/SupplierForm.vue";
import Modal from "@/components/Modal.vue";
import DeleteIcon from "../../../icons/DeleteIcon.vue";
import InputDate from "@/components/InputDate.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const props = defineProps({
    order: { type: Object, default: null },
    suppliers: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
});

const emit = defineEmits(["saved", "close"]);

const showSupplierModal = ref(false);
const loading = ref(false);
const productOptions = ref([]);

const errors = ref({});

const form = reactive({
    id: null,
    supplier_id: "",
    currency_id: "",
    expected_received_date: "",
    note: "",
    items: [],
});

// ==================== COMPUTED ====================
const supplierOptions = computed(() =>
    props.suppliers.map((s) => ({ value: s.id, label: s.name })),
);

// Thêm computed này vào vùng COMPUTED trong file của bạn
const currencyOptions = computed(() =>
    props.currencies.map((c) => ({
        value: c.id,
        label: `${c.code} - ${c.name}`, // Hiển thị định dạng: "VND - Việt Nam Đồng" hoặc "USD - Đô la Mỹ"
    })),
);

const totalAmount = computed(() =>
    form.items.reduce(
        (sum, item) =>
            sum + Number(item.quantity || 0) * Number(item.price || 0),
        0,
    ),
);

// ==================== FETCH PRODUCTS ====================
const fetchAllProducts = async () => {
    try {
        const { data } = await axios.get("/api/products/for-select");
        productOptions.value = data.map((p) => ({
            value: String(p.id),
            label: p.code ? `${p.code} - ${p.name}` : p.name,
            price: Number(p.price || 0),
        }));
    } catch (error) {
        console.error("Lỗi tải sản phẩm:", error);
    }
};

// ==================== METHODS ====================
const openSupplierModal = () => (showSupplierModal.value = true);

const onSupplierCreated = (newSupplier) => {
    showSupplierModal.value = false;
    if (!newSupplier) return;
    if (!props.suppliers.some((s) => s.id == newSupplier.id)) {
        props.suppliers.push(newSupplier);
    }
    form.supplier_id = newSupplier.id;
};

function formatNumber(value) {
    if (value === 0) return "0";
    if (!value) return "";
    return new Intl.NumberFormat("vi-VN").format(value);
}

function updatePrice(item, event) {
    item.price = parseNumber(event.target.value);
}

// Giữ lại fix chuỗi rỗng gửi lên Backend bắt validate
function parseNumber(value) {
    if (!value && value !== 0) return "";
    return Number(String(value).replace(/[^\d]/g, ""));
}

function addItem() {
    form.items.push({ product_id: "", quantity: 1, price: "" });
}

// Khắc phục lỗi watch khởi tạo sai cú pháp bằng việc dùng hàm ẩn danh theo dõi chuẩn Vue 3
function resetForm() {
    form.id = null;
    form.supplier_id = "";
    form.currency_id = "";
    form.expected_received_date = "";
    form.note = "";
    form.items = [{ product_id: "", quantity: 1, price: "" }];
    errors.value = {};
}

// ==================== WATCH ORDER ====================
watch(
    () => props.order,
    (order) => {
        if (!order) {
            resetForm();
            return;
        }

        form.id = order.id;
        form.supplier_id = order.supplier_id || order.supplier?.id || "";
        form.currency_id = order.currency_id || order.currency?.id || "";
        form.expected_received_date = order.expected_received_date
            ? String(order.expected_received_date).substring(0, 10)
            : "";
        form.note = order.note || "";

        if (order.items && order.items.length > 0) {
            form.items = order.items.map((item) => ({
                product_id: String(item.product_id || item.product?.id || ""),
                quantity: Number(item.quantity || 1),
                price: item.price === 0 || item.price ? Number(item.price) : "",
            }));
        } else {
            form.items = [{ product_id: "", quantity: 1, price: "" }];
        }
        errors.value = {};
    },
    { immediate: true },
);

watch(
    () => form.supplier_id,
    (supplierId) => {
        if (!supplierId) return;
        const supplier = props.suppliers.find((s) => s.id == supplierId);
        if (supplier)
            form.currency_id = supplier.currency_id || form.currency_id;
    },
);

// ==================== LIFECYCLE ====================
onMounted(() => {
    fetchAllProducts();
});

// ==================== SUBMIT ====================
async function submit() {
    loading.value = true;
    errors.value = {};

    try {
        const cleanItems = form.items.map((item) => ({
            product_id: item.product_id ? String(item.product_id) : null,
            quantity: item.quantity === "" ? null : Number(item.quantity),
            price: item.price === "" ? null : Number(item.price),
        }));

        const payload = {
            supplier_id: form.supplier_id || null,
            currency_id: form.currency_id || null,
            expected_received_date: form.expected_received_date,
            note: form.note,
            items: cleanItems,
        };

        if (form.id) {
            await axios.put(`/api/purchase/orders/${form.id}`, payload);
        } else {
            await axios.post("/api/purchase/orders", payload);
        }

        toast.success(
            form.id ? "Cập nhật đơn mua thành công" : "Tạo đơn mua thành công",
        );
        emit("saved");
    } catch (error) {
        console.error(error);
        if (error.response && error.response.status === 422) {
            errors.value = { ...error.response.data.errors };
            toast.error("Dữ liệu nhập vào chưa hợp lệ, vui lòng kiểm tra lại.");
        } else {
            toast.error(error.response?.data?.message || "Có lỗi xảy ra");
        }
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
/* Trick CSS hoàn hảo giải quyết bài toán Dropdown bị cắt bởi overflow-x-auto trên table */
.style-scroll-visible {
    overflow-x: auto;
    overflow-y: visible !important;
}
</style>
