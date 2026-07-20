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

                    <span v-if="createdSlip" class="font-bold text-blue-600">
                        {{ createdSlip.code }}
                    </span>

                    <span v-else class="text-gray-500">
                        Được tạo tự động sau khi lưu
                    </span>
                </div>
            </div>

            <!-- CHỌN KHO -->
            <div class="mb-6">
                <label class="block text-sm mb-1"
                    >Kho nhập <span class="text-red">*</span></label
                >
                <FormSelect
                    v-model="warehouseId"
                    :options="warehouseOptions"
                    label=""
                    placeholder="Tìm hoặc chọn warehouse..."
                    searchable
                    allow-create
                    add-new-text="Thêm kho mới"
                    @add-new="openWarehouseModal"
                />
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
                            {{ item.product?.unit?.name }}
                        </td>

                        <!-- INPUT -->
                        <td class="border p-3">
                            <input
                                type="number"
                                min="0"
                                :step="item.product?.unit?.allow_decimal ? '0.01' : '1'"
                                :max="
                                    item.quantity -
                                    (item.received_quantity || 0)
                                "
                                v-model="item.import_quantity"
                                @input="onInputQuantity(item)"
                                class="w-full border rounded px-3 py-2 text-center"
                            />
                            <p class="mt-1 text-xs text-gray-500 text-center">{{ item.product?.unit?.allow_decimal ? 'Cho phép số lẻ' : 'Chỉ được nhập số nguyên' }}</p>
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
            <!-- DANH SÁCH PHIẾU -->
            <div class="mt-8">
                <h3 class="font-bold text-lg mb-3">
                    Danh sách phiếu nhập đã tạo
                </h3>

                <DataTable
                    :columns="slipColumns"
                    :data="slips"
                    :actions="slipActions"
                    :showIndex="true"
                    emptyMessage="Chưa có phiếu nhập nào"
                />
            </div>
        </div>
    </AdminLayout>
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
import { ref, onMounted, h, computed } from "vue";
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import CheckIcon from "../../../icons/CheckIcon.vue";
import DeleteIcon from "../../../icons/DeleteIcon.vue";
import DetailButtonIcon from "../../../icons/DetailButtonIcon.vue";
import DataTable from "@/components/DataTable.vue";
import Modal from "@/components/Modal.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import FormSelect from "@/components/FormSelect.vue";
import WarehouseForm from "@/Pages/Warehouse/WarehouseForm.vue";
import SlipDetail from "./SlipDetail.vue";
import { usePermission } from "@/composables/usePermission";
import { getValidationMessage } from "@/config/helpers";

const { can } = usePermission();
const showDetailModal = ref(false);
const selectedSlip = ref(null);
const slipColumns = [
    {
        key: "code",
        label: "Mã phiếu",
    },

    {
        label: "Kho nhập",
        render: (row) => h("span", {}, row.warehouse?.name ?? "-"),
    },

    {
        label: "Người tạo",
        render: (row) => h("span", {}, row.created_by?.name ?? "-"),
    },

    {
        key: "created_at",
        label: "Ngày tạo",
    },

    {
        label: "Trạng thái",
        render: (row) => {
            const config = {
                pending: {
                    text: "Chờ duyệt",
                    class: "bg-yellow-100 text-yellow-700",
                },
                approved: {
                    text: "Đã duyệt",
                    class: "bg-green-100 text-green-700",
                },
                rejected: {
                    text: "Từ chối",
                    class: "bg-red-100 text-red-700",
                },
            };

            const status = config[row.status];

            return h(
                "span",
                {
                    class:
                        status.class +
                        " px-3 py-1 rounded-full text-xs font-medium",
                },
                status.text,
            );
        },
    },
];
const slipActions = [
    {
        title: "Duyệt phiếu",
        icon: CheckIcon,
        hidden: (row) =>
            !can("phieu_kho.duyet") || row.status !== "pending",
        onClick: async (row) => {
            try {
                await axios.post(`/api/warehouse/slips/${row.id}/approve`);

                toast.success("Duyệt phiếu thành công", {
                    position: "top-right",
                    autoClose: 3000,
                    theme: "colored",
                });
                await loadOrder();
                await loadSlips();
            } catch (e) {
                toast.error("Không thể duyệt phiếu", {
                    position: "top-right",
                    autoClose: 3000,
                    theme: "colored",
                });
            }
        },
    },

    {
        title: "Từ chối",
        icon: DeleteIcon,
        hidden: (row) =>
            !can("phieu_kho.tu_choi") || row.status !== "pending",
        onClick: async (row) => {
            try {
                await axios.post(`/api/warehouse/slips/${row.id}/reject`);

                toast.success("Từ chối phiếu thành công", {
                    position: "top-right",
                    autoClose: 3000,
                    theme: "colored",
                });
                await loadOrder();
await loadSlips();
            } catch (e) {
            
                toast.error("Không thể từ chối phiếu", {
                    position: "top-right",
                    autoClose: 3000,
                    theme: "colored",
                });
            }
        },
    },
    {
        title: "Chi tiết",
        icon: DetailButtonIcon,
        onClick: openDetail,
    },
];
async function openDetail(row) {
    selectedSlip.value = row;
    showDetailModal.value = true;
}
function onInputQuantity(item) {
    const max = item.quantity - (item.received_quantity || 0);

    if (item.import_quantity > max) {
        item.import_quantity = max;
    }

    if (item.import_quantity < 0 || !item.import_quantity) {
        item.import_quantity = 0;
    }
}
const warehouseOptions = computed(() => {
    return warehouses.value.map((w) => ({
        value: w.id,
        label: w.name,
    }));
});
const showWarehouseModal = ref(false);
const openWarehouseModal = () => {
    showWarehouseModal.value = true;
};
const onWarehouseCreated = (newWarehouse) => {
    showWarehouseModal.value = false;

    if (!newWarehouse) return;

    const exists = warehouses.value.some((w) => w.id == newWarehouse.id);

    if (!exists) {
        warehouses.value.push(newWarehouse);
    }

    warehouseId.value = newWarehouse.id;
};
// =====================
// STATE
// =====================
const slips = ref([]);
const purchaseOrder = ref(null);
const items = ref([]);
const warehouses = ref([]);
const warehouseId = ref("");
const loading = ref(false);
const Order = ref(null);
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
    try {
        const res = await axios.get(
            `/api/warehouse/orders/${orderId}/stock-in`,
        );

        purchaseOrder.value = res.data;

        items.value = (res.data.items || []).map((i) => ({
            ...i,
            import_quantity: 0, // Reset số lượng nhập lần này
            received_quantity: i.received_quantity || 0,
        }));
    } catch (error) {
        console.error(error);
        toast.error("Không thể tải lại dữ liệu đơn hàng");
    }
}
async function loadSlips() {
    const res = await axios.get(
        `/api/warehouse/slips?purchase_order_id=${orderId}`,
    );

    slips.value = res.data.data ?? res.data;
}
// =====================
// LOAD WAREHOUSE
// =====================
async function loadWarehouses() {
    const res = await axios.get("/api/warehouses/all");
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
        return toast.warning("Vui lòng chọn kho nhập");
    }

    const validItems = items.value.filter((i) => Number(i.import_quantity) > 0);

    if (validItems.length === 0) {
        return toast.warning("Vui lòng nhập ít nhất một sản phẩm");
    }

    loading.value = true;

    try {
        const payload = {
            type: "import",
            purchase_order_id: orderId, // Đảm bảo có giá trị
            warehouse_id: warehouseId.value,
            items: validItems.map((i) => ({
                product_id: i.product_id,
                quantity: Number(i.import_quantity),
                price: i.price || i.unit_price || 0,
            })),
            note: "", // thêm tạm nếu cần
        };


        const res = await axios.post("/api/warehouse/slips", payload);

        toast.success(`Tạo phiếu ${res.data.slip.code} thành công!`);
        // SỬA TẠI ĐÂY: Gọi lại API load lại thông tin đơn hàng để đồng bộ số lượng chuẩn xác từ DB
        await loadOrder();

        // Cập nhật lại danh sách phiếu hiển thị ở bảng phía dưới
        await loadSlips();
    } catch (error) {
        console.error("Lỗi tạo phiếu:", error.response?.data);

        if (error.response?.data?.errors) {
            console.table(error.response.data.errors);
            toast.error(getValidationMessage(error));
        } else {
            toast.error(getValidationMessage(error, "Không thể tạo phiếu nhập warehouse."));
        }
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
    loadSlips();
    loadWarehouses();
});
</script>
