<template>
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-6xl p-6 relative z-50"
    >
        <!-- HEADER -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold">
                    {{ form.id ? "Cập nhật đơn bán hàng" : "Tạo đơn bán hàng" }}
                </h2>
                <p class="text-sm text-gray-500">Quản lý đơn bán hàng</p>
            </div>
            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-red-500 text-2xl"
            >
                ✕
            </button>
        </div>

        <!-- THÔNG TIN ĐƠN HÀNG -->
        <div class="bg-gray-50 rounded-xl p-5 mb-6">
            <h3 class="font-semibold text-lg mb-4">Thông tin đơn bán hàng</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <div>
                    <label class="block text-sm font-medium mb-1"
                        >Tiền tệ</label
                    >
                    <select
                        v-model="form.currency_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Chọn tiền tệ</option>
                        <option
                            v-for="c in currencies"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.code }} - {{ c.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1"
                        >Tỉnh / Thành phố</label
                    >
                    <FormSelect
                        v-model="form.province_id"
                        :options="provinceOptions"
                        placeholder="Tìm hoặc chọn tỉnh..."
                        searchable
                        @update:modelValue="onProvinceChange"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1"
                        >Phường / Xã</label
                    >
                    <FormSelect
                        v-model="form.ward_id"
                        :options="wardOptions"
                        placeholder="Tìm hoặc chọn phường..."
                        searchable
                        :disabled="!form.province_id"
                    />
                </div>

                <div>
                    <InputDate
                        v-model="form.expected_delivery_date"
                        label="Ngày giao dự kiến"
                        :clearable="true"
                        :config="{ minDate: 'today' }"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1"
                        >Trạng thái</label
                    >
                    <input
                        disabled
                        value="Chờ xử lý"
                        class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1"
                        >Địa chỉ chi tiết</label
                    >
                    <textarea
                        v-model="form.address_detail"
                        rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        placeholder="Số nhà, ngõ, đường..."
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1"
                        >Ghi chú</label
                    >
                    <textarea
                        v-model="form.note"
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        placeholder="Ghi chú cho đơn hàng..."
                    />
                </div>
            </div>
        </div>

        <!-- DANH SÁCH SẢN PHẨM -->
        <div class="bg-white border rounded-xl p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-lg">Danh sách sản phẩm</h3>
                <button
                    @click="addItem"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg flex items-center gap-2"
                >
                    + Thêm sản phẩm
                </button>
            </div>

            <div class="overflow-x-auto border rounded-lg overflow-visible">
                <!-- Thêm overflow-visible -->
                <table class="w-full table-auto min-w-full">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <!-- Tăng z-index cho header -->
                        <tr>
                            <th class="border p-3 text-left w-5/12">
                                Sản phẩm
                            </th>
                            <th class="border p-3 text-center w-20">Tồn kho</th>
                            <th class="border p-3 text-center w-32">
                                Số lượng
                            </th>
                            <th class="border p-3 text-center w-24">VAT %</th>
                            <th class="border p-3 text-right">Đơn giá</th>
                            <th class="border p-3 text-right">Thành tiền</th>
                            <th class="border p-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="hover:bg-gray-50"
                        >
                            <td class="border p-2 relative z-50">
                                <FormSelect
                                    v-model="item.product_id"
                                    :options="productOptions"
                                    searchable
                                    @update:modelValue="
                                        () => onSelectProduct(item)
                                    "
                                />
                            </td>
                            <td class="border p-2 text-center font-medium">
                                {{ item.stock_quantity ?? 0 }}
                            </td>
                            <td class="border p-2 relative">
                                <input
                                    type="number"
                                    min="1"
                                    :max="item.stock_quantity || 999999"
                                    v-model="item.quantity"
                                    @input="handleQuantityChange(item)"
                                    class="w-full border rounded px-3 py-2 text-center"
                                />
                            </td>
                            <td class="border p-2">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    v-model="item.vat_percent"
                                    @input="calculateItem(item)"
                                    class="w-full border rounded px-3 py-2 text-center"
                                />
                            </td>
                            <td class="border p-2">
                                <input
                                    :value="formatNumber(item.unit_price)"
                                    @input="updateUnitPrice(item, $event)"
                                    class="w-full border rounded px-3 py-2 text-right"
                                />
                            </td>
                            <td
                                class="border p-2 text-right font-semibold text-green-600"
                            >
                                {{
                                    formatMoney(
                                        item.amount || 0,
                                        currentCurrency,
                                    )
                                }}
                            </td>
                            <td class="border p-2 text-center">
                                <button
                                    @click="removeItem(index)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <DeleteIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-6">
                <div class="bg-blue-50 rounded-xl p-6 min-w-[380px]">
                    <div class="flex justify-between text-lg">
                        <span class="text-gray-600">Tạm tính</span
                        ><span>{{
                            formatMoney(subtotal, currentCurrency)
                        }}</span>
                    </div>
                    <div class="flex justify-between mt-2 text-lg">
                        <span class="text-gray-600">VAT</span
                        ><span>{{
                            formatMoney(vatAmount, currentCurrency)
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between mt-4 border-t pt-4 text-2xl font-bold text-blue-700"
                    >
                        <span>Tổng: </span
                        ><span>{{
                            formatMoney(totalAmount, currentCurrency)
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION -->
        <div class="flex justify-end gap-3 mt-8">
            <button
                @click="$emit('close')"
                class="px-6 py-3 border rounded-lg hover:bg-gray-50"
            >
                Hủy
            </button>
            <button
                @click="submit"
                :disabled="loading"
                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg disabled:opacity-50 font-medium"
            >
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
                @saved="onCustomerCreated"
                @close="showCustomerModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { reactive, computed, watch, onMounted, nextTick, ref } from "vue";
import axios from "axios";
import { formatMoney } from "@/config/helpers";
import { toast } from "vue3-toastify";

import FormSelect from "@/components/FormSelect.vue";
import InputDate from "@/components/InputDate.vue";
import Modal from "@/components/Modal.vue";
import DeleteIcon from "../../../icons/DeleteIcon.vue";
import CustomerForm from "../Customer/CustomerForm.vue";
const provinceOptions = computed(() =>
    props.provinces.map((p) => ({
        value: p.id,
        label: p.name,
    })),
);
const wardOptions = computed(() =>
    wards.value.map((w) => ({
        value: w.id,
        label: w.name,
    })),
);
const props = defineProps({
    order: { type: Object, default: null },
    customers: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    provinces: { type: Array, default: () => [] },
});

const emit = defineEmits(["saved", "close", "customer-created"]);

const showCustomerModal = ref(false);
const loading = ref(false);
const wards = ref([]);

// Computed
const filteredWards = computed(() => wards.value);

const productOptions = ref([]);
const customerOptions = computed(() =>
    props.customers.map((c) => ({
        value: c.id,
        label: `${c.code || ""} - ${c.name}`,
    })),
);

const currentCurrency = computed(
    () => props.currencies.find((c) => c.id === form.currency_id) || {},
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

// Computed tiền
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

// ==================== LOAD WARDS ====================
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
        toast.error("Không thể tải danh sách phường/xã");
    }
}

// ==================== METHODS ====================
function addItem() {
    form.items.push({
        product_id: "",
        quantity: 0,
        unit_price: 0,
        vat_percent: 0,
        amount: 0,
        stock_quantity: 0,
    });
}

function removeItem(index) {
    if (form.items.length === 1) return;
    form.items.splice(index, 1);
}

function onSelectProduct(item) {
    const product = props.products.find(
        (p) => String(p.id) === String(item.product_id),
    );

    if (!product) return;

    const stock = Number(
        product.stock_quantity ?? product.quantity ?? product.stock ?? 0,
    );

    // ❌ HẾT HÀNG -> TOAST + RESET
    if (stock <= 0) {
        toast.error("Sản phẩm này đã hết tồn kho");

        item.product_id = "";
        item.unit_price = 0;
        item.stock_quantity = 0;
        item.quantity = 0;
        item.amount = 0;

        return;
    }

    // ✅ CÒN HÀNG -> SET DATA
    item.unit_price = Number(product.sale_price || 0);
    item.stock_quantity = stock;
    item.quantity = 1;

    calculateItem(item);
    item.vat_percent = item.vat_percent || 0;
    calculateItem(item);
}

function handleQuantityChange(item) {
    const stock = item.stock_quantity || 0;
    if (item.quantity > stock) {
        item.quantity = stock;
        toast.warning(`Số lượng tối đa chỉ còn ${stock}`);
    }
    calculateItem(item);
}

function calculateItem(item) {
    const qty = Number(item.quantity) || 0;
    const price = Number(item.unit_price) || 0;
    const vat = Number(item.vat_percent) || 0;
    const subtotalItem = qty * price;
    item.amount = subtotalItem + (subtotalItem * vat) / 100;
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
    if (!value) return "";
    return new Intl.NumberFormat("vi-VN").format(value);
}

function parseNumber(value) {
    return Number(String(value).replace(/[^\d]/g, "")) || 0;
}

function openCustomerModal() {
    showCustomerModal.value = true;
}

function onCustomerCreated(newCustomer) {
    showCustomerModal.value = false;
    emit("customer-created", newCustomer);
}

// Submit
async function submit() {
    if (!form.customer_id) return alert("Vui lòng chọn khách hàng");
    if (form.items.length === 0)
        return alert("Vui lòng thêm ít nhất 1 sản phẩm");

    for (const item of form.items) {
        if (!item.product_id)
            return alert("Vui lòng chọn sản phẩm cho tất cả các dòng");
        if (Number(item.quantity) <= 0) return alert("Số lượng phải lớn hơn 0");
        if (Number(item.quantity) > Number(item.stock_quantity || 0)) {
            return alert(`Sản phẩm chỉ còn ${item.stock_quantity} tồn kho`);
        }
    }

    loading.value = true;

    const payload = {
        customer_id: form.customer_id,
        currency_id: form.currency_id,
        province_id: form.province_id || null,
        ward_id: form.ward_id || null,
        address_detail: form.address_detail,
        expected_delivery_date: form.expected_delivery_date,
        note: form.note,
        subtotal: subtotal.value,
        vat_amount: vatAmount.value,
        total_amount: totalAmount.value,
        items: form.items.map((item) => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            vat_percent: item.vat_percent || 0,
            amount: item.amount || 0,
        })),
    };
    console.log(payload);
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
        alert(error.response?.data?.message || "Lưu thất bại");
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    Object.assign(form, {
        id: null,
        customer_id: "",
        currency_id: "",
        province_id: "",
        ward_id: "",
        address_detail: "",
        expected_delivery_date: "",
        note: "",
        items: [
            {
                product_id: "",
                quantity: 0,
                unit_price: 0,
                vat_percent: 0,
                amount: 0,
                stock_quantity: 0,
            },
        ],
    });
}

// ==================== WATCHERS ====================
watch(() => form.province_id, loadWards);

watch(
    () => form.customer_id,
    async (id) => {
        if (!id) return;
        const customer = props.customers.find(
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
        form.currency_id = order.currency_id || order.currency?.id || "";
        form.province_id = order.province_id || "";
        form.ward_id = order.ward_id || "";
        form.address_detail =
            order.address_detail || order.shipping_address || "";
        form.expected_delivery_date = order.expected_delivery_date || "";
        form.note = order.note || "";

        if (order.items?.length > 0) {
            form.items = order.items.map((item) => ({
                product_id: String(item.product_id || item.product?.id),
                quantity: Number(item.quantity),
                unit_price: Number(item.unit_price || item.price),
                vat_percent: Number(item.vat_percent || 0),
                amount: Number(item.amount || 0),
                stock_quantity: Number(item.product?.stock_quantity ?? 0),
            }));
            form.items.forEach((item) => calculateItem(item));
        }
    },
    { immediate: true },
);

onMounted(async () => {
    if (form.items.length === 0) addItem();
    const res = await axios.get("/api/products/for-select");

    productOptions.value = res.data.map((p) => ({
        value: String(p.id),
        label: `${p.name}`,
        sale_price: Number(p.sale_price || 0),
        stock_quantity: Number(p.stock_quantity || 0),
    }));
});
</script>
