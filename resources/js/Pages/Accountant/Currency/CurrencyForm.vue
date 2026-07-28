<template>
    <div class="z-50 w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
        <!-- HEADER -->
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-xl font-bold text-slate-900">
                {{ currency?.id ? "Cập nhật tiền tệ" : "Thêm tiền tệ" }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">Quản lý thông tin và tỷ giá quy đổi của tiền tệ.</p>
        </div>

        <!-- TAB -->
        <div v-if="currency?.id" class="flex gap-2 border-b border-slate-200 px-6 pt-2">
            <button
                @click="activeTab = 'info'"
                class="px-4 py-3 text-sm transition"
                :class="
                    activeTab === 'info'
                        ? 'border-b-2 border-blue-600 text-blue-600 font-medium'
                        : 'text-gray-500'
                "
            >
                Thông tin
            </button>

            <button
                @click="activeTab = 'rates'"
                class="px-4 py-3 text-sm transition"
                :class="
                    activeTab === 'rates'
                        ? 'border-b-2 border-blue-600 text-blue-600 font-medium'
                        : 'text-gray-500'
                "
            >
                Lịch sử tỷ giá
            </button>
        </div>

        <!-- TAB INFO -->
        <div v-if="activeTab === 'info'" class="px-6 py-5">
            <form @submit.prevent="submit" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- CODE -->
                    <div>
                        <label class="block mb-1 text-sm font-medium">
                            Mã tiền tệ
                        </label>

                        <input
                            v-model="form.code"
                            :disabled="currency?.id"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-100"
                            placeholder="VD: USD"
                        />
                    </div>

                    <!-- SYMBOL -->
                    <div>
                        <label class="block mb-1 text-sm font-medium">
                            Ký hiệu
                        </label>

                        <input
                            v-model="form.symbol"
                            :disabled="currency?.is_used"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-100"
                            placeholder="$"
                        />
                    </div>

                    <!-- NAME -->
                    <div>
                        <label class="block mb-1 text-sm font-medium">
                            Tên tiền tệ
                        </label>

                        <input
                            v-model="form.name"
                            :disabled="currency?.is_used"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-100"
                        />
                    </div>

                    <!-- RATE -->
                    <div>
                        <label class="block mb-1 text-sm font-medium">
                            Tỷ giá hiện tại
                        </label>

                        <input
                            type="text"
                            v-model="rateInput"
                            inputmode="decimal"
                            placeholder="Ví dụ: 25.500"
                            @focus="showRawRate"
                            @blur="normalizeRate"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-right font-semibold tabular-nums outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        />
                        <p class="mt-1.5 text-xs text-slate-500">
                            Giá trị quy đổi sang tiền tệ cơ sở.
                        </p>
                    </div>

                    <!-- ACTIVE -->
                    <div>
                        <label class="block mb-1 text-sm font-medium">
                            Trạng thái
                        </label>

                        <select
                            v-model="form.is_active"
                            :disabled="currency?.is_used"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-100"
                        >
                            <option :value="1">Hoạt động</option>

                            <option :value="0">Khóa</option>
                        </select>
                    </div>
                </div>

                <!-- ACTION -->
                <div class="flex justify-end gap-3 pt-5 border-t">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Hủy
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Lưu
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB RATE HISTORY -->
        <CurrencyRateHistory
            v-if="activeTab === 'rates' && currency?.id"
            class="px-6 py-5"
            :currency-id="currency.id"
            :locked="currency.code?.toUpperCase() === 'VND'"
        />
    </div>
</template>

<script setup>
import { reactive, watch, ref } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import CurrencyRateHistory from "../Currency/CurencyRateHistory.vue";

const props = defineProps({
    currency: Object,
});

const emit = defineEmits(["saved", "close"]);

const activeTab = ref("info");
const rateInput = ref("1");

const form = reactive({
    code: "",
    name: "",
    symbol: "",
    exchange_rate: 1,
    is_active: 1,
});

watch(
    () => props.currency,
    (val) => {
        if (!val) return;

        Object.assign(form, {
            code: val.code,
            name: val.name,
            symbol: val.symbol,
            exchange_rate: val.exchange_rate,
            is_active: val.is_active,
        });
        rateInput.value = formatRate(val.exchange_rate);
    },
    {
        immediate: true,
    },
);

function formatRate(value) {
    return Number(value || 0).toLocaleString("vi-VN", {
        maximumFractionDigits: 6,
    });
}

function showRawRate() {
    rateInput.value = String(form.exchange_rate || "");
}

function normalizeRate() {
    const input = String(rateInput.value).trim().replace(/\s/g, "");
    const normalized = /^\d{1,3}(\.\d{3})+$/.test(input)
        ? input.replace(/\./g, "")
        : input.replace(/,/g, ".");
    const value = Number(normalized);

    form.exchange_rate = Number.isFinite(value) && value > 0 ? value : 1;
    rateInput.value = formatRate(form.exchange_rate);
}

async function submit() {
    normalizeRate();
    try {
        if (props.currency?.id) {
            await axios.put(
                `/api/accountant/currencies/${props.currency.id}`,
                form,
            );
        } else {
            await axios.post("/api/accountant/currencies", form);
        }

        emit("saved");
    } catch (error) {
        console.error(error);
    }
}
</script>
