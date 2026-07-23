<template>
    <Modal v-if="modelValue" @close="closeModal" size="large">
        <template #body>
            <div class="p-4 bg-white rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">
                            {{ customer?.name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ customer?.code }}
                        </p>
                    </div>
                    <button
                        class="text-gray-400 hover:text-red-500"
                        @click="closeModal"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="detailLoading" class="text-sm text-gray-500">
                    Đang tải...
                </div>

                <div v-else>
                    <div class="grid md:grid-cols-3 gap-3 mb-4">
                        <div class="rounded-xl border p-3 bg-red-50">
                            <p class="text-xs uppercase text-red-500">
                                Còn phải thu
                            </p>
                            <p class="text-xl font-semibold text-red-600">
                                {{ formatMoney(detailSummary.remaining_debt) }}
                            </p>
                        </div>
                        <div class="rounded-xl border p-3 bg-blue-50">
                            <p class="text-xs uppercase text-blue-500">
                                Tổng phát sinh
                            </p>
                            <p class="text-xl font-semibold text-blue-600">
                                {{
                                    formatMoney(detailSummary.total_receivable)
                                }}
                            </p>
                        </div>
                        <div class="rounded-xl border p-3 bg-green-50">
                            <p class="text-xs uppercase text-green-500">
                                Đã thanh toán
                            </p>
                            <p class="text-xl font-semibold text-green-600">
                                {{ formatMoney(detailSummary.total_paid) }}
                            </p>
                        </div>
                    </div>

                    <div class="border rounded-xl overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left p-2">Thời gian</th>
                                    <th class="text-left p-2">Loại</th>
                                    <th class="text-left p-2">Số tiền</th>
                                    <th class="text-left p-2">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in debtHistory"
                                    :key="item.id"
                                    class="border-t"
                                >
                                    <td class="p-2">
                                        {{
                                            item.created_at
                                                ? new Date(
                                                      item.created_at,
                                                  ).toLocaleString()
                                                : "-"
                                        }}
                                    </td>
                                    <td class="p-2">{{ item.type }}</td>
                                    <td
                                        class="p-2"
                                        :class="
                                            item.amount >= 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{ formatMoney(item.amount) }}
                                    </td>
                                    <td class="p-2">{{ item.note || "-" }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch } from "vue";
import axios from "axios";
import Modal from "@/components/Modal.vue";
import { formatMoney as formatMoneyHelper } from "@/config/helpers";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    customer: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue"]);

const detailSummary = ref({});
const debtHistory = ref([]);
const detailLoading = ref(false);

// Theo dõi khi modal được mở và có customer thì mới gọi API
watch(
    () => props.modelValue,
    async (isOpen) => {
        if (isOpen && props.customer?.id) {
            await fetchDetailData();
        }
    },
);

async function fetchDetailData() {
    detailLoading.value = true;
    try {
        const res = await axios.get(
            `/api/accountant/customers-debt/${props.customer.id}/detail`,
        );
        detailSummary.value = res.data.debt_summary || {};
        debtHistory.value = res.data.debt_history || [];
    } catch (error) {
        console.error("Lỗi khi tải chi tiết công nợ:", error);
    } finally {
        detailLoading.value = false;
    }
}

function closeModal() {
    emit("update:modelValue", false);
}

function formatMoney(value) {
    return formatMoneyHelper(value ?? 0);
}

useRealtimeRefresh(() => {
    if (props.modelValue && props.customer?.id) fetchDetailData();
});
</script>
