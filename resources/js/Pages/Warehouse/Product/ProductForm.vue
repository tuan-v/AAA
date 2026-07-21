<template>
    <div
        class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden z-50"
    >
        <!-- Header -->
        <div
            class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white"
        >
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">
                        {{ form.id ? "Cập nhật sản phẩm" : "Thêm sản phẩm" }}
                    </h2>
                    <p class="text-sm text-gray-400 mt-0.5">
                        {{
                            form.id
                                ? "Chỉnh sửa thông tin sản phẩm"
                                : "Khai báo sản phẩm mới vào hệ thống"
                        }}
                    </p>
                </div>
            </div>

            <button
                @click="$emit('close')"
                type="button"
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
                X
            </button>
        </div>

        <form @submit.prevent="saveProduct">
            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                <!-- SECTION: THÔNG TIN CƠ BẢN -->
                <div class="mb-6">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-clipboard-text text-base"></i>
                        Thông tin cơ bản
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tên -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Tên sản phẩm <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i
                                    class="ti ti-package absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Nhập tên sản phẩm"
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

                        <!-- SKU -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Mã hàng <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i
                                    class="ti ti-barcode absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    v-model="form.sku"
                                    type="text"
                                    placeholder="Nhập mã hàng"
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                                    :class="errors.sku ? 'border-red-300' : ''"
                                />
                            </div>
                            <p
                                v-if="errors.sku"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.sku[0] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SECTION: PHÂN LOẠI -->
                <div class="mb-6">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-category text-base"></i>
                        Phân loại &amp; đơn vị
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Danh mục -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
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

                            <p
                                v-if="errors.category_id"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.category_id[0] }}
                            </p>
                        </div>

                        <!-- Đơn vị -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
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

                            <p
                                v-if="errors.unit_id"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.unit_id[0] }}
                            </p>
                        </div>

                        <!-- Loại -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Loại sản phẩm
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i
                                    class="ti ti-tags absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"
                                ></i>
                                <select
                                    v-model="form.type"
                                    class="w-full appearance-none border border-gray-200 rounded-lg pl-5 pr-8 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 bg-white"
                                >
                                    <option value="hang_hoa">Hàng hóa</option>
                                    <option value="vat_tu">Vật tư</option>
                                    <option value="dich_vu">Dịch vụ</option>
                                </select>
                                <i
                                    class="ti ti-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"
                                ></i>
                            </div>
                        </div>

                        <!-- Trạng thái -->
                    </div>
                </div>

                <!-- SECTION: HÌNH ẢNH -->
                <div class="mb-6">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-photo text-base"></i>
                        Hình ảnh sản phẩm
                    </h3>

                    <div class="flex items-start gap-4">
                        <label
                            class="flex-1 flex items-center gap-3 border border-dashed border-gray-300 rounded-lg px-4 py-3 cursor-pointer hover:border-blue-400 hover:bg-blue-50/30 transition-colors"
                        >
                            <i
                                class="ti ti-cloud-upload text-2xl text-gray-400"
                            ></i>
                            <div class="text-sm">
                                <p class="font-medium text-gray-600">
                                    Chọn hoặc kéo thả ảnh vào đây
                                </p>
                                <p class="text-gray-400 text-xs mt-0.5">
                                    PNG, JPG tối đa vài MB
                                </p>
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleImage"
                                class="hidden"
                            />
                        </label>

                        <!-- Preview -->
                        <img
                            v-if="previewImage"
                            :src="previewImage"
                            class="w-20 h-20 object-cover rounded-lg border border-gray-200 shrink-0"
                        />
                    </div>
                </div>

                <!-- Mô tả -->
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1.5"
                    >
                        Mô tả
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Mô tả ngắn về sản phẩm..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm transition-colors resize-none focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400"
                    ></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/60"
            >
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                    <i class="ti ti-device-floppy text-base"></i>
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

    if (!categories.value.some((c) => c.id === newCategory.id)) {
        categories.value.unshift(newCategory);
    }

    form.value.category_id = newCategory.id;
}

function onUnitCreated(newUnit) {
    showUnitModal.value = false;

    if (!units.value.some((u) => u.id === newUnit.id)) {
        units.value.unshift(newUnit);
    }

    form.value.unit_id = newUnit.id;
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
            await axios.put(`/api/warehouse/products/${form.value.id}`, data);
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
            toast.warning(
                error.response.data.message || "Dữ liệu sản phẩm không hợp lệ",
            );
        } else {
            toast.error(error.response?.data?.message || "Có lỗi xảy ra khi lưu sản phẩm");
        }
    }
}

// ==================== LOAD DATA ====================
async function loadData() {
    try {
        const [categoryRes, unitRes] = await Promise.all([
            axios.get("/api/purchase/categories/select?active_only=1"),
            axios.get("/api/purchase/units/select?active_only=1"),
        ]);
        categories.value = categoryRes.data.data || categoryRes.data;
        units.value = unitRes.data.data || unitRes.data;
    } catch (e) {
        console.error("Lỗi tải danh mục/đơn vị", e);
    }
}

onMounted(loadData);
</script>
