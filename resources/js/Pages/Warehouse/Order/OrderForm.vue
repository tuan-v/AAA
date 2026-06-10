<template>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-5 z-50">
        <!-- HEADER -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-lg font-semibold">
                {{ form.id ? "Cập nhật đơn hàng" : "Tạo đơn hàng" }}
            </h2>

            <button @click="$emit('close')">✕</button>
        </div>

        <!-- TYPE -->
        <div class="mb-3">
            <label class="text-sm">Loại đơn</label>
            <select v-model="form.type" class="w-full border p-2 rounded">
                <option value="purchase">Đơn mua</option>
                <option value="sale">Đơn bán</option>
            </select>
        </div>

        <!-- WAREHOUSE -->
        <div class="mb-3">
            <label class="text-sm">Kho</label>
            <select
                v-model="form.warehouse_id"
                class="w-full border p-2 rounded"
            >
                <option value="">-- chọn kho --</option>
                <option v-for="w in warehouses" :key="w.id" :value="w.id">
                    {{ w.name }}
                </option>
            </select>
        </div>

        <!-- ITEMS -->
        <div class="mb-3">
            <div class="flex justify-between items-center mb-2">
                <label class="text-sm font-medium">Sản phẩm</label>

                <button
                    type="button"
                    @click="addItem"
                    class="text-blue-600 text-sm"
                >
                    + Thêm
                </button>
            </div>

            <div
                v-for="(item, index) in form.items"
                :key="index"
                class="flex gap-2 mb-2"
            >
                <!-- PRODUCT -->
                <select
                    v-model="item.product_id"
                    class="border p-2 rounded w-2/3"
                >
                    <option value="">Chọn sản phẩm</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">
                        {{ p.name }}
                    </option>
                </select>

                <!-- QUANTITY -->
                <input
                    type="number"
                    v-model="item.quantity"
                    min="1"
                    class="border p-2 rounded w-1/4"
                    placeholder="SL"
                />

                <!-- REMOVE -->
                <button
                    type="button"
                    @click="removeItem(index)"
                    class="bg-red-500 text-white px-2 rounded"
                >
                    X
                </button>
            </div>
        </div>

        <!-- NOTE -->
        <div class="mb-3">
            <label class="text-sm">Ghi chú</label>
            <textarea
                v-model="form.note"
                class="w-full border p-2 rounded"
            ></textarea>
        </div>

        <!-- ACTION -->
        <div class="flex justify-end gap-2">
            <button @click="$emit('close')" class="px-4 py-2 border rounded">
                Hủy
            </button>

            <button
                @click="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Lưu
            </button>
        </div>
    </div>
</template>
<script setup>
import { reactive, watch } from "vue";
import axios from "axios";

const props = defineProps({
    order: Object,
    products: Array,
    warehouses: Array,
});

const emit = defineEmits(["saved", "close"]);

const form = reactive({
    id: null,
    type: "purchase",
    warehouse_id: null,
    note: "",
    items: [
        {
            product_id: "",
            quantity: 1,
        },
    ],
});

// 👉 nếu edit thì fill data
watch(
    () => [props.order, props.products, props.warehouses],
    ([order, products, warehouses]) => {
        if (!order) return;

        form.id = order.id;
        form.type = order.type;
        form.warehouse_id = order.warehouse_id;
        form.note = order.note;

        form.items = order.items?.length
            ? order.items.map((i) => ({
                  product_id: i.product_id ?? i.product?.id,
                  quantity: i.quantity,
              }))
            : [{ product_id: "", quantity: 1 }];
    },
    { immediate: true },
);
// ➕ add item
function addItem() {
    form.items.push({
        product_id: "",
        quantity: 1,
    });
}

// ❌ remove item
function removeItem(index) {
    form.items.splice(index, 1);
}

// 💾 submit
async function submit() {
    try {
        if (form.id) {
            await axios.put(`/api/warehouse/orders/${form.id}`, form);
        } else {
            await axios.post("/api/warehouse/orders", form);
        }

        emit("saved");
    } catch (err) {
        console.error(err);
        alert("Có lỗi xảy ra");
    }
}
</script>
