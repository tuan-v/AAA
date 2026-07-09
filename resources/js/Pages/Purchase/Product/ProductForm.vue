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
                        {{ errors.name[0] }}
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
                    <p v-if="errors.sku" class="text-red-500 text-sm">
                        {{ errors.sku[0] }}
                    </p>
                </div>

                <!-- Danh mục -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Danh mục <span class="text-red-500">*</span>
                    </label>

                    <FormSelect
                        v-model="form.category_id"
                        :options="categoryOptions"
                        searchable
                        allow-create
                        add-new-text="Thêm danh mục mới"
                        @add-new="showCategoryModal = true"
                    />

                    <p v-if="errors.category_id" class="text-red-500 text-sm">
                        {{ errors.category_id[0] }}
                    </p>
                </div>

                <!-- Đơn vị -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Đơn vị tính <span class="text-red-500">*</span>
                    </label>

                    <FormSelect
                        v-model="form.unit_id"
                        :options="unitOptions"
                        searchable
                        allow-create
                        add-new-text="Thêm đơn vị mới"
                        @add-new="showUnitModal = true"
                    />

                    <p v-if="errors.unit_id" class="text-red-500 text-sm">
                        {{ errors.unit_id[0] }}
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
    <Modal v-if="showCategoryModal" @close="showCategoryModal = false">
        <template #body>
            <CategoryForm
                @saved="onCategoryCreated"
                @close="showCategoryModal = false"
            />
        </template>
    </Modal>

    <Modal v-if="showUnitModal" @close="showUnitModal = false">
        <template #body>
            <UnitForm @saved="onUnitCreated" @close="showUnitModal = false" />
        </template>
    </Modal>
</template>

<script setup>
import axios from "axios";
import { ref, watch, onMounted, computed } from "vue";
import { formatMoney, removeMoneyFormat } from "@/config/helpers";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import FormSelect from "../../../components/FormSelect.vue";
import CategoryForm from "../Category/CategoryForm.vue";
import UnitForm from "../Unit/UnitForm.vue";
import Modal from "@/components/Modal.vue";
const showCategoryModal = ref(false);
const showUnitModal = ref(false);
const purchasePriceDisplay = ref("");
const sellPriceDisplay = ref("");
const categoryOptions = computed(() =>
    categories.value.map((c) => ({
        value: c.id,
        label: c.name,
    })),
);

const unitOptions = computed(() =>
    units.value.map((u) => ({
        value: u.id,
        label: u.name,
    })),
);
const props = defineProps({
    product: { type: Object, default: null },
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

// ==================== WATCH ====================
watch(
    () => props.product,
    (p) => {
        errors.value = {};

        if (!p) {
            // Reset form
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
            purchasePriceDisplay.value = "";
            sellPriceDisplay.value = "";
            previewImage.value = null;
            return;
        }

        // Gán dữ liệu an toàn
        form.value.id = p.id;
        form.value.name = p.name || "";
        form.value.sku = p.sku || "";
        form.value.category_id = p.category_id || "";
        form.value.unit_id = p.unit_id || "";
        form.value.type = p.type || "hang_hoa";
        form.value.purchase_price = Number(p.purchase_price || 0);
        form.value.sell_price = Number(p.sell_price || 0);
        form.value.quantity = Number(p.quantity || 0);
        form.value.status = p.status || "active";
        form.value.description = p.description || "";
        form.value.image = null;

        // Format hiển thị giá
        purchasePriceDisplay.value = formatMoney(form.value.purchase_price);
        sellPriceDisplay.value = formatMoney(form.value.sell_price);

        // Preview ảnh (nếu có URL)
        previewImage.value = p.image ? p.image : null;
    },
    { immediate: true, deep: true },
);

// ==================== HANDLERS ====================
function handlePurchasePrice(e) {
    const raw = removeMoneyFormat(e.target.value);
    form.value.purchase_price = Number(raw || 0);
    purchasePriceDisplay.value = e.target.value;
}

function handleSellPrice(e) {
    const raw = removeMoneyFormat(e.target.value);
    form.value.sell_price = Number(raw || 0);
    sellPriceDisplay.value = e.target.value;
}
function onCategoryCreated(newCategory) {
    showCategoryModal.value = false;

    if (!categories.value.some((c) => c.id == newCategory.id)) {
        categories.value.push(newCategory);
    }

    form.category_id = newCategory.id;
}
function onUnitCreated(newUnit) {
    showUnitModal.value = false;

    if (!units.value.some((u) => u.id == newUnit.id)) {
        units.value.push(newUnit);
    }

    form.unit_id = newUnit.id;
}
function formatPurchaseBlur() {
    purchasePriceDisplay.value = formatMoney(form.value.purchase_price || 0);
}

function formatSellBlur() {
    sellPriceDisplay.value = formatMoney(form.value.sell_price || 0);
}

function handleImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    form.value.image = file;
    previewImage.value = URL.createObjectURL(file);
}

// ==================== SAVE ====================
async function saveProduct() {
    errors.value = {};

    const data = new FormData();
    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null && form.value[key] !== undefined) {
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

        toast.success(
            form.value.id
                ? "Cập nhật sản phẩm thành công"
                : "Thêm sản phẩm thành công",
        );
        emit("saved");
        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            toast.error("Có lỗi xảy ra khi lưu sản phẩm");
        }
    }
}

// ==================== LOAD DATA ====================
async function loadData() {
    try {
        const [categoryRes, unitRes] = await Promise.all([
            axios.get("/api/categories"),
            axios.get("/api/units"),
        ]);
        categories.value = categoryRes.data.data || categoryRes.data;
        units.value = unitRes.data.data || unitRes.data;
    } catch (e) {
        console.error("Lỗi tải danh mục/đơn vị", e);
    }
}

onMounted(loadData);
</script>
