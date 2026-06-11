<template>
    <Head title="Tạo phiếu nhập kho" />

    <AdminLayout>
        <PageBreadcrumb
            :items="[
                { text: 'Phiếu nhập kho', link: '/warehouse/slips' },
                { text: 'Tạo phiếu nhập', link: null },
            ]"
        />

        <div class="bg-white rounded-xl shadow p-6">
            <!-- HEADER -->
            <div class="border-b pb-4 mb-5">
                <h2 class="text-2xl font-bold">Tạo phiếu nhập kho</h2>

                <!-- MÃ PHIẾU NHẬP (LẤY TỪ BACKEND SAU KHI LOAD / HOẶC PREVIEW) -->
                <div class="mt-2 text-sm text-gray-600">
                    Mã phiếu nhập:
                    <span class="font-semibold text-black">
                        {{ slipCode || "Đang tạo..." }}
                    </span>
                </div>
            </div>

            <!-- CHỌN KHO -->
            <div class="mb-6">
                <label class="block text-sm mb-1"
                    >Kho nhập <span class="text-red">*</span></label
                >
                <select
                    v-model="warehouseId"
                    class="w-full border rounded-lg px-3 py-2"
                >
                    <option value="">Chọn kho</option>
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">
                        {{ w.name }}
                    </option>
                </select>
            </div>

            <!-- THÔNG TIN ĐƠN -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm mb-1">Mã đơn mua</label>
                    <input
                        disabled
                        :value="purchaseOrder?.code"
                        class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                    />
                </div>

                <div>
                    <label class="block text-sm mb-1">Nhà cung cấp</label>
                    <input
                        disabled
                        :value="purchaseOrder?.supplier?.name"
                        class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                    />
                </div>
            </div>

            <!-- TABLE -->
            <table class="w-full border mb-6">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-3">Sản phẩm</th>
                        <th class="border p-3">SL Đã nhập / SL đặt</th>
                        <th class="border p-3">Nhập lần này</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="item in items" :key="item.product_id">
                        <!-- PRODUCT -->
                        <td class="border p-3">
                            {{ item.product?.name }}
                        </td>

                        <!-- SLN / SLĐ -->
                        <td class="border p-3 text-center font-semibold">
                            {{ item.received_quantity || 0 }}
                            /
                            {{ item.quantity }}
                        </td>

                        <!-- INPUT -->
                        <td class="border p-3">
                            <input
                                type="number"
                                min="0"
                                :max="
                                    item.quantity -
                                    (item.received_quantity || 0)
                                "
                                v-model="item.import_quantity"
                                class="w-full border rounded px-3 py-2 text-center"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- SUBMIT -->
            <div class="flex justify-end">
                <button
                    @click="submit"
                    :disabled="loading"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium"
                >
                    {{ loading ? "Đang lưu..." : "Lưu phiếu nhập kho" }}
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

// =====================
// STATE
// =====================
const purchaseOrder = ref(null);
const items = ref([]);
const warehouses = ref([]);
const warehouseId = ref("");
const loading = ref(false);

// 👉 code từ backend (có thể trả về trong API nếu bạn muốn nâng cấp sau)
const slipCode = ref("");

// =====================
// GET ORDER ID
// =====================
const urlParams = new URLSearchParams(window.location.search);
const orderId = urlParams.get("order_id");

// =====================
// LOAD ORDER
// =====================
async function loadOrder() {
    if (!orderId) {
        alert("Không tìm thấy đơn hàng!");
        return;
    }

    const res = await axios.get(`/api/purchase/orders/${orderId}/stock-in`);

    purchaseOrder.value = res.data;

    items.value = (res.data.items || []).map((i) => ({
        ...i,
        import_quantity: 0,
        received_quantity: i.received_quantity || 0,
    }));
}

// =====================
// LOAD WAREHOUSE
// =====================
async function loadWarehouses() {
    const res = await axios.get("/api/warehouses");
    warehouses.value = res.data.data ?? res.data;
}

// =====================
// GENERATE CODE FROM BACKEND (OPTIONAL PREVIEW)
// =====================
// Nếu bạn muốn chuẩn hơn: backend trả slip_code luôn
function fakePreviewCode() {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, "0");

    slipCode.value =
        "PN-" +
        now.getFullYear() +
        pad(now.getMonth() + 1) +
        pad(now.getDate()) +
        pad(now.getHours()) +
        pad(now.getMinutes()) +
        pad(now.getSeconds());
}

// =====================
// SUBMIT
// =====================
async function submit() {
    if (!warehouseId.value) {
        return alert("Vui lòng chọn kho nhập");
    }

    const validItems = items.value.filter((i) => Number(i.import_quantity) > 0);

    if (validItems.length === 0) {
        return alert("Vui lòng nhập ít nhất một sản phẩm");
    }

    loading.value = true;

    try {
        await axios.post("/api/warehouse/slips", {
            order_id: orderId,
            warehouse_id: warehouseId.value,
            items: validItems.map((i) => ({
                product_id: i.product_id,
                import_quantity: Number(i.import_quantity),
            })),
        });

        alert("Tạo phiếu nhập kho thành công!");
        window.location.href = "/warehouse/slips";
    } catch (error) {
        console.error(error);
        alert(error.response?.data?.message || "Có lỗi xảy ra");
    } finally {
        loading.value = false;
    }
}

// =====================
// INIT
// =====================
onMounted(() => {
    fakePreviewCode(); // chỉ preview UI
    if (orderId) loadOrder();
    loadWarehouses();
});
</script>
