<template>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-6xl relative z-50">
        <!-- HEADER -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-2xl px-6 py-5 flex justify-between items-center"
        >
            <div>
                <h2 class="text-xl font-bold text-white">
                    {{ form.id ? "Cập nhật đơn bán hàng" : "Tạo đơn bán hàng" }}
                </h2>
                <p class="text-sm text-blue-200 mt-0.5">Quản lý đơn bán hàng</p>
            </div>
            <button
                @click="$emit('close')"
                class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition"
            >
                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-5">
            <!-- THÔNG TIN ĐƠN HÀNG -->
            <div class="border border-gray-100 rounded-xl p-5 bg-gray-50/60">
                <h3
                    class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2"
                >
                    <span
                        class="w-1 h-4 bg-blue-500 rounded-full inline-block"
                    ></span>
                    Thông tin đơn bán hàng
                </h3>
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
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Tiền tệ</label
                        >
                        <select
                            v-model="form.currency_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
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
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
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
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
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
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Trạng thái</label
                        >
                        <input
                            disabled
                            value="Chờ xử lý"
                            class="w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-400 cursor-not-allowed"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Địa chỉ chi tiết</label
                        >
                        <textarea
                            v-model="form.address_detail"
                            rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Số nhà, ngõ, đường..."
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-600 mb-1"
                            >Ghi chú</label
                        >
                        <textarea
                            v-model="form.note"
                            rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Ghi chú cho đơn hàng..."
                        />
                    </div>
                </div>
            </div>

            <!-- DANH SÁCH SẢN PHẨM -->
            <div class="border border-gray-100 rounded-xl p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3
                        class="text-sm font-semibold text-gray-700 flex items-center gap-2"
                    >
                        <span
                            class="w-1 h-4 bg-indigo-500 rounded-full inline-block"
                        ></span>
                        Danh sách sản phẩm
                    </h3>
                    <button
                        @click="addItem"
                        class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4.5v15m7.5-7.5h-15"
                            />
                        </svg>
                        Sản phẩm
                    </button>
                </div>

                <div
                    class="overflow-x-auto rounded-lg border border-gray-100 overflow-visible"
                >
                    <table class="w-full table-auto min-w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-5/12"
                                >
                                    Sản phẩm
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-20"
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
                                    class="border-b border-gray-100 px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Đơn giá
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                >
                                    Thành tiền
                                </th>
                                <th
                                    class="border-b border-gray-100 px-3 py-2.5 w-10"
                                ></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="hover:bg-blue-50/30 transition"
                            >
                                <td class="px-3 py-2 relative z-50">
                                    <FormSelect
                                        v-model="item.product_id"
                                        :options="productOptions"
                                        searchable
                                        @update:modelValue="
                                            () => onSelectProduct(item)
                                        "
                                    />
                                </td>
                                <td class="px-3 py-2 text-center">
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
                                        :max="item.stock_quantity || 999999"
                                        v-model="item.quantity"
                                        @input="handleQuantityChange(item)"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-center text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        v-model="item.vat_percent"
                                        @input="calculateItem(item)"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-center text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        :value="formatNumber(item.unit_price)"
                                        @input="updateUnitPrice(item, $event)"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-right text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </td>
                                <td
                                    class="px-3 py-2 text-right font-semibold text-green-600"
                                >
                                    {{
                                        formatMoney(
                                            item.amount || 0,
                                            currentCurrency,
                                        )
                                    }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button
                                        @click="removeItem(index)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition mx-auto"
                                    >
                                        <DeleteIcon class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TỔNG TIỀN -->
                <div class="flex justify-end mt-5">
                    <div
                        class="bg-blue-50 border border-blue-100 rounded-xl p-5 min-w-[340px] space-y-2"
                    >
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tạm tính</span>
                            <span class="font-medium text-gray-800">{{
                                formatMoney(subtotal, currentCurrency)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>VAT</span>
                            <span class="font-medium text-gray-800">{{
                                formatMoney(vatAmount, currentCurrency)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between pt-3 border-t border-blue-200 text-base font-bold text-blue-700"
                        >
                            <span>Tổng cộng</span>
                            <span>{{
                                formatMoney(totalAmount, currentCurrency)
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION -->
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
                              ? "Cập nhật đơn"
                              : "Tạo đơn hàng"
                    }}
                </button>
            </div>
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
    customerId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits(["saved", "close", "customer-created"]);

const showCustomerModal = ref(false);
const loading = ref(false);
const wards = ref([]);

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

        toast.success(`Đã tải thông tin khách hàng: ${customer.name}`);
    } catch (error) {
        console.error("Lỗi tải thông tin khách hàng:", error);
        toast.error("Không thể tải đầy đủ thông tin khách hàng");
    }
};

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

    if (stock <= 0) {
        toast.error("Sản phẩm này đã hết tồn kho");
        item.product_id = "";
        item.unit_price = 0;
        item.stock_quantity = 0;
        item.quantity = 0;
        item.amount = 0;
        return;
    }

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
        // Luôn lấy currency_id gốc từ DB (không phải currency đã quy đổi sang tiền công ty)
        form.currency_id = order.currency_id || "";
        form.province_id = order.province_id || "";
        form.ward_id = order.ward_id || "";
        form.address_detail =
            order.address_detail || order.shipping_address || "";
        // Đảm bảo có ngày giao và ghi chú
        form.expected_delivery_date = order.expected_delivery_date
            ? String(order.expected_delivery_date).substring(0, 10)
            : "";
        form.note = order.note || "";

        if (order.items?.length > 0) {
            form.items = order.items.map((item) => ({
                product_id: String(item.product_id || item.product?.id),
                quantity: Number(item.quantity),
                // Lấy unit_price gốc (giá khách hàng), không dùng company_unit_price
                unit_price: Number(item.unit_price || item.price || 0),
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

    if (props.products.length > 0) {
        productOptions.value = props.products.map((p) => ({
            value: String(p.id),
            label: p.name,
            sale_price: Number(p.sale_price || 0),
            stock_quantity: Number(p.stock_quantity || 0),
        }));
    } else {
        const res = await axios.get("/api/products/for-select");
        productOptions.value = res.data.map((p) => ({
            value: String(p.id),
            label: p.name,
            sale_price: Number(p.sale_price || 0),
            stock_quantity: Number(p.stock_quantity || 0),
        }));
    }

    if (props.customerId) {
        await loadCustomerData(props.customerId);
    }
});
</script>
