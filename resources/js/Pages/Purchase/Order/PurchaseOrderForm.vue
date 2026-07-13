<template>
    <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-6xl relative z-50"
    >
        <!-- Header -->
        <div
            class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white rounded-t-2xl"
        >
            <div class="flex items-center gap-3">
                <div
                    class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"
                >
                    <i class="ti ti-shopping-cart text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">
                        {{ form.id ? "Cập nhật đơn mua" : "Tạo đơn mua hàng" }}
                    </h2>
                    <p class="text-sm text-gray-400 mt-0.5">
                        {{
                            form.id
                                ? "Chỉnh sửa thông tin đơn mua hàng"
                                : "Khai báo đơn mua hàng mới với nhà cung cấp"
                        }}
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

        <div class="p-6 space-y-6">
            <!-- SECTION: THÔNG TIN ĐƠN MUA -->
            <div>
                <h3
                    class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                >
                    <i class="ti ti-clipboard-text text-base"></i>
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
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.supplier_id[0] }}
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
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.currency_id[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
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
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.expected_received_date[0] }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1.5"
                            >Ghi chú</label
                        >
                        <textarea
                            rows="2"
                            v-model="form.note"
                            class="w-full border rounded-lg px-3 py-2.5 text-sm transition-colors resize-none focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                            :class="
                                errors.note
                                    ? 'border-red-300'
                                    : 'border-gray-200'
                            "
                            placeholder="Nhập ghi chú đơn mua hàng..."
                        />
                        <p
                            v-if="errors.note"
                            class="text-red-500 text-xs mt-1 flex items-center gap-1"
                        >
                            <i class="ti ti-alert-circle"></i
                            >{{ errors.note[0] }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECTION: DANH SÁCH SẢN PHẨM MUA -->
            <div class="border border-gray-100 rounded-xl p-5 overflow-visible">
                <div class="flex justify-between items-center mb-4">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2"
                    >
                        <i class="ti ti-list-details text-base"></i>
                        Danh sách sản phẩm mua
                    </h3>
                    <button
                        @click="addItem"
                        type="button"
                        class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        <i class="ti ti-plus text-base"></i>
                        Sản phẩm
                    </button>
                </div>

                <p
                    v-if="errors.items"
                    class="text-red-500 text-sm mb-3 font-semibold flex items-center gap-1"
                >
                    <i class="ti ti-alert-circle"></i>{{ errors.items[0] }}
                </p>

                <div
                    class="overflow-x-auto rounded-lg border border-gray-200 style-scroll-visible pb-32 -mb-32"
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
                                class="hover:bg-blue-50/30 transition-colors relative"
                                :style="{
                                    zIndex: form.items.length + 10 - index,
                                }"
                            >
                                <td class="px-3 py-2 overflow-visible">
                                    <div
                                        :class="{
                                            'rounded-lg border border-red-300':
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
                                        class="text-red-500 text-xs mt-1 flex items-center gap-1"
                                    >
                                        <i class="ti ti-alert-circle"></i
                                        >{{
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
                                        class="w-full border rounded-lg px-2 py-1.5 text-center text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                                        :class="
                                            errors[`items.${index}.quantity`]
                                                ? 'border-red-300'
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
                                        class="w-full border rounded-lg px-2 py-1.5 text-right text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                                        :class="
                                            errors[`items.${index}.price`]
                                                ? 'border-red-300'
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
                                    class="px-3 py-2 text-right font-semibold text-blue-600"
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
                                        type="button"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto"
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
        </div>

        <!-- Footer -->
        <div
            class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl"
        >
            <button
                @click="$emit('close')"
                type="button"
                class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
            >
                Hủy
            </button>
            <button
                @click="submit"
                type="button"
                :disabled="loading"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
            >
                <i
                    v-if="loading"
                    class="ti ti-loader-2 animate-spin text-base"
                ></i>
                <i v-else class="ti ti-device-floppy text-base"></i>
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

// FIX: "currentCurrency" được dùng trong template (formatMoney(..., currentCurrency))
// nhưng trước đây chưa từng được khai báo -> luôn là undefined -> formatMoney
// không có tiền tệ để lấy symbol -> mất ký hiệu tiền tệ ở cột Đơn giá/Thành tiền.
const currentCurrency = computed(
    () => props.currencies.find((c) => c.id == form.currency_id) || null,
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
// FIX: "onSelectProduct" được gọi trong template khi chọn sản phẩm ở bảng
// (@update:modelValue="() => onSelectProduct(item)") nhưng trước đây chưa
// từng được khai báo -> chọn sản phẩm không tự điền đơn giá.
// Tự động điền đơn giá từ productOptions khi người dùng chọn sản phẩm,
// chỉ điền khi ô giá đang trống để không ghi đè giá người dùng đã tự sửa tay.
function onSelectProduct(item) {
    const product = productOptions.value.find(
        (p) => p.value === String(item.product_id),
    );
    if (product && (item.price === "" || item.price === null)) {
        item.price = product.price;
    }
}

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

// FIX: "removeItem" được gọi trong template (nút xóa dòng sản phẩm)
// nhưng trước đây chưa từng được khai báo -> bấm nút xóa không có tác dụng gì.
// Giữ lại tối thiểu 1 dòng sản phẩm trong bảng: nếu chỉ còn 1 dòng thì reset
// dòng đó về rỗng thay vì xóa hẳn, để form luôn có ít nhất 1 hàng để nhập.
function removeItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    } else {
        form.items[0] = { product_id: "", quantity: 1, price: "" };
    }
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
