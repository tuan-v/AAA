<template>
    <Head title="Tạo phiếu xuất kho" />

    <AdminLayout>
        <PageBreadcrumb
            :items="[
                { text: 'Phiếu xuất kho', link: '/warehouse/export-slips' },
                { text: 'Tạo phiếu xuất', link: null },
            ]"
        />

        <div class="bg-white rounded-xl shadow p-6">
            <!-- HEADER -->
            <div class="border-b pb-4 mb-5">
                <h2 class="text-2xl font-bold">Tạo phiếu xuất kho</h2>

                <div class="mt-2 text-sm text-gray-600">
                    Mã phiếu xuất:
                    <span class="font-semibold text-black">
                        {{ slipCode || "Đang tạo..." }}
                    </span>
                </div>
            </div>

            <!-- CHỌN KHO -->
            <div class="mb-6">
                <label class="block text-sm mb-1">
                    Kho xuất <span class="text-red">*</span>
                </label>

                <FormSelect
                    v-model="warehouseId"
                    :options="warehouseOptions"
                    placeholder="Chọn kho xuất..."
                    searchable
                    allow-create
                    add-new-text="Thêm kho mới"
                    @add-new="openWarehouseModal"
                />
            </div>

            <!-- THÔNG TIN ĐƠN -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm mb-1">Mã đơn</label>
                    <input
                        disabled
                        :value="order?.code"
                        class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                    />
                </div>

                <div>
                    <label class="block text-sm mb-1">Khách hàng</label>
                    <input
                        disabled
                        :value="order?.customer?.name"
                        class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                    />
                </div>
            </div>

            <!-- TABLE -->
            <table class="w-full border mb-6">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-3">Sản phẩm</th>
                        <th class="border p-3">Đã xuất / Cần xuất</th>
                        <th class="border p-3">Xuất lần này</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="item in items" :key="item.product_id">
                        <td class="border p-3">
                            {{ item.product?.name }}
                        </td>

                        <td class="border p-3 text-center font-semibold">
                            {{ item.exported_quantity || 0 }}
                            /
                            {{ item.quantity }}
                            {{ item.product?.unit?.name }}
                        </td>

                        <td class="border p-3">
                            <input
                                type="number"
                                min="0"
                                :max="
                                    item.quantity -
                                    (item.exported_quantity || 0)
                                "
                                v-model="item.export_quantity"
                                @input="onInputQuantity(item)"
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
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg"
                >
                    {{ loading ? "Đang lưu..." : "Lưu phiếu xuất kho" }}
                </button>
            </div>

            <!-- DANH SÁCH PHIẾU -->
            <div class="mt-8">
                <h3 class="font-bold text-lg mb-3">
                    Danh sách phiếu xuất đã tạo
                </h3>

                <DataTable
                    :columns="slipColumns"
                    :data="slips"
                    :actions="slipActions"
                    :showIndex="true"
                    emptyMessage="Chưa có phiếu xuất nào"
                />
            </div>
        </div>
    </AdminLayout>

    <!-- MODAL KHO -->
    <Modal v-if="showWarehouseModal" @close="showWarehouseModal = false">
        <template #body>
            <WarehouseForm
                @saved="onWarehouseCreated"
                @close="showWarehouseModal = false"
            />
        </template>
    </Modal>
    <Modal v-if="showDetailModal" @close="showDetailModal = false">
        <template #body>
            <SlipDetail
                :slipId="selectedSlip?.id"
                @close="showDetailModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import axios from "axios";
import { ref, computed, onMounted, h } from "vue";
import { Head } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import FormSelect from "@/components/FormSelect.vue";
import Modal from "@/components/Modal.vue";
import WarehouseForm from "@/Pages/Warehouse/WarehouseForm.vue";
import SlipDetail from "../../Warehouse/Slip/SlipDetail.vue";
import CheckIcon from "../../../icons/CheckIcon.vue";
import DeleteIcon from "../../../icons/DeleteIcon.vue";
import DetailButtonIcon from "../../../icons/DetailButtonIcon.vue";

// ===================== STATE
const slips = ref([]);
const order = ref(null);
const items = ref([]);
const warehouses = ref([]);
const warehouseId = ref("");
const loading = ref(false);
const slipCode = ref("");
const showWarehouseModal = ref(false);
const showDetailModal = ref(false);
const selectedSlip = ref(null);
const orderId = new URLSearchParams(window.location.search).get("order_id");

// ===================== WAREHOUSE
const warehouseOptions = computed(() =>
    warehouses.value.map((w) => ({
        value: w.id,
        label: w.name,
    })),
);

const openWarehouseModal = () => {
    showWarehouseModal.value = true;
};

const onWarehouseCreated = (w) => {
    showWarehouseModal.value = false;
    if (!w) return;

    if (!warehouses.value.find((x) => x.id === w.id)) {
        warehouses.value.push(w);
    }

    warehouseId.value = w.id;
};

// ===================== INPUT CONTROL
function onInputQuantity(item) {
    const max = item.quantity - (item.exported_quantity || 0);

    if (item.export_quantity > max) item.export_quantity = max;
    if (item.export_quantity < 0 || !item.export_quantity)
        item.export_quantity = 0;
}

// ===================== LOAD DATA
async function loadOrder() {
    if (!orderId) {
        toast.error("Không tìm thấy mã đơn hàng");
        return;
    }

    try {
        const res = await axios.get(
            `/api/warehouse/orders/${orderId}/stock-out`,
        );
        console.log("Dữ liệu đơn hàng:", res.data); // ← Debug

        order.value = res.data;
        items.value = (res.data.items || []).map((i) => ({
            ...i,
            export_quantity: 0,
            exported_quantity: i.exported_quantity || 0,
        }));
    } catch (error) {
        console.error(error.response?.data);
        if (error.response?.status === 404) {
            toast.error("Không tìm thấy đơn hàng hoặc đơn hàng không hợp lệ");
        } else {
            toast.error("Lỗi tải thông tin đơn hàng");
        }
    }
}

async function loadSlips() {
    const res = await axios.get("/api/warehouse/slips", {
        params: {
            sales_order_id: orderId,
            type: "export",
        },
    });

    slips.value = res.data.data ?? res.data;
}

async function loadWarehouses() {
    const res = await axios.get("/api/available-for-export", {
        params: {
            order_id: orderId,
        },
    });

    warehouses.value = res.data;
}

// ===================== FAKE CODE
function fakePreviewCode() {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, "0");

    slipCode.value =
        "PX-" +
        d.getFullYear() +
        pad(d.getMonth() + 1) +
        pad(d.getDate()) +
        pad(d.getHours()) +
        pad(d.getMinutes());
}

// ===================== SUBMIT
async function submit() {
    if (!warehouseId.value) {
        return toast.warning("Vui lòng chọn kho xuất");
    }

    const validItems = items.value.filter((i) => Number(i.export_quantity) > 0);

    if (!validItems.length) {
        return toast.warning("Vui lòng nhập số lượng xuất");
    }

    loading.value = true;

    try {
        await axios.post("/api/warehouse/slips", {
            type: "export",
            sales_order_id: orderId,
            warehouse_id: warehouseId.value,
            items: validItems.map((i) => ({
                product_id: i.product_id,
                quantity: Number(i.export_quantity),
                price: i.price,
            })),
        });

        toast.success("Tạo phiếu xuất thành công");
        //
        validItems.forEach((submitted) => {
            const item = items.value.find(
                (i) => i.product_id === submitted.product_id,
            );
            if (item) {
                item.exported_quantity =
                    (item.exported_quantity || 0) +
                    Number(submitted.export_quantity);
                item.export_quantity = 0;
            }
        });
        await loadSlips();
    } catch (e) {
        toast.error(e.response?.data?.message || "Có lỗi xảy ra");
    } finally {
        loading.value = false;
    }
}

// ===================== SLIP TABLE
const slipColumns = [
    { key: "code", label: "Mã phiếu" },
    {
        label: "Kho xuất",
        render: (row) => h("span", {}, row.warehouse?.name ?? "-"),
    },
    {
        label: "Người tạo",
        render: (row) => h("span", {}, row.created_by?.name ?? "-"),
    },
    { key: "created_at", label: "Ngày tạo" },
    {
        label: "Trạng thái",
        render: (row) => {
            const map = {
                pending: {
                    text: "Chờ duyệt",
                    class: "bg-yellow-100 text-yellow-700",
                },
                approved: {
                    text: "Đã duyệt",
                    class: "bg-green-100 text-green-700",
                },
                rejected: { text: "Từ chối", class: "bg-red-100 text-red-700" },
            };

            const s = map[row.status];

            return h(
                "span",
                { class: s.class + " px-3 py-1 rounded-full text-xs" },
                s.text,
            );
        },
    },
];

const slipActions = [
    {
        title: "Duyệt",
        icon: CheckIcon,
        visible: (r) => r.status === "pending",
        onClick: async (row) => {
            await axios.post(`/api/warehouse/slips/${row.id}/approve`);
            toast.success("Duyệt phiếu thành công", {
                position: "top-right",
                autoClose: 3000,
                theme: "colored",
            });
            await loadSlips();
        },
    },
    {
        title: "Từ chối",
        icon: DeleteIcon,
        visible: (r) => r.status === "pending",
        onClick: async (row) => {
            await axios.post(`/api/warehouse/slips/${row.id}/reject`);
            toast.success("Hủy phiếu thành công", {
                position: "top-right",
                autoClose: 3000,
                theme: "colored",
            });
            await loadSlips();
            await loadOrder();
        },
    },
    {
        title: "Chi tiết",
        icon: DetailButtonIcon,
        onClick: openDetail,
    },
];
async function openDetail(row) {
    try {
        const res = await axios.get(`/api/warehouse/slips/${row.id}`);
        selectedSlip.value = res.data;
        showDetailModal.value = true;
    } catch (e) {
        toast.error("Không load được chi tiết phiếu");
    }
}

// ===================== INIT
onMounted(() => {
    fakePreviewCode();
    if (orderId) loadOrder();
    loadSlips();
    loadWarehouses();
});
</script>
