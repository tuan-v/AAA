<template>
    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-base font-semibold text-gray-800"><span class="h-5 w-1 rounded-full bg-green-500"></span>Lịch sử thanh toán</h2>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ payments.length }} bản ghi</span>
        </div>
        <div v-if="payments.length" class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="p-3 text-left">Ngày</th><th class="p-3 text-left">Mã giao dịch</th><th class="p-3 text-left">Phương thức</th><th class="p-3 text-left">Tài khoản thu/chi</th><th class="p-3 text-left">Đơn liên quan</th><th class="p-3 text-right">Số tiền</th><th class="p-3 text-right">Thao tác</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in payments" :key="item.id" class="hover:bg-gray-50">
                        <td class="p-3">{{ formatDate(item.created_at) }}</td>
                        <td class="p-3 font-semibold text-gray-800">{{ item.transaction?.code || '-' }}</td>
                        <td class="p-3"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ paymentLabel(item.transaction?.payment_method) }}</span></td>
                        <td class="p-3 text-gray-700"><span class="font-semibold">{{ directionLabel(item.transaction) }}</span><span class="block text-xs text-gray-500">{{ accountLabel(paymentAccount(item.transaction)) }}</span></td>
                        <td class="p-3"><button v-if="item.transaction?.order_id" class="font-semibold text-indigo-600 hover:underline" @click="$emit('view-order', item.transaction.order_id)">{{ item.transaction.order_code || `#${item.transaction.order_id}` }}</button><span v-else class="text-gray-400">Không gắn đơn</span></td>
                        <td class="p-3 text-right font-semibold tabular-nums" :class="Number(item.amount) < 0 ? 'text-green-600' : 'text-red-600'">{{ formatMoney(item.amount) }}</td>
                        <td class="p-3 text-right"><button v-if="item.transaction" class="rounded-lg bg-indigo-50 px-3 py-1.5 font-semibold text-indigo-600 hover:bg-indigo-100" @click="selected = item">Chi tiết</button><span v-else class="text-gray-400">-</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="py-10 text-center text-sm text-gray-400">Chưa có lịch sử thanh toán</div>

        <div v-if="selected" class="fixed inset-0 z-[1100] flex items-center justify-center bg-black/45 p-4" @click.self="selected = null">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl"><div class="flex justify-between border-b p-5"><div><p class="text-xs uppercase text-gray-400">Chi tiết thanh toán</p><h3 class="mt-1 text-xl font-bold">{{ selected.transaction.code }}</h3></div><button @click="selected = null">✕</button></div><div class="space-y-3 p-5 text-sm"><Row label="Ngày thanh toán" :value="formatDate(selected.created_at)"/><Row label="Phương thức" :value="paymentLabel(selected.transaction.payment_method)"/><Row :label="directionLabel(selected.transaction)" :value="accountLabel(paymentAccount(selected.transaction))"/><Row label="Số tiền" :value="formatMoney(selected.amount)"/><Row label="Trạng thái" :value="statusLabel(selected.transaction.status)"/><Row label="Nội dung" :value="selected.transaction.description || selected.note || '-'"/><div class="rounded-xl bg-slate-50 p-3"><p class="text-xs uppercase text-gray-400">Đơn liên quan</p><button v-if="selected.transaction.order_id" class="mt-1 font-bold text-indigo-600" @click="$emit('view-order', selected.transaction.order_id)">{{ selected.transaction.order_code || `#${selected.transaction.order_id}` }}</button><p v-else class="mt-1 text-gray-500">Không gắn đơn</p></div></div></div>
        </div>
    </section>
</template>
<script setup>
import { computed, ref } from "vue";
import Row from "@/components/PaymentHistoryRow.vue";
const props = defineProps({ items: { type: Array, default: () => [] }, currency: Object });
defineEmits(["view-order"]);
const selected = ref(null);
const payments = computed(() =>
    props.items.filter(
        (item) => item.type === "payment" || Boolean(item.transaction),
    ),
);
const formatDate = v => v ? new Date(v).toLocaleString("vi-VN") : "-";
const formatMoney = v => `${new Intl.NumberFormat("vi-VN", { maximumFractionDigits: 2 }).format(Math.abs(Number(v || 0)))} ${props.currency?.symbol || props.currency?.code || ""}`;
const paymentLabel = v => ({ cash: "Tiền mặt", bank_transfer: "Chuyển khoản" })[v] || v || "-";
const paymentAccount = transaction => transaction?.direction === "receipt" ? transaction.to_account : transaction?.from_account;
const directionLabel = transaction => transaction?.direction === "receipt" ? "Tài khoản thu" : "Tài khoản chi";
const accountLabel = account => account ? `${account.code ? `${account.code} - ` : ""}${account.name}` : "Chưa xác định";
const statusLabel = v => ({ pending: "Chờ duyệt", approved: "Đã duyệt", rejected: "Từ chối" })[v] || v || "-";
</script>
