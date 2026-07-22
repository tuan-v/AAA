<template>
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-6xl relative z-50 max-h-[90vh] flex flex-col overflow-hidden"
    >
        <!-- Header (cố định) -->
        <div
            class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white rounded-t-2xl shrink-0"
        >
            <div>
                <h2 class="text-xl font-bold text-gray-800 leading-tight">
                    {{ form.id ? "Cập nhật đơn bán hàng" : "Tạo đơn bán hàng" }}
                </h2>
                <p class="text-sm text-gray-400 mt-0.5">
                    Quản lý đơn bán hàng doanh nghiệp
                </p>
            </div>
            <button
                @click="$emit('close')"
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
                ✕
            </button>
        </div>

        <!-- Body (phần duy nhất cuộn) -->
        <div class="p-6 space-y-5 flex-1 overflow-y-auto">
            <div class="border border-gray-100 rounded-xl p-5 bg-gray-50/60">
                <h3
                    class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2"
                >
                    <span
                        class="w-1 h-4 bg-blue-500 rounded-full inline-block"
                    ></span>
                    Thông tin đơn bán hàng
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div>
                            <FormSelect
                                v-model="form.customer_id"
                                :options="customerOptions"
                                label="Khách hàng"
                                placeholder="Chọn khách hàng..."
                                searchable
                                allow-create
                                add-new-text="Thêm khách hàng mới"
                                @add-new="openCustomerModal"
                            />
                        </div>
                        <p
                            v-if="errors.customer_id"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.customer_id[0] }}
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
                        <InputDate
                            v-model="form.expected_delivery_date"
                            label="Ngày giao dự kiến"
                            :clearable="true"
                            :config="{ minDate: 'today' }"
                        />
                        <p
                            v-if="errors.expected_delivery_date"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.expected_delivery_date[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Tỉnh / Thành phố
                            <span class="text-red-500">*</span></label
                        >
                        <div>
                            <FormSelect
                                v-model="form.province_id"
                                :options="provinceOptions"
                                placeholder="Tìm hoặc chọn tỉnh..."
                                searchable
                                @update:modelValue="onProvinceChange"
                            />
                        </div>
                        <p
                            v-if="errors.province_id"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.province_id[0] }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Phường / Xã
                            <span class="text-red-500">*</span></label
                        >
                        <div>
                            <FormSelect
                                v-model="form.ward_id"
                                :options="wardOptions"
                                placeholder="Tìm hoặc chọn phường..."
                                searchable
                                :disabled="!form.province_id"
                            />
                        </div>
                        <p
                            v-if="errors.ward_id"
                            class="text-red-500 text-sm mt-1"
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
                            v-model="form.address_detail"
                            type="text"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent h-[38px]"
                            :class="
                                errors.address_detail
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-gray-200'
                            "
                            placeholder="Số nhà, ngõ, đường..."
                        />
                        <p
                            v-if="errors.address_detail"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.address_detail[0] }}
                        </p>
                    </div>

                    <div class="md:col-span-3">
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Ghi chú đơn hàng</label
                        >
                        <textarea
                            v-model="form.note"
                            rows="2"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            :class="
                                errors.note
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-gray-200'
                            "
                            placeholder="Nhập ghi chú hoặc yêu cầu đặc biệt khi giao hàng..."
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
                        Danh sách sản phẩm xuất kho
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
                    class="overflow-x-auto rounded-lg border border-gray-100"
                >
                    <table
                        class="w-full table-auto min-w-[950px] text-sm table-layout-fixed"
                    >
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-4/12"
                                >
                                    Sản phẩm
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-24"
                                >
                                    Tồn kho
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-28"
                                >
                                    Số lượng
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-24"
                                >
                                    VAT %
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide w-36"
                                >
                                    Đơn giá
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide w-36"
                                >
                                    Thành tiền
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 w-12"
                                ></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="hover:bg-blue-50/30 transition relative"
                                :style="{
                                    zIndex: form.items.length + 10 - index,
                                }"
                            >
                                <td class="px-3 py-2 overflow-visible relative">
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
                                            append-to-body
                                            searchable
                                            placeholder="Chọn sản phẩm bán..."
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

                                <td class="px-3 py-2 text-center align-middle">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="
                                            (item.stock_quantity ?? 0) > 0
                                                ? 'bg-green-50 text-green-600'
                                                : 'bg-red-50 text-red-500'
                                        "
                                    >
                                        {{ item.stock_quantity ?? 0 }}
                                    </span>
                                </td>

                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        min="1"
                                        :step="item.allow_decimal ? '0.01' : '1'"
                                        :max="item.stock_quantity || 999999"
                                        v-model="item.quantity"
                                        @input="handleQuantityChange(item, index)"
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
                                    <p class="text-[11px] mt-1 text-center text-gray-500">{{ item.allow_decimal ? 'Cho phép số lẻ' : 'Chỉ được nhập số nguyên' }}</p>
                                </td>

                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        min="0"
                                        max="10"
                                        step="0.01"
                                        v-model="item.vat_percent"
                                        @input="handleVatChange(item, index)"
                                        class="w-full border rounded-lg px-2 py-1.5 text-center text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent border-gray-200"
                                    />
                                    <p
                                        v-if="
                                            errors[`items.${index}.vat_percent`]
                                        "
                                        class="text-red-500 text-xs mt-1 text-center"
                                    >
                                        {{
                                            errors[
                                                `items.${index}.vat_percent`
                                            ][0]
                                        }}
                                    </p>
                                </td>

                                <td class="px-3 py-2">
                                    <input
                                        :value="formatNumber(item.unit_price)"
                                        @input="updateUnitPrice(item, $event)"
                                        class="w-full border rounded-lg px-2 py-1.5 text-right text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent border-gray-200"
                                    />
                                    <p v-if="showConvertedAmounts" class="mt-1 text-[11px] text-right text-gray-500">
                                        Quy đổi: {{ formatMoney(convertToCompanyCurrency(item.unit_price), companyCurrency) }}
                                    </p>
                                    <p
                                        v-if="
                                            errors[`items.${index}.unit_price`]
                                        "
                                        class="text-red-500 text-xs mt-1 text-right"
                                    >
                                        {{
                                            errors[
                                                `items.${index}.unit_price`
                                            ][0]
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-3 py-2 text-right font-semibold text-green-600 align-middle"
                                >
                                    <div>{{ formatMoney(lineTotalWithVat(item), currentCurrency) }}</div>
                                    <div v-if="showConvertedAmounts" class="mt-1 text-[11px] font-normal text-gray-500">
                                        Quy đổi: {{ formatMoney(convertToCompanyCurrency(lineTotalWithVat(item)), companyCurrency) }}
                                    </div>
                                </td>

                                <td class="px-3 py-2 text-center align-middle">
                                    <button
                                        @click="removeItem(index)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition mx-auto"
                                        title="Xóa dòng"
                                    >
                                        <DeleteIcon class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-6 relative z-0">
                    <div
                        class="bg-blue-50 border border-blue-100 rounded-xl p-5 min-w-[340px] space-y-2.5"
                    >
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tạm tính hàng hóa:</span>
                            <span class="font-medium text-gray-800">{{
                                formatMoney(subtotal, currentCurrency)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tiền thuế VAT:</span>
                            <span class="font-medium text-gray-800">{{
                                formatMoney(vatAmount, currentCurrency)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between pt-3 border-t border-blue-200 text-base font-bold text-blue-700"
                        >
                            <span>Tổng tiền thanh toán:</span>
                            <span>{{
                                formatMoney(totalAmount, currentCurrency)
                            }}</span>
                        </div>
                        <div v-if="showConvertedAmounts" class="flex justify-between text-xs text-gray-500">
                            <span>Tổng sau quy đổi:</span>
                            <span class="font-semibold">{{ formatMoney(convertToCompanyCurrency(totalAmount), companyCurrency) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer (cố định) -->
        <div
            class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl shrink-0"
        >
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
                          ? "Cập nhật đơn"
                          : "Tạo đơn hàng"
                }}
            </button>
        </div>
    </div>

    <Modal v-if="showCustomerModal" @close="showCustomerModal = false">
        <template #body>
            <CustomerForm
                :currencies="customerCurrencies"
                @saved="onCustomerCreated"
                @close="showCustomerModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { reactive, computed, watch, onMounted, nextTick, ref } from "vue";
import axios from "axios";
import { formatMoney, getValidationMessage } from "@/config/helpers";
import { toast } from "vue3-toastify";
import FormSelect from "@/components/FormSelect.vue";
import InputDate from "@/components/InputDate.vue";
import Modal from "@/components/Modal.vue";
import DeleteIcon from "../../../icons/DeleteIcon.vue";
import CustomerForm from "../Customer/CustomerForm.vue";

const props = defineProps({
    order: { type: Object, default: null },
    customers: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    provinces: { type: Array, default: () => [] },
    customerId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits(["saved", "close", "customer-created"]);

const showCustomerModal = ref(false);
const customerCurrencies = ref([]);
const loading = ref(false);
const wards = ref([]);
const errors = ref({});
const productOptions = ref([]);

// Khách hàng tạo nhanh ngay trong đơn hàng — chưa có trong props.customers
// vì props là dữ liệu tĩnh được trang cha truyền xuống lúc mở modal, không
// tự refetch khi có khách hàng mới.
const locallyCreatedCustomers = ref([]);

// ==================== COMPUTED ====================
const provinceOptions = computed(() =>
    props.provinces.map((p) => ({ value: p.id, label: p.name })),
);
const wardOptions = computed(() =>
    wards.value.map((w) => ({ value: w.id, label: w.name })),
);
const allCustomers = computed(() => [
    ...locallyCreatedCustomers.value,
    ...props.customers,
]);
const customerOptions = computed(() =>
    allCustomers.value.map((c) => ({
        value: c.id,
        label: c.code ? `${c.code} - ${c.name}` : c.name,
    })),
);
const currencyOptions = computed(() =>
    props.currencies.map((c) => ({
        value: c.id,
        label: `${c.code} - ${c.name}`,
    })),
);

const form = reactive({
    id: null,
    customer_id: "",
    currency_id: "",
    province_id: "",
    ward_id: "",
    address_detail: "",
    expected_delivery_date: "",
    note: "",
    items: [],
});

const subtotal = computed(() =>
    form.items.reduce(
        (sum, item) =>
            sum + Number(item.quantity || 0) * Number(item.unit_price || 0),
        0,
    ),
);
const vatAmount = computed(() =>
    form.items.reduce((sum, item) => {
        const sub = Number(item.quantity || 0) * Number(item.unit_price || 0);
        return sum + (sub * Number(item.vat_percent || 0)) / 100;
    }, 0),
);
const totalAmount = computed(() => subtotal.value + vatAmount.value);

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

// ==================== DATA METHODS ====================
async function loadWards(provinceId) {
    if (!provinceId) {
        wards.value = [];
        return;
    }
    try {
        const res = await axios.get(`/api/provinces/${provinceId}/wards`);
        wards.value = Array.isArray(res.data) ? res.data : [];
    } catch (error) {
        console.error("Lỗi load wards:", error);
        wards.value = [];
    }
}

const loadCustomerData = async (customerId) => {
    if (!customerId) return;
    try {
        const res = await axios.get(`/api/sale/customers/${customerId}/detail`);
        const customer = res.data.customer;

        form.customer_id = String(customer.id);
        form.currency_id = customer.currency_id || customer.currency?.id || "";
        form.province_id = customer.province_id || "";
        form.address_detail = customer.address_detail || "";

        if (form.province_id) {
            await loadWards(form.province_id);
            form.ward_id = customer.ward_id || "";
        }
    } catch (error) {
        console.error("Lỗi tải thông tin khách hàng:", error);
    }
};

function addItem() {
    form.items.push({
        product_id: "",
        quantity: 1,
        unit_price: "",
        vat_percent: 0,
        amount: 0,
        stock_quantity: 0,
        allow_decimal: false,
    });
}

function removeItem(index) {
    if (form.items.length === 1) return;
    form.items.splice(index, 1);
}

function onSelectProduct(item) {
    const product = props.products.find((p) => String(p.id) === String(item.product_id))
        || productOptions.value.find((p) => String(p.value ?? p.id) === String(item.product_id));
    if (!product) return;

    const stock = Number(
        product.stock_quantity ?? product.quantity ?? product.stock ?? 0,
    );
    if (stock <= 0) {
        toast.error("Sản phẩm này đã hết tồn kho");
        item.product_id = "";
        item.unit_price = "";
        item.stock_quantity = 0;
        item.quantity = 0;
        item.amount = 0;
        return;
    }

    item.unit_price = Number(product.sale_price || 0);
    item.stock_quantity = stock;
    item.allow_decimal = Boolean(product.allow_decimal ?? product.unit?.allow_decimal);
    item.quantity = 1;
    item.vat_percent = item.vat_percent || 0;
    calculateItem(item);
}

function handleQuantityChange(item, index) {
    const key = `items.${index}.quantity`;
    const quantity = Number(item.quantity);
    if (!item.allow_decimal && Number.isFinite(quantity) && !Number.isInteger(quantity)) errors.value[key] = ['Đơn vị tính của sản phẩm này không cho phép nhập số lượng lẻ.'];
    else delete errors.value[key];
    const stock = item.stock_quantity || 0;
    if (item.quantity > stock) {
        item.quantity = stock;
        toast.warning(`Số lượng tối đa trong kho chỉ còn ${stock}`);
    }
    calculateItem(item);
}

function handleVatChange(item, index) {
    const key = `items.${index}.vat_percent`;
    if (Number(item.vat_percent) > 10) errors.value[key] = ['VAT không được vượt quá 10%.'];
    else delete errors.value[key];
    calculateItem(item);
}

function calculateItem(item) {
    const qty = Number(item.quantity) || 0;
    const price = Number(item.unit_price) || 0;
    const subtotalItem = qty * price;
    // amount luôn là giá trị trước VAT; VAT được lưu và tổng hợp riêng.
    item.amount = subtotalItem;
}

function lineTotalWithVat(item) {
    const amount = Number(item.quantity || 0) * Number(item.unit_price || 0);
    return amount + amount * (Number(item.vat_percent || 0) / 100);
}

function updateUnitPrice(item, event) {
    item.unit_price = parseNumber(event.target.value);
    calculateItem(item);
}

function onProvinceChange() {
    form.ward_id = "";
    loadWards(form.province_id);
}

function formatNumber(value) {
    if (value === 0) return "0";
    if (!value) return "";
    return formatMoney(value);
}

function parseNumber(value) {
    if (!value && value !== 0) return "";
    return Number(String(value).replace(/[^\d]/g, ""));
}

async function openCustomerModal() {
    try {
        const { data } = await axios.get("/api/currencies/for-select", {
            params: { scope: "all" },
        });
        customerCurrencies.value = Array.isArray(data) ? data : data.data || [];
    } catch (error) {
        customerCurrencies.value = [...props.currencies];
        toast.error("Không thể tải đầy đủ danh sách tiền tệ.");
    }

    showCustomerModal.value = true;
}

function onCustomerCreated(newCustomer) {
    showCustomerModal.value = false;

    if (!newCustomer || !newCustomer.id) return;

    // 1. Thêm vào list local để FormSelect có option hiển thị ngay
    //    (tránh trùng nếu component cha cũng đã push vào props.customers)
    const alreadyExists = allCustomers.value.some(
        (c) => String(c.id) === String(newCustomer.id),
    );
    if (!alreadyExists) {
        locallyCreatedCustomers.value.unshift(newCustomer);
    }

    // 2. Tự fill vào ô chọn khách hàng — trigger watch(form.customer_id)
    //    bên dưới để tự đổ tiền tệ + tỉnh/phường/địa chỉ của khách hàng này
    form.customer_id = String(newCustomer.id);
    emit("customer-created", newCustomer);
}

function resetForm() {
    form.id = null;
    form.customer_id = "";
    form.currency_id = "";
    form.province_id = "";
    form.ward_id = "";
    form.address_detail = "";
    form.expected_delivery_date = "";
    form.note = "";
    form.items = [
        {
            product_id: "",
            quantity: 1,
            unit_price: "",
            vat_percent: 0,
            amount: 0,
            stock_quantity: 0,
            allow_decimal: false,
        },
    ];
    errors.value = {};
}

// ==================== WATCHERS ====================
watch(() => form.province_id, loadWards);

watch(
    () => form.customer_id,
    async (id) => {
        if (!id) return;
        const customer = allCustomers.value.find(
            (c) => String(c.id) === String(id),
        );
        if (!customer) return;

        form.currency_id = customer.currency_id || form.currency_id;
        form.province_id = customer.province_id || "";
        form.address_detail = customer.address_detail || customer.address || "";

        await nextTick();
        if (form.province_id) {
            await loadWards(form.province_id);
            form.ward_id = customer.ward_id || "";
        }
    },
);

watch(
    () => props.order,
    (order) => {
        if (!order) {
            resetForm();
            return;
        }

        form.id = order.id;
        form.customer_id = order.customer_id || order.customer?.id || "";
        form.currency_id = order.currency_id || "";
        form.province_id = order.province_id || "";
        form.ward_id = order.ward_id || "";
        form.address_detail =
            order.address_detail || order.shipping_address || "";
        form.expected_delivery_date = order.expected_delivery_date
            ? String(order.expected_delivery_date).substring(0, 10)
            : "";
        form.note = order.note || "";

        if (order.items?.length > 0) {
            form.items = order.items.map((item) => ({
                product_id: String(item.product_id || item.product?.id),
                quantity: Number(item.quantity),
                unit_price: Number(item.unit_price || item.price || 0),
                vat_percent: Number(item.vat_percent || 0),
                amount: Number(item.amount || 0),
                stock_quantity: Number(item.product?.stock_quantity ?? 0),
                allow_decimal: Boolean(item.product?.allow_decimal ?? item.product?.unit?.allow_decimal),
            }));
            form.items.forEach((item) => calculateItem(item));
        }
        errors.value = {};
    },
    { immediate: true },
);

// ==================== LIFECYCLE ====================
onMounted(async () => {
    if (form.items.length === 0) addItem();

    if (props.products.length > 0) {
        productOptions.value = props.products.map((p) => ({
            value: String(p.id),
            label: p.name,
            sale_price: Number(p.sale_price || 0),
            stock_quantity: Number(p.stock_quantity || 0),
            allow_decimal: Boolean(p.allow_decimal ?? p.unit?.allow_decimal),
        }));
    } else {
        try {
            const res = await axios.get("/api/products/for-select");
            productOptions.value = res.data.map((p) => ({
                value: String(p.id),
                label: p.name,
                sale_price: Number(p.sale_price || 0),
                stock_quantity: Number(p.stock_quantity || 0),
                allow_decimal: Boolean(p.allow_decimal ?? p.unit?.allow_decimal),
            }));
        } catch (e) {
            console.error("Lỗi lấy sản phẩm:", e);
        }
    }

    if (props.customerId) {
        await loadCustomerData(props.customerId);
    }
});

// ==================== SUBMIT ====================
async function submit() {
    errors.value = {};
    loading.value = true;

    const cleanItems = form.items.map((item) => ({
        product_id: item.product_id ? String(item.product_id) : null,
        quantity: item.quantity === "" ? null : Number(item.quantity),
        unit_price: item.unit_price === "" ? null : Number(item.unit_price),
        vat_percent: item.vat_percent === "" ? 0 : Number(item.vat_percent),
        amount: item.amount || 0,
    }));

    const payload = {
        customer_id: form.customer_id || null,
        currency_id: form.currency_id || null,
        province_id: form.province_id || null,
        ward_id: form.ward_id || null,
        address_detail: form.address_detail,
        expected_delivery_date: form.expected_delivery_date,
        note: form.note,
        subtotal: subtotal.value,
        vat_amount: vatAmount.value,
        total_amount: totalAmount.value,
        items: cleanItems,
    };

    try {
        if (form.id) {
            await axios.put(`/api/sale/orders/${form.id}`, payload);
        } else {
            await axios.post("/api/sale/orders", payload);
        }
        toast.success(form.id ? "Cập nhật thành công" : "Tạo đơn thành công");
        emit("saved");
    } catch (error) {
        console.error(error);
        if (error.response && error.response.status === 422) {
            errors.value = { ...error.response.data.errors };
            toast.error(getValidationMessage(error));
        } else {
            toast.error(error.response?.data?.message || "Lưu thất bại");
        }
    } finally {
        loading.value = false;
    }
}
</script>
