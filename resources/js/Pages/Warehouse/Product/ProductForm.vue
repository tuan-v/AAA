<template>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-5 z-50">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold">
                {{ form.id ? "Cập nhật sản phẩm" : "Thêm sản phẩm" }}
            </h2>

            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-red-500"
            >
                ✕
            </button>
        </div>

        <form @submit.prevent="saveProduct">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tên -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tên sản phẩm <span class="text-red-500">*</span>
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <p v-if="errors.name" class="text-red-500 text-sm">
                        {{ errors.name }}
                    </p>
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Mã hàng
                    </label>

                    <input
                        v-model="form.sku"
                        type="text"
                        class="w-full border rounded-lg px-3 py-2"
                    />
                </div>

                <!-- Danh mục -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Danh mục <span class="text-red-500">*</span>
                    </label>

                    <select
                        v-model="form.category_id"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option value="">-- Chọn danh mục --</option>

                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.name }}
                        </option>
                    </select>

                    <p v-if="errors.category_id" class="text-red-500 text-sm">
                        {{ errors.category_id }}
                    </p>
                </div>

                <!-- Đơn vị -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Đơn vị tính <span class="text-red-500">*</span>
                    </label>

                    <select
                        v-model="form.unit_id"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option value="">-- Chọn đơn vị --</option>

                        <option
                            v-for="unit in units"
                            :key="unit.id"
                            :value="unit.id"
                        >
                            {{ unit.name }}
                        </option>
                    </select>

                    <p v-if="errors.unit_id" class="text-red-500 text-sm">
                        {{ errors.unit_id }}
                    </p>
                </div>

                <!-- Loại -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Loại sản phẩm <span class="text-red-500">*</span>
                    </label>

                    <select
                        v-model="form.type"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option value="hang_hoa">Hàng hóa</option>
                        <option value="vat_tu">Vật tư</option>
                        <option value="dich_vu">Dịch vụ</option>
                    </select>
                </div>

                <!-- Số lượng -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tồn kho <span class="text-red-500">*</span>
                    </label>

                    <input
                        v-model="form.quantity"
                        type="number"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <p v-if="errors.quantity" class="text-red-500 text-sm">
                        {{ errors.quantity }}
                    </p>
                </div>

                <!-- Giá nhập -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Giá nhập <span class="text-red-500">*</span>
                    </label>

                    <input
                        :value="purchasePriceDisplay"
                        @input="handlePurchasePrice"
                        type="text"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <p
                        v-if="errors.purchase_price"
                        class="text-red-500 text-sm"
                    >
                        {{ errors.purchase_price }}
                    </p>
                </div>

                <!-- Giá bán -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Giá bán <span class="text-red-500">*</span>
                    </label>

                    <input
                        :value="sellPriceDisplay"
                        @input="handleSellPrice"
                        type="text"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <p v-if="errors.sell_price" class="text-red-500 text-sm">
                        {{ errors.sell_price }}
                    </p>
                </div>

                <!-- Trạng thái -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Trạng thái
                    </label>

                    <select
                        v-model="form.status"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Ẩn</option>
                    </select>
                </div>

                <!-- Upload ảnh -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Hình ảnh
                    </label>

                    <input
                        type="file"
                        accept="image/*"
                        @change="handleImage"
                        class="w-full border rounded-lg px-3 py-2"
                    />
                </div>
            </div>

            <!-- Preview -->
            <div v-if="previewImage" class="mt-4">
                <img
                    :src="previewImage"
                    class="w-32 h-32 object-cover rounded-lg border"
                />
            </div>

            <!-- Mô tả -->
            <div class="mt-4">
                <label class="block text-sm font-medium mb-1"> Mô tả </label>

                <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full border rounded-lg px-3 py-2"
                ></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 mt-5">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 border rounded-lg"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    {{ form.id ? "Cập nhật" : "Lưu" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, watch, onMounted } from "vue";
import { formatMoney, removeMoneyFormat } from "@/config/helpers";

const purchasePriceDisplay = ref("");
const sellPriceDisplay = ref("");
const props = defineProps({
    product: Object,
});

const emit = defineEmits(["saved", "close"]);

const categories = ref([]);
const units = ref([]);
const previewImage = ref(null);

const errors = ref({});

const form = ref({
    id: null,
    name: "",
    sku: "",
    category_id: "",
    unit_id: "",
    type: "hang_hoa",
    purchase_price: 0,
    sell_price: 0,
    quantity: 0,
    status: "active",
    description: "",
    image: null,
});

watch(
    () => props.product,
    (p) => {
        errors.value = {};

        if (!p) {
            form.value = {
                id: null,
                name: "",
                sku: "",
                category_id: "",
                unit_id: "",
                type: "hang_hoa",
                purchase_price: 0,
                sell_price: 0,
                quantity: 0,
                status: "active",
                description: "",
                image: null,
            };
            previewImage.value = null;
            return;
        }

        form.value = {
            ...form.value,
            ...p,
            image: null, // QUAN TRỌNG: không giữ URL làm file
        };

        previewImage.value = p.image || null;
    },
    { immediate: true },
);

watch(
    () => form.value.purchase_price,
    (value) => {
        purchasePriceDisplay.value = formatMoney(value);
    },
    { immediate: true },
);

watch(
    () => form.value.sell_price,
    (value) => {
        sellPriceDisplay.value = formatMoney(value);
    },
    { immediate: true },
);
function handlePurchasePrice(e) {
    const raw = removeMoneyFormat(e.target.value);

    form.value.purchase_price = raw;
    purchasePriceDisplay.value = formatMoney(raw);
}

function handleSellPrice(e) {
    const raw = removeMoneyFormat(e.target.value);

    form.value.sell_price = raw;
    sellPriceDisplay.value = formatMoney(raw);
}
function handleImage(e) {
    const file = e.target.files[0];

    if (!file) return;

    form.value.image = file;
    previewImage.value = URL.createObjectURL(file);
}

async function saveProduct() {
    errors.value = {};

    const data = new FormData();

    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) {
            data.append(key, form.value[key]);
        }
    });

    try {
        if (form.value.id) {
            data.append("_method", "PUT");

            await axios.post(`/api/warehouse/products/${form.value.id}`, data);
        } else {
            await axios.post("/api/warehouse/products", data);
        }

        emit("saved");
        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
}

async function loadData() {
    const [categoryRes, unitRes] = await Promise.all([
        axios.get("/api/categories"),
        axios.get("/api/units"),
    ]);

    categories.value = categoryRes.data.data;
    units.value = unitRes.data.data;
}

onMounted(loadData);
</script>
