<template>
    <div
        v-if="transaction"
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
            </div>
        </div>

        <!-- Metric row: Số tiền + Ngày + Loại thanh toán -->
        <div
            class="grid grid-cols-3 divide-x divide-gray-200 border-b border-gray-200"
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
        </div>

        <!-- Tài khoản -->
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

        <!-- Nội dung -->
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

        <!-- Footer: timestamps + actions -->
        <div class="pt-3 flex items-center justify-between flex-wrap gap-2">
            <p class="text-xs text-gray-400">
                Tạo lúc: {{ formatDate(transaction.created_at) }}
                <span v-if="transaction.updated_at">
                    · Cập nhật: {{ formatDate(transaction.updated_at) }}</span
                >
            </p>
            <div class="flex gap-2">
                <button
                    @click="$emit('print', transaction)"
                    class="text-xs px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50 flex items-center gap-1"
                >
                    <PrinterIcon class="w-3.5 h-3.5" /> In phiếu
                </button>
                <button
                    @click="$emit('edit', transaction)"
                    class="text-xs px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50 flex items-center gap-1"
                >
                    <PencilIcon class="w-3.5 h-3.5" /> Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
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

defineEmits(["print", "edit"]);

const transaction = ref(null);

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
        label: "Chuyển khoản",
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

const statusBadgeClass = computed(() => "bg-green-50 text-green-700");
const statusLabel = computed(() => "Đã xác nhận");

const amountClass = computed(() => {
    if (transaction.value?.type === "receipt") return "text-green-600";
    if (transaction.value?.type === "payment") return "text-red-600";
    return "text-gray-900";
});

function formatMoney(value) {
    return Number(value || 0).toLocaleString("vi-VN");
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
    const res = await axios.get(
        `/api/accountant/transactions/${props.transactionId}`,
    );
    transaction.value = res.data;
}

onMounted(loadData);
</script>
