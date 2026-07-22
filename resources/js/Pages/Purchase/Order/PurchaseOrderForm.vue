<template>
    <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-6xl relative z-50 max-h-[90vh] flex flex-col overflow-hidden"
    >
        <!-- Header (cố định, không cuộn) -->
        <div
            class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white rounded-t-2xl shrink-0"
        >
            <div class="flex items-center gap-3">
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
                <i class="ti ti-x text-xl">X</i>
            </button>
        </div>

        <!-- Body (phần duy nhất cuộn) -->
        <div class="p-6 space-y-6 flex-1 overflow-y-auto">
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
                            Dự kiến nhận <span class="text-red-500">*</span>
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
                    class="overflow-x-auto rounded-lg border border-gray-200"
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
                                    class="border-b border-gray-100 px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-24"
                                >
                                    VAT (%)
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
                                            append-to-body
                                            placeholder="Chọn sản phẩm..."
                                            searchable
                                            allow-create
                                            add-new-text="Thêm sản phẩm mới"
                                            class="w-full"
                                            @update:modelValue="
                                                () => onSelectProduct(item)
                                            "
                                            @add-new="openProductModal(item)"
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
                                        :step="item.allow_decimal ? '0.01' : '1'"
                                        v-model="item.quantity"
                                        @input="handleQuantityChange(item, index)"
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
                                    <p class="text-[11px] mt-1 text-center text-gray-500">{{ item.allow_decimal ? 'Cho phép số lẻ' : 'Chỉ được nhập số nguyên' }}</p>
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
                                    <p v-if="showConvertedAmounts" class="mt-1 text-[11px] text-right text-gray-500">
                                        Quy đổi: {{ formatMoney(convertToCompanyCurrency(item.price), companyCurrency) }}
                                    </p>
                                    <p
                                        v-if="errors[`items.${index}.price`]"
                                        class="text-red-500 text-xs mt-1 text-right"
                                    >
                                        {{ errors[`items.${index}.price`][0] }}
                                    </p>
                                </td>

                                <td class="px-3 py-2">
                                    <input
                                        v-model.number="item.vat_percent"
                                        type="number"
                                        min="0"
                                        max="10"
                                        step="0.01"
                                        @input="handleVatChange(item, index)"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-center text-sm"
                                    />
                                    <p v-if="errors[`items.${index}.vat_percent`]" class="text-red-500 text-xs mt-1 text-center">{{ errors[`items.${index}.vat_percent`][0] }}</p>
                                </td>

                                <td
                                    class="px-3 py-2 text-right font-semibold text-blue-600"
                                >
                                    <div>{{ formatMoney(lineTotal(item), currentCurrency) }}</div>
                                    <div v-if="showConvertedAmounts" class="mt-1 text-[11px] font-normal text-gray-500">
                                        Quy đổi: {{ formatMoney(convertToCompanyCurrency(lineTotal(item)), companyCurrency) }}
                                    </div>
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

                <div class="flex justify-end mt-6">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 min-w-[320px] space-y-2 relative z-0">
                        <div class="flex justify-between text-sm"><span>Tạm tính:</span><span>{{ formatMoney(subtotal, currentCurrency) }}</span></div>
                        <div class="flex justify-between text-sm"><span>VAT:</span><span>{{ formatMoney(vatAmount, currentCurrency) }}</span></div>
                        <div class="flex justify-between border-t pt-2 font-bold text-xl text-blue-700"><span>Tổng tiền:</span><span>{{ formatMoney(totalAmount, currentCurrency) }}</span></div>
                        <div v-if="showConvertedAmounts" class="flex justify-between text-xs text-gray-500"><span>Tổng sau quy đổi:</span><span class="font-semibold">{{ formatMoney(convertToCompanyCurrency(totalAmount), companyCurrency) }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer (cố định, không cuộn) -->
        <div
            class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl shrink-0"
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
                :currencies="supplierCurrencies"
                @saved="onSupplierCreated"
                @close="showSupplierModal = false"
            />
        </template>
    </Modal>

    <!-- TODO: chỉnh lại đường dẫn import + props cho đúng ProductForm thật của bạn -->
    <Modal v-if="showProductModal" @close="showProductModal = false">
        <template #body>
            <ProductForm
                @saved="onProductCreated"
                @close="showProductModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import axios from "axios";
import { reactive, computed, watch, ref, onMounted } from "vue";
import { formatMoney, getValidationMessage } from "@/config/helpers";
import FormSelect from "@/components/FormSelect.vue";
import SupplierForm from "@/Pages/Purchase/Supplier/SupplierForm.vue";
// TODO: sửa lại đường dẫn cho đúng component ProductForm thật của bạn
import ProductForm from "@/Pages/Purchase/Product/ProductForm.vue";
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
const showProductModal = ref(false);
const supplierCurrencies = ref([]);
// Dòng sản phẩm đang được thao tác "Thêm sản phẩm mới" (để biết điền kết quả vào đúng dòng nào)
const activeProductItem = ref(null);
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

const currencyOptions = computed(() =>
    props.currencies.map((c) => ({
        value: c.id,
        label: `${c.code} - ${c.name}`,
    })),
);

const subtotal = computed(() =>
    form.items.reduce(
        (sum, item) =>
            sum + Number(item.quantity || 0) * Number(item.price || 0),
        0,
    ),
);
const vatAmount = computed(() =>
    form.items.reduce((sum, item) => {
        const base = Number(item.quantity || 0) * Number(item.price || 0);
        return sum + base * (Number(item.vat_percent || 0) / 100);
    }, 0),
);
const totalAmount = computed(() => subtotal.value + vatAmount.value);
const lineTotal = (item) => {
    const base = Number(item.quantity || 0) * Number(item.price || 0);
    return base * (1 + Number(item.vat_percent || 0) / 100);
};

const currentCurrency = computed(
    () => props.currencies.find((c) => c.id == form.currency_id) || null,
);
const companyCurrency = computed(
    () => props.currencies.find((c) => c.is_default || c.pivot?.is_default) || null,
);
const showConvertedAmounts = computed(
    () => currentCurrency.value && companyCurrency.value
        && String(currentCurrency.value.id) !== String(companyCurrency.value.id),
);
const convertToCompanyCurrency = (amount) =>
    Number(amount || 0) * Number(currentCurrency.value?.exchange_rate || 1);

// ==================== FETCH PRODUCTS ====================
const fetchAllProducts = async () => {
    try {
        const { data } = await axios.get("/api/products/for-select");
        productOptions.value = data.map((p) => ({
            value: String(p.id),
            label: p.code ? `${p.code} - ${p.name}` : p.name,
            price: Number(p.price || 0),
            allow_decimal: Boolean(p.allow_decimal),
            unit_name: p.unit_name || '',
        }));
    } catch (error) {
        console.error("Lỗi tải sản phẩm:", error);
    }
};

// ==================== METHODS ====================
function onSelectProduct(item) {
    const product = productOptions.value.find(
        (p) => p.value === String(item.product_id),
    );
    if (product && (item.price === "" || item.price === null)) {
        item.price = product.price;
    }
    if (product) item.allow_decimal = product.allow_decimal;
}

function handleQuantityChange(item, index) {
    const value = Number(item.quantity);
    const key = `items.${index}.quantity`;
    if (!item.allow_decimal && Number.isFinite(value) && !Number.isInteger(value)) errors.value[key] = ['Đơn vị tính của sản phẩm này không cho phép nhập số lượng lẻ.'];
    else delete errors.value[key];
}

function handleVatChange(item, index) {
    const key = `items.${index}.vat_percent`;
    if (Number(item.vat_percent) > 10) errors.value[key] = ['VAT không được vượt quá 10%.'];
    else delete errors.value[key];
}

const openSupplierModal = async () => {
    try {
        const { data } = await axios.get("/api/currencies/for-select", {
            params: { scope: "all" },
        });
        supplierCurrencies.value = Array.isArray(data) ? data : data.data || [];
    } catch (error) {
        supplierCurrencies.value = [...props.currencies];
        toast.error("Không thể tải đầy đủ danh sách tiền tệ.");
    }

    showSupplierModal.value = true;
};

const onSupplierCreated = (newSupplier) => {
    showSupplierModal.value = false;
    if (!newSupplier) return;
    if (!props.suppliers.some((s) => s.id == newSupplier.id)) {
        props.suppliers.push(newSupplier);
    }
    form.supplier_id = newSupplier.id;
};

// Mở modal "Thêm sản phẩm mới", ghi nhớ dòng đang thao tác để điền kết quả đúng chỗ
function openProductModal(item) {
    activeProductItem.value = item;
    showProductModal.value = true;
}

// Sau khi tạo sản phẩm mới thành công: thêm vào danh sách option
// và tự động chọn + điền giá vào đúng dòng vừa bấm "Thêm sản phẩm mới"
function onProductCreated(newProduct) {
    showProductModal.value = false;
    if (!newProduct) return;

    const option = {
        value: String(newProduct.id),
        label: newProduct.code
            ? `${newProduct.code} - ${newProduct.name}`
            : newProduct.name,
        price: Number(newProduct.price || 0),
    };

    if (!productOptions.value.some((p) => p.value === option.value)) {
        productOptions.value.push(option);
    }

    if (activeProductItem.value) {
        activeProductItem.value.product_id = option.value;
        if (
            activeProductItem.value.price === "" ||
            activeProductItem.value.price === null
        ) {
            activeProductItem.value.price = option.price;
        }
        activeProductItem.value = null;
    }
}

function formatNumber(value) {
    if (value === 0) return "0";
    if (!value) return "";
    return formatMoney(value);
}

function updatePrice(item, event) {
    item.price = parseNumber(event.target.value);
}

function parseNumber(value) {
    if (!value && value !== 0) return "";
    return Number(String(value).replace(/[^\d]/g, ""));
}

function addItem() {
    form.items.push({ product_id: "", quantity: 1, price: "", vat_percent: 0 });
}

function removeItem(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    } else {
        form.items[0] = { product_id: "", quantity: 1, price: "", vat_percent: 0 };
    }
}

function resetForm() {
    form.id = null;
    form.supplier_id = "";
    form.currency_id = "";
    form.expected_received_date = "";
    form.note = "";
    form.items = [{ product_id: "", quantity: 1, price: "", vat_percent: 0 }];
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
                vat_percent: Number(item.vat_percent || 0),
            }));
        } else {
            form.items = [{ product_id: "", quantity: 1, price: "", vat_percent: 0 }];
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
            vat_percent: Number(item.vat_percent || 0),
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
            toast.error(getValidationMessage(error));
        } else {
            toast.error(error.response?.data?.message || "Có lỗi xảy ra");
        }
    } finally {
        loading.value = false;
    }
}
</script>
