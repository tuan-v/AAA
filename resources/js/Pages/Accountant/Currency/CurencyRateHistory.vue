<template>
    <section class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Lịch sử tỷ giá</h3>
                <p class="mt-1 text-sm text-slate-500">Theo dõi tỷ giá theo từng ngày hiệu lực.</p>
            </div>
            <button
                v-if="!locked"
                type="button"
                @click="openCreate"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >
                <span class="text-lg leading-none">+</span> Tỷ giá mới
            </button>
        </div>

        <div v-if="locked" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Tỷ giá VND được cố định ở mức 1 và không thể thay đổi.
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Ngày hiệu lực</th>
                        <th class="px-4 py-3 text-right">Tỷ giá</th>
                        <th class="px-4 py-3 text-left">Người cập nhật</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                    <tr v-if="loading">
                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">Đang tải lịch sử tỷ giá...</td>
                    </tr>
                    <tr v-else-if="rates.length === 0">
                        <td colspan="3" class="px-4 py-10 text-center">
                            <p class="font-medium text-slate-700">Chưa có lịch sử tỷ giá</p>
                            <p class="mt-1 text-xs text-slate-500">Hãy thêm tỷ giá đầu tiên cho tiền tệ này.</p>
                        </td>
                    </tr>
                    <tr v-for="rate in rates" v-else :key="rate.id" class="transition hover:bg-slate-50">
                        <td class="px-4 py-3.5 font-medium text-slate-800">{{ formatDate(rate.effective_date) }}</td>
                        <td class="px-4 py-3.5 text-right font-semibold tabular-nums text-blue-700">{{ formatRate(rate.rate) }}</td>
                        <td class="px-4 py-3.5">{{ rate.creator?.name ?? "-" }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="showCreate" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm" @click.self="closeCreate">
            <form class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl" @submit.prevent="saveRate">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h3 class="text-lg font-bold text-slate-900">Thêm tỷ giá mới</h3>
                    <p class="mt-1 text-sm text-slate-500">Nhập tỷ giá và ngày bắt đầu áp dụng.</p>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tỷ giá mới <span class="text-red-500">*</span></label>
                        <input
                            v-model.number="newRate.rate"
                            type="number"
                            min="0.000001"
                            step="0.000001"
                            inputmode="decimal"
                            placeholder="Ví dụ: 25.500"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-right font-semibold tabular-nums outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />
                        <p class="mt-1.5 text-xs text-slate-500">Giá trị quy đổi sang tiền tệ cơ sở.</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ngày hiệu lực <span class="text-red-500">*</span></label>
                        <input
                            v-model="newRate.effective_date"
                            type="date"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" @click="closeCreate" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Hủy</button>
                    <button type="submit" :disabled="saving" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ saving ? "Đang lưu..." : "Lưu tỷ giá" }}
                    </button>
                </div>
            </form>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

const props = defineProps({
    currencyId: Number,
    locked: { type: Boolean, default: false },
});

const rates = ref([]);
const loading = ref(false);
const saving = ref(false);
const showCreate = ref(false);
const emptyRate = () => ({ rate: "", effective_date: new Date().toISOString().slice(0, 10) });
const newRate = ref(emptyRate());

function formatRate(value) {
    return Number(value || 0).toLocaleString("vi-VN", { maximumFractionDigits: 6 });
}

function formatDate(value) {
    if (!value) return "-";
    const [year, month, day] = String(value).slice(0, 10).split("-");
    return `${day}/${month}/${year}`;
}

function openCreate() {
    newRate.value = emptyRate();
    showCreate.value = true;
}

function closeCreate() {
    if (!saving.value) showCreate.value = false;
}

async function getRates() {
    loading.value = true;
    try {
        const res = await axios.get(`/api/accountant/currencies/${props.currencyId}/rates`);
        rates.value = res.data;
    } finally {
        loading.value = false;
    }
}

async function saveRate() {
    saving.value = true;
    try {
        await axios.post(`/api/accountant/currencies/${props.currencyId}/rates`, newRate.value);
        showCreate.value = false;
        toast.success("Đã cập nhật tỷ giá mới");
        await getRates();
    } catch (error) {
        toast.error(error.response?.data?.message || "Không thể cập nhật tỷ giá");
    } finally {
        saving.value = false;
    }
}

useRealtimeRefresh(getRates);
onMounted(getRates);
</script>
