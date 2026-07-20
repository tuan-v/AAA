<template>
    <div v-if="loading" class="w-full max-w-3xl rounded-xl bg-white p-8 text-center text-gray-500">
        Đang tải chi tiết giao dịch...
    </div>
    <div v-else-if="error" class="w-full max-w-3xl rounded-xl border border-red-200 bg-red-50 p-6 text-red-700">
        {{ error }}
    </div>
    <div
        v-else-if="transaction"
        class="space-y-0 bg-white rounded-xl shadow-lg w-full max-w-3xl p-5 z-50"
    >
        <!-- Header: Mã + Badge trạng thái -->
        <div
            class="flex items-start justify-between flex-wrap gap-3 pb-4 border-b border-gray-200"
        >
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">
                    Mã giao dịch
                </p>
                <p class="text-lg font-medium font-mono">
                    {{ transaction.code }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span
                    :class="statusBadgeClass"
                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-md"
                >
                    <CheckCircleIcon class="w-3.5 h-3.5" />
                    {{ statusLabel }}
                </span>
                <span
                    :class="typeBadgeClass"
                    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-md"
                >
                    <component :is="typeIcon" class="w-3.5 h-3.5" />
                    {{ typeLabel }}
                </span>
                <button
                    @click="$emit('close')"
                    type="button"
                    class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                >
                    <i class="ti ti-x text-xl">X</i>
                </button>
            </div>
        </div>

        <!-- Metric row: Số tiền + Ngày + Loại thanh toán -->
        <div
            class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-gray-200 border-b border-gray-200"
        >
            <div class="py-4 pr-4">
                <p class="text-xs text-gray-500 mb-1">Số tiền</p>
                <p :class="amountClass" class="text-xl font-medium">
                    {{ formatMoney(transaction.amount_base) }} ₫
                </p>
            </div>
            <div class="py-4 px-4">
                <p class="text-xs text-gray-500 mb-1">Ngày giao dịch</p>
                <p class="text-sm font-medium">
                    {{ formatDateOnly(transaction.transaction_date) }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ formatTimeOnly(transaction.transaction_date) }}
                </p>
            </div>
            <div class="py-4 pl-4">
                <p class="text-xs text-gray-500 mb-1">Loại thanh toán</p>
                <p class="text-sm font-medium">
                    {{ transaction.category?.name ?? "—" }}
                </p>
            </div>
            <div class="py-4 pl-4">
                <p class="text-xs text-gray-500 mb-1">Hình thức giao dịch</p>
                <p class="text-sm font-medium">
                    {{ transaction.type === "transfer" ? "Chuyển giữa tài khoản nội bộ" : (transaction.payment_method === "bank_transfer" ? "Chuyển khoản" : "Tiền mặt") }}
                </p>
            </div>
            <div class="py-4 pl-4">
                <p class="text-xs text-gray-500 mb-1">Nghiệp vụ</p>
                <p class="text-sm font-medium">{{ purposeLabel }}</p>
            </div>
        </div>

        <!-- Thông tin tài khoản -->
        <div class="py-4 border-b border-gray-200">
            <p
                class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3"
            >
                Thông tin tài khoản
            </p>
            <div class="grid grid-cols-2 gap-3">
                <div
                    v-if="transaction.from_account"
                    class="bg-gray-50 rounded-lg p-3"
                >
                    <p
                        class="text-xs text-gray-500 mb-1 flex items-center gap-1"
                    >
                        <ArrowUpCircleIcon class="w-3.5 h-3.5" /> Từ tài khoản
                    </p>
                    <p class="text-sm font-medium">
                        {{ transaction.from_account.name }}
                    </p>
                    <p
                        v-if="transaction.from_account.code"
                        class="text-xs text-gray-400 font-mono mt-0.5"
                    >
                        {{ transaction.from_account.code }}
                    </p>
                </div>
                <div
                    v-if="transaction.to_account"
                    class="bg-gray-50 rounded-lg p-3"
                >
                    <p
                        class="text-xs text-gray-500 mb-1 flex items-center gap-1"
                    >
                        <ArrowDownCircleIcon class="w-3.5 h-3.5" /> Đến tài
                        khoản
                    </p>
                    <p class="text-sm font-medium">
                        {{ transaction.to_account.name }}
                    </p>
                    <p
                        v-if="transaction.to_account.code"
                        class="text-xs text-gray-400 font-mono mt-0.5"
                    >
                        {{ transaction.to_account.code }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Người tạo - Người duyệt - Phương thức thanh toán -->
        <div class="py-4 border-b border-gray-200">
            <p
                class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3"
            >
                Thông tin bổ sung
            </p>
            <div class="grid grid-cols-3 gap-4">
                <!-- Người tạo -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1">Người tạo</p>
                    <p class="text-sm font-medium">
                        {{
                            transaction.created_by?.name ||
                            "—"
                        }}
                    </p>
                    <p
                        v-if="
                            transaction.created_by?.email
                        "
                        class="text-xs text-gray-400"
                    >
                        {{
                            transaction.created_by?.email
                        }}
                    </p>
                </div>

                <!-- Người duyệt -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1">Người duyệt</p>
                    <p class="text-sm font-medium">
                        {{
                            transaction.approved_by?.name ||
                            "Chưa duyệt"
                        }}
                    </p>
                    <p
                        v-if="transaction.approved_at"
                        class="text-xs text-gray-400"
                    >
                        {{ formatDate(transaction.approved_at) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Nội dung -->
        <div v-if="transaction.status === 'rejected'" class="border-b border-gray-200 py-4">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-red-500">Thông tin từ chối</p>
            <div class="rounded-lg border border-red-100 bg-red-50 p-3">
                <p class="text-sm text-red-700">{{ transaction.rejection_reason || 'Không có lý do' }}</p>
                <p class="mt-1 text-xs text-red-500">
                    {{ transaction.rejected_by?.name || 'Người từ chối' }}
                    <span v-if="transaction.rejected_at"> · {{ formatDate(transaction.rejected_at) }}</span>
                </p>
            </div>
        </div>

        <div
            v-if="transaction.description"
            class="py-4 border-b border-gray-200"
        >
            <p
                class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2"
            >
                Nội dung
            </p>
            <p
                class="text-sm text-gray-700 bg-gray-50 px-3 py-2.5 rounded-md border-l-2 border-green-500 leading-relaxed"
            >
                {{ transaction.description }}
            </p>
        </div>

        <!-- Footer -->
        <div class="pt-3 flex items-center justify-between flex-wrap gap-2">
            <p class="text-xs text-gray-400">
                Tạo lúc: {{ formatDate(transaction.created_at) }}
                <span v-if="transaction.updated_at">
                    · Cập nhật: {{ formatDate(transaction.updated_at) }}
                </span>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { formatMoney as formatMoneyHelper } from "@/config/helpers";
import {
    CheckCircleIcon,
    ArrowUpCircleIcon,
    ArrowDownCircleIcon,
    ArrowsRightLeftIcon,
    PrinterIcon,
    PencilIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    transactionId: Number,
});

defineEmits(["close", "print", "edit"]);

const transaction = ref(null);
const loading = ref(true);
const error = ref("");

const TYPE_MAP = {
    receipt: {
        label: "Thu tiền",
        icon: ArrowDownCircleIcon,
        badgeClass: "bg-blue-50 text-blue-700",
    },
    payment: {
        label: "Chi tiền",
        icon: ArrowUpCircleIcon,
        badgeClass: "bg-amber-50 text-amber-700",
    },
    transfer: {
        label: "Chuyển nội bộ",
        icon: ArrowsRightLeftIcon,
        badgeClass: "bg-purple-50 text-purple-700",
    },
};

const typeLabel = computed(
    () =>
        TYPE_MAP[transaction.value?.type]?.label ??
        transaction.value?.type ??
        "—",
);
const typeIcon = computed(
    () => TYPE_MAP[transaction.value?.type]?.icon ?? ArrowsRightLeftIcon,
);
const typeBadgeClass = computed(
    () =>
        TYPE_MAP[transaction.value?.type]?.badgeClass ??
        "bg-gray-100 text-gray-600",
);

const PURPOSE_MAP = {
    customer_receipt: "Thu tiền khách hàng",
    supplier_payment: "Thanh toán nhà cung cấp",
    customer_refund: "Hoàn tiền khách hàng",
    supplier_refund: "Nhà cung cấp hoàn tiền",
    internal_transfer: "Chuyển tiền nội bộ",
    other_receipt: "Khoản thu khác",
    other_payment: "Khoản chi khác",
};
const purposeLabel = computed(() => PURPOSE_MAP[transaction.value?.purpose] ?? "—");

const STATUS_MAP = {
    pending: {
        label: "Chờ duyệt",
        badgeClass: "bg-yellow-50 text-yellow-700",
    },
    approved: {
        label: "Đã duyệt",
        badgeClass: "bg-green-50 text-green-700",
    },
    rejected: {
        label: "Từ chối",
        badgeClass: "bg-red-50 text-red-700",
    },
};

const statusLabel = computed(
    () =>
        STATUS_MAP[transaction.value?.status]?.label ??
        transaction.value?.status ??
        "—",
);

const statusBadgeClass = computed(
    () =>
        STATUS_MAP[transaction.value?.status]?.badgeClass ??
        "bg-gray-100 text-gray-600",
);

const amountClass = computed(() => {
    if (transaction.value?.type === "receipt") return "text-green-600";
    if (transaction.value?.type === "payment") return "text-red-600";
    return "text-gray-900";
});

function formatMoney(value) {
    return formatMoneyHelper(value || 0);
}

function formatDate(value) {
    if (!value) return "—";
    return new Date(value).toLocaleString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function formatDateOnly(value) {
    if (!value) return "—";
    return new Date(value).toLocaleDateString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

function formatTimeOnly(value) {
    if (!value) return "";
    return new Date(value).toLocaleTimeString("vi-VN", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
}

async function loadData() {
    loading.value = true;
    error.value = "";
    try {
        const res = await axios.get(
            `/api/accountant/transactions/${props.transactionId}`,
        );
        transaction.value = res.data;
    } catch (e) {
        error.value = e.response?.data?.message || "Không thể tải chi tiết giao dịch.";
    } finally {
        loading.value = false;
    }
}

onMounted(loadData);
</script>
