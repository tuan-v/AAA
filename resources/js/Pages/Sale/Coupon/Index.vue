<template>
    <Head title="Phiếu giảm giá" />
    <AdminLayout>
        <PageBreadcrumb title="" :items="[{ text: 'Phiếu giảm giá', link: null }]" />

        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Danh sách phiếu giảm giá</h2>
                <p class="mt-1 text-sm text-gray-500">Quản lý ưu đãi dùng chung cho POS, website và đơn bán quản trị</p>
            </div>
            <button v-if="can('phieu_giam_gia.them')" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white shadow transition hover:bg-blue-700" @click="openCreate">
                <span class="text-lg leading-none">+</span> Phiếu giảm giá
            </button>
        </div>

        <div class="mb-5 rounded-xl border bg-white p-4 shadow-sm">
            <p class="mb-3 text-sm text-gray-500">Tìm kiếm và lọc phiếu giảm giá</p>
            <SearchPage :filters="filters" @filter="handleFilter" />
        </div>

        <DataTable :columns="columns" :data="coupons.data" :loading="loading" :showIndex="true" :actions="actions"
            :indexOffset="(coupons.current_page - 1) * coupons.per_page" emptyMessage="Không có phiếu giảm giá" />

        <Pagination :totalItems="coupons.total" :itemsPerPage="coupons.per_page" :currentPage="coupons.current_page"
            :doingShow="coupons.data.length" @page-change="fetchCoupons" />
    </AdminLayout>

    <Modal v-if="showModal" @close="closeModal">
        <template #body>
            <CouponForm :coupon="selectedCoupon" @saved="reloadData" @close="closeModal" />
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { computed, h, onMounted, ref } from "vue";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import SearchPage from "@/components/SearchPage.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import CouponForm from "./CouponForm.vue";
import { usePermission } from "@/composables/usePermission";
import { useActionConfirm } from "@/composables/useActionConfirm";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";
import { formatMoney } from "@/config/helpers";
import { toast } from "vue3-toastify";

const { can } = usePermission();
const { confirmAction, alertAction } = useActionConfirm();
const loading = ref(false);
const showModal = ref(false);
const selectedCoupon = ref(null);
const filterParams = ref({});
const coupons = ref({ data: [], total: 0, per_page: 10, current_page: 1, last_page: 1 });
let latestRequest = 0;

const filters = [
    { name: "search", type: "text", placeholder: "Mã / Tên phiếu giảm giá" },
    { name: "type", type: "select", placeholder: "Loại giảm", options: [
        { value: "percent", label: "Theo phần trăm" }, { value: "fixed", label: "Số tiền cố định" },
    ] },
    { name: "scope", type: "select", placeholder: "Phạm vi khách", options: [
        { value: "public", label: "Công khai" }, { value: "personal", label: "Cá nhân" },
    ] },
    { name: "date_range", type: "date_range", placeholder: "Thời gian hiệu lực" },
    { name: "status", type: "select", placeholder: "Trạng thái", options: [
        { value: "active", label: "Đang chạy" }, { value: "draft", label: "Bản nháp" }, { value: "paused", label: "Tạm dừng" },
    ] },
];

const channelLabels = { pos: "POS", web: "Website", admin: "Quản trị" };
const dateText = (value) => value ? new Date(value).toLocaleDateString("vi-VN") : "Không giới hạn";
const columns = [
    { label: "Phạm vi", render: (row) => h("div", {}, [h("span", { class: row.scope === "personal" ? "rounded bg-violet-50 px-2 py-1 text-xs font-medium text-violet-700" : "rounded bg-sky-50 px-2 py-1 text-xs font-medium text-sky-700" }, row.scope === "personal" ? "Cá nhân" : "Công khai"), row.scope === "personal" ? h("div", { class: "mt-1 text-xs text-gray-500" }, `${row.assigned_customers?.length || 0} khách`) : null]) },
    { label: "Mã phiếu", render: (row) => h("div", {}, [h("div", { class: "font-bold text-blue-700" }, row.code), h("div", { class: "mt-0.5 text-xs text-gray-500" }, row.name)]) },
    { label: "Mức giảm", align: "text-right", render: (row) => h("div", { class: "font-semibold text-gray-800" }, [row.type === "percent" ? `${Number(row.value)}%` : formatMoney(row.value), row.maximum_discount ? h("div", { class: "text-xs font-normal text-gray-500" }, `Tối đa ${formatMoney(row.maximum_discount)}`) : null]) },
    { label: "Đơn tối thiểu", align: "text-right", render: (row) => h("span", { class: "font-medium text-gray-800" }, formatMoney(row.minimum_order_amount || 0)) },
    { label: "Kênh áp dụng", render: (row) => h("div", { class: "flex flex-wrap gap-1" }, (row.channels?.length ? row.channels : ["pos", "web", "admin"]).map((item) => h("span", { class: "rounded bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700" }, channelLabels[item]))) },
    { label: "Thời hạn", render: (row) => h("div", { class: "text-xs" }, [h("div", {}, `Từ: ${dateText(row.starts_at)}`), h("div", { class: "mt-1" }, `Đến: ${dateText(row.ends_at)}`)]) },
    { label: "Lượt dùng", align: "text-center", render: (row) => h("div", {}, [h("div", { class: "font-semibold" }, `${row.actual_used_count ?? row.used_count} / ${row.usage_limit ?? "∞"}`), Number(row.reserved_count) ? h("div", { class: "mt-1 text-xs text-amber-600" }, `${row.reserved_count} đang giữ`) : null]) },
    { label: "Trạng thái", align: "text-center", render: (row) => h("span", { class: statusClass(row.status) }, statusText(row.status)) },
];
const actions = computed(() => [
    { icon: EditButtonIcon, type: "edit", hidden: () => !can("phieu_giam_gia.sua"), onClick: openEdit },
    { icon: DeleteIcon, type: "delete", hidden: () => !can("phieu_giam_gia.xoa"), onClick: handleDelete },
]);
function statusText(status) { return ({ active: "Đang chạy", draft: "Bản nháp", paused: "Tạm dừng" })[status] || status; }
function statusClass(status) { return `inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${status === "active" ? "bg-green-50 text-green-700" : status === "paused" ? "bg-amber-50 text-amber-700" : "bg-gray-100 text-gray-600"}`; }
function openCreate() { selectedCoupon.value = null; showModal.value = true; }
function openEdit(item) { selectedCoupon.value = { ...item }; showModal.value = true; }
function closeModal() { showModal.value = false; selectedCoupon.value = null; }
function handleFilter(params) { filterParams.value = params; fetchCoupons(1); }
async function fetchCoupons(page = 1) {
    const requestId = ++latestRequest;
    loading.value = true;
    try {
        const response = await axios.get("/api/sale/coupons", {
            params: { page: Number(page) || 1, per_page: coupons.value.per_page || 10, ...filterParams.value },
            headers: { Accept: "application/json" },
        });
        if (requestId !== latestRequest) return;

        const payload = response.data?.current_page !== undefined
            ? response.data
            : response.data?.data;
        coupons.value = {
            data: Array.isArray(payload?.data) ? payload.data : [],
            total: Number(payload?.total || 0),
            per_page: Number(payload?.per_page || 10),
            current_page: Number(payload?.current_page || 1),
            last_page: Number(payload?.last_page || 1),
        };
    } catch (error) {
        if (requestId !== latestRequest) return;
        toast.error(error.response?.data?.message || "Không thể tải danh sách phiếu giảm giá.");
    } finally {
        if (requestId === latestRequest) loading.value = false;
    }
}
async function reloadData() { closeModal(); await fetchCoupons(coupons.value.current_page || 1); }
async function handleDelete(item) {
    const confirmed = await confirmAction({ title: "Xóa phiếu giảm giá", message: `Xác nhận xóa phiếu “${item.code} - ${item.name}”?`, confirmText: "Xóa", tone: "danger" });
    if (!confirmed) return;
    try { await axios.delete(`/api/sale/coupons/${item.id}`); fetchCoupons(coupons.value.current_page); }
    catch (error) { await alertAction({ title: "Không thể xóa", message: error.response?.data?.message || "Phiếu giảm giá đã được sử dụng.", confirmText: "Đã hiểu", tone: "danger" }); }
}
useRealtimeRefresh(reloadData);
onMounted(() => fetchCoupons(1));
</script>
