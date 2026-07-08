<template>
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-6xl p-6 relative z-50"
    >
        <!-- HEADER -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold">
                    {{ form.id ? "Cập nhật đơn mua" : "Tạo đơn mua hàng" }}
                </h2>
                <p class="text-sm text-gray-500">Quản lý đơn mua hàng</p>
            </div>
            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-red-500"
            >
                ✕
            </button>
        </div>

        <!-- THÔNG TIN -->
        <div class="bg-gray-50 rounded-xl p-5 mb-6">
            <h3 class="font-semibold text-lg mb-4">Thông tin đơn mua</h3>
            <div class="grid grid-cols-2 gap-4">
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

                <div>
                    <label class="block text-sm mb-1"
                        >Tiền tệ<span class="text-red">*</span></label
                    >
                    <select
                        v-model="form.currency_id"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option value="">Chọn tiền tệ</option>
                        <option
                            v-for="c in currencies"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.code }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1"
                        >Dự kiến nhận<span class="text-red">*</span></label
                    >
                    <InputDate
                        v-model="form.expected_received_date"
                        placeholder="Chọn ngày"
                        :clearable="true"
                        :config="{ minDate: 'today' }"
                    />
                </div>

                <div class="col-span-2">
                    <label class="block text-sm mb-1">Ghi chú</label>
                    <textarea
                        rows="3"
                        v-model="form.note"
                        class="w-full border rounded-lg px-3 py-2"
                    />
                </div>
            </div>
        </div>

        <!-- SẢN PHẨM -->
        <div class="bg-white border rounded-xl p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-lg">Danh sách sản phẩm</h3>
                <button
                    @click="addItem"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                >
                    + Sản phẩm
                </button>
            </div>

            <div
                class="overflow-x-auto"
                style="max-height: 60vh; overflow-y: auto"
            >
                <table class="w-full border min-w-[800px]">
                    <thead class="bg-gray-100 sticky top-0 z-20">
                        <tr>
                            <th class="p-3 border">Sản phẩm</th>
                            <th class="p-3 border w-32">SL</th>
                            <th class="p-3 border w-44">Đơn giá</th>
                            <th class="p-3 border w-44">Thành tiền</th>
                            <th class="p-3 border w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.items" :key="index">
                            <td class="border p-2 relative" style="z-index: 30">
                                <FormSelect
                                    v-model="item.product_id"
                                    :options="productOptions"
                                    placeholder="Chọn sản phẩm..."
                                    searchable
                                    allow-create
                                    class="w-full"
                                    :style="{ zIndex: 100 - index }"
                                    @update:modelValue="
                                        () => onSelectProduct(item)
                                    "
                                />
                            </td>
                            <td class="border p-2">
                                <input
                                    type="number"
                                    min="1"
                                    v-model="item.quantity"
                                    class="w-full border rounded px-2 py-2"
                                />
                            </td>
                            <td class="border p-2">
                                <input
                                    :value="formatNumber(item.price)"
                                    @input="
                                        item.price = parseNumber(
                                            $event.target.value,
                                        )
                                    "
                                    class="w-full border rounded px-2 py-2 text-right"
                                />
                            </td>
                            <td class="border p-2 text-right font-semibold">
                                {{
                                    formatMoney(
                                        item.price * item.quantity,
                                        currentCurrency,
                                    )
                                }}
                            </td>
                            <td class="p-2 border text-center">
                                <button
                                    @click="removeItem(index)"
                                    class="text-red-500 hover:text-red-700"
                                    title="Xóa sản phẩm"
                                >
                                    <DeleteIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-5">
                <div class="bg-blue-50 px-5 py-3 rounded-lg">
                    <span class="text-gray-500">Tổng tiền:</span>
                    <span class="font-bold text-xl text-blue-600 ml-2">
                        {{ formatMoney(totalAmount, currentCurrency) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ACTION -->
        <div class="flex justify-end gap-3 mt-6">
            <button @click="$emit('close')" class="px-5 py-2 border rounded-lg">
                Hủy
            </button>
            <button
                @click="submit"
                :disabled="loading"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg disabled:opacity-50"
            >
                {{ loading ? "Đang lưu..." : "Lưu đơn hàng" }}
            </button>
        </div>
    </div>

    <Modal v-if="showSupplierModal" @close="showSupplierModal = false">
        <template #body>
            <SupplierForm
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

const props = defineProps({
    order: { type: Object, default: null },
    suppliers: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
});

const emit = defineEmits(["saved", "close"]);

const showSupplierModal = ref(false);
const loading = ref(false);
const productOptions = ref([]);

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

const currentCurrency = computed(
    () => props.currencies.find((c) => c.id === form.currency_id) || {},
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
    if (!value) return "";
    return new Intl.NumberFormat("vi-VN").format(value);
}

function parseNumber(value) {
    return Number(value.replace(/[^\d]/g, ""));
}

function addItem() {
    form.items.push({ product_id: "", quantity: 1, price: 0 });
}

function removeItem(index) {
    if (form.items.length === 1) return;
    form.items.splice(index, 1);
}

function onSelectProduct(item) {
    const selected = productOptions.value.find(
        (opt) => opt.value === String(item.product_id),
    );
    if (selected) {
        item.price = selected.price;
    }
}

function resetForm() {
    form.id = null;
    form.supplier_id = "";
    form.currency_id = "";
    form.expected_received_date = "";
    form.note = "";
    form.items = [{ product_id: "", quantity: 1, price: 0 }];
}

// ==================== WATCH ORDER ====================
watch(
    () => props.order,
    (order) => {
        console.log("order =", order);
        console.log("expected_received_date =", order?.expected_received_date);

        form.expected_received_date = order?.expected_received_date
            ? String(order.expected_received_date).substring(0, 10)
            : "";

        console.log("form =", form.expected_received_date);
    },
    { immediate: true }
);
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

        // Xử lý items cẩn thận
        if (order.items && order.items.length > 0) {
            form.items = order.items.map((item) => ({
                product_id: String(item.product_id || item.product?.id || ""),
                quantity: Number(item.quantity || 1),
                price: Number(item.price || 0),
            }));
        } else {
            form.items = [{ product_id: "", quantity: 1, price: 0 }];
        }
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
    try {
        if (!form.supplier_id) return alert("Vui lòng chọn nhà cung cấp");
        if (!form.currency_id) return alert("Vui lòng chọn tiền tệ");
        if (!form.items.length) return alert("Vui lòng thêm sản phẩm");

        for (const item of form.items) {
            if (!item.product_id)
                return alert("Vui lòng chọn sản phẩm cho tất cả dòng");
            if (Number(item.quantity) <= 0)
                return alert("Số lượng phải lớn hơn 0");
        }

        loading.value = true;

        const payload = {
            supplier_id: form.supplier_id,
            currency_id: form.currency_id,
            expected_received_date: form.expected_received_date,
            note: form.note,
            items: form.items,
        };

        if (form.id) {
            await axios.put(`/api/purchase/orders/${form.id}`, payload);
        } else {
            await axios.post("/api/purchase/orders", payload);
        }

        emit("saved");
    } catch (error) {
        console.error(error);
        alert(error.response?.data?.message || "Có lỗi xảy ra");
    } finally {
        loading.value = false;
    }
}
</script>
