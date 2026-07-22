<template>
    <Head title="Chuyển kho" />
    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[{ text: 'Chuyển kho', link: null }]"
        />
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Phiếu chuyển kho</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Chuyển nguyên giá trị tồn giữa hai kho.
                </p>
            </div>
            <button
                v-if="can('chuyen_kho.them')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white"
                @click="openCreate"
            >
                + Chuyển kho
            </button>
        </div>
        <div class="mb-4">
            <select
                v-model="status"
                class="rounded-lg border px-3 py-2"
                @change="load(1)"
            >
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ duyệt</option>
                <option value="approved">Đã duyệt</option>
                <option value="cancelled">Đã hủy</option>
            </select>
        </div>
        <DataTable
            :columns="columns"
            :data="transfers.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(transfers.current_page - 1) * transfers.per_page"
            emptyMessage="Chưa có phiếu chuyển kho"
        />
        <Pagination
            :totalItems="transfers.total"
            :itemsPerPage="transfers.per_page"
            :currentPage="transfers.current_page"
            :doingShow="transfers.data.length"
            @page-change="load"
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <form
                class="asfy-modal-scroll relative z-20 max-h-[calc(100vh-3rem)] w-full max-w-3xl space-y-5 overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6"
                @submit.prevent="createTransfer"
            >
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        Tạo phiếu chuyển kho
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Chọn kho nguồn trước để tải sản phẩm còn tồn.
                    </p>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block text-sm font-medium text-gray-700"
                        >Kho nguồn
                        <select
                            v-model="form.from_warehouse_id"
                            required
                            class="mt-1 w-full rounded-lg border px-3 py-2"
                        >
                            <option value="" disabled>Chọn kho nguồn</option>
                            <option
                                v-for="item in warehouses"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-gray-700"
                        >Kho đích
                        <select
                            v-model="form.to_warehouse_id"
                            required
                            class="mt-1 w-full rounded-lg border px-3 py-2"
                        >
                            <option value="" disabled>Chọn kho đích</option>
                            <option
                                v-for="item in destinationWarehouses"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                    </label>
                </div>
                <div
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="grid gap-2 rounded-xl border border-gray-200 bg-gray-50 p-3 sm:grid-cols-[minmax(0,1fr)_140px_auto] sm:items-center"
                >
                    <select
                        v-model="item.product_id"
                        required
                        :disabled="!form.from_warehouse_id || loadingProducts"
                        class="min-w-0 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100"
                    >
                        <option value="" disabled>
                            {{ loadingProducts ? "Đang tải..." : "Chọn sản phẩm" }}
                        </option>
                        <option
                            v-for="product in products"
                            :key="product.id"
                            :value="product.id"
                        >
                            {{ product.sku }} - {{ product.name }}
                            (tồn {{ formatQuantity(product.quantity) }} {{ product.unit_name || "" }})
                        </option>
                    </select>
                    <input
                        v-model.number="item.quantity"
                        required
                        :min="selectedProduct(item)?.allow_decimal ? 0.001 : 1"
                        :step="selectedProduct(item)?.allow_decimal ? 0.001 : 1"
                        :max="selectedProduct(item)?.quantity"
                        :disabled="!item.product_id"
                        type="number"
                        placeholder="Số lượng"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 disabled:bg-gray-100"
                    />
                    <button
                        type="button"
                        class="justify-self-start rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 sm:justify-self-auto"
                        :disabled="form.items.length === 1"
                        @click="form.items.splice(index, 1)"
                    >
                        Xóa
                    </button>
                </div>
                <button
                    type="button"
                    class="rounded-lg px-2 py-1 text-sm font-medium text-blue-600 hover:bg-blue-50"
                    @click="form.items.push({ product_id: '', quantity: 1 })"
                >
                    + Thêm sản phẩm
                </button>
                <textarea
                    v-model="form.note"
                    rows="2"
                    placeholder="Ghi chú"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                ></textarea>
                <p v-if="errorMessage" class="text-sm text-red-600">
                    {{ errorMessage }}
                </p>
                <div class="sticky -bottom-5 -mx-5 flex justify-end gap-2 border-t border-gray-200 bg-white px-5 pb-1 pt-4 sm:-bottom-6 sm:-mx-6 sm:px-6">
                    <button
                        type="button"
                        class="rounded-lg border px-4 py-2"
                        @click="showModal = false"
                    >
                        Hủy
                    </button>
                    <button
                        :disabled="saving"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
                    >
                        {{ saving ? "Đang lưu..." : "Tạo phiếu" }}
                    </button>
                </div>
            </form>
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { computed, h, onMounted, reactive, ref, watch } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import CheckIcon from "@/icons/CheckIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import { formatQuantity, getValidationMessage } from "@/config/helpers";
import { usePermission } from "@/composables/usePermission";
import { useActionConfirm } from "@/composables/useActionConfirm";

const { can } = usePermission();
const { confirmAction } = useActionConfirm();
const transfers = ref({ data: [], total: 0, per_page: 10, current_page: 1 });
const warehouses = ref([]);
const products = ref([]);
const status = ref("");
const showModal = ref(false);
const saving = ref(false);
const loadingProducts = ref(false);
const errorMessage = ref("");
const form = reactive({
    from_warehouse_id: "",
    to_warehouse_id: "",
    note: "",
    items: [{ product_id: "", quantity: 1 }],
});
const destinationWarehouses = computed(() =>
    warehouses.value.filter(
        (item) => String(item.id) !== String(form.from_warehouse_id),
    ),
);
const statusLabels = {
    pending: "Chờ duyệt",
    approved: "Đã duyệt",
    cancelled: "Đã hủy",
};
const statusClasses = {
    pending: "bg-amber-100 text-amber-700",
    approved: "bg-emerald-100 text-emerald-700",
    cancelled: "bg-red-100 text-red-700",
};
const columns = [
    { label: "Mã phiếu", key: "code" },
    {
        label: "Kho nguồn",
        render: (row) => h("span", row.from_warehouse?.name || "-"),
    },
    {
        label: "Kho đích",
        render: (row) => h("span", row.to_warehouse?.name || "-"),
    },
    {
        label: "Sản phẩm",
        render: (row) =>
            h(
                "span",
                row.items
                    ?.map(
                        (item) =>
                            `${item.product?.name} (${formatQuantity(item.quantity)})`,
                    )
                    .join(", ") || "-",
            ),
    },
    { label: "Ghi chú", key: "note" },
    {
        label: "Trạng thái",
        render: (row) =>
            h(
                "span",
                {
                    class: [
                        "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",
                        statusClasses[row.status] || "bg-gray-100 text-gray-700",
                    ],
                },
                statusLabels[row.status] || row.status,
            ),
    },
];
const actions = [
    {
        title: "Duyệt",
        icon: CheckIcon,
        hidden: (row) => !can("chuyen_kho.duyet") || row.status !== "pending",
        confirm: false,
        onClick: approve,
    },
    {
        title: "Hủy",
        icon: DeleteIcon,
        hidden: (row) => !can("chuyen_kho.huy") || row.status !== "pending",
        confirm: false,
        onClick: cancel,
    },
];
async function load(page = 1) {
    const { data } = await axios.get("/api/warehouse/transfers", {
        params: { page, status: status.value || undefined },
    });
    transfers.value = data;
}
function openCreate() {
    Object.assign(form, {
        from_warehouse_id: "",
        to_warehouse_id: "",
        note: "",
        items: [{ product_id: "", quantity: 1 }],
    });
    errorMessage.value = "";
    showModal.value = true;
}
function selectedProduct(item) {
    return products.value.find(
        (product) => String(product.id) === String(item.product_id),
    );
}
async function loadProducts() {
    products.value = [];
    form.items = [{ product_id: "", quantity: 1 }];
    if (!form.from_warehouse_id) return;

    loadingProducts.value = true;
    try {
        const { data } = await axios.get("/api/warehouse/products", {
            params: { warehouse_id: form.from_warehouse_id, per_page: 100 },
        });
        products.value = data.data || [];
    } catch (error) {
        toast.error(getValidationMessage(error, "Không thể tải tồn kho nguồn."));
    } finally {
        loadingProducts.value = false;
    }
}
async function createTransfer() {
    saving.value = true;
    errorMessage.value = "";
    try {
        await axios.post("/api/warehouse/transfers", form);
        showModal.value = false;
        toast.success("Đã tạo phiếu chuyển kho");
        await load(1);
    } catch (error) {
        errorMessage.value = getValidationMessage(
            error,
            "Không thể tạo phiếu chuyển kho.",
        );
    } finally {
        saving.value = false;
    }
}
async function approve(row) {
    const confirmed = await confirmAction({
        title: "Duyệt phiếu chuyển kho",
        message: `Xác nhận duyệt phiếu ${row.code || `#${row.id}`} và cập nhật tồn kho?`,
        confirmText: "Duyệt phiếu",
        tone: "success",
    });
    if (!confirmed) return;
    await axios.post(`/api/warehouse/transfers/${row.id}/approve`);
    toast.success("Đã duyệt phiếu chuyển kho");
    await load(transfers.value.current_page);
}
async function cancel(row) {
    const confirmed = await confirmAction({
        title: "Hủy phiếu chuyển kho",
        message: `Bạn có chắc muốn hủy phiếu ${row.code || `#${row.id}`}?`,
        confirmText: "Hủy phiếu",
        tone: "danger",
    });
    if (!confirmed) return;
    await axios.post(`/api/warehouse/transfers/${row.id}/cancel`);
    toast.success("Đã hủy phiếu chuyển kho");
    await load(transfers.value.current_page);
}
onMounted(async () => {
    const warehouseResponse = await axios.get("/api/warehouses/all");
    warehouses.value = warehouseResponse.data.data || warehouseResponse.data;
    await load();
});

watch(
    () => form.from_warehouse_id,
    () => {
        if (String(form.to_warehouse_id) === String(form.from_warehouse_id)) {
            form.to_warehouse_id = "";
        }
        loadProducts();
    },
);
</script>
