<template>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-5 z-50">
        <!-- HEADER -->
        <div class="border-b pb-4 mb-5">
            <h2 class="text-xl font-bold">
                {{ currency?.id ? "Cập nhật tiền tệ" : "Thêm tiền tệ" }}
            </h2>
        </div>

        <!-- TAB -->
        <div v-if="currency?.id" class="flex gap-2 border-b mb-5">
            <button
                @click="activeTab = 'info'"
                class="px-4 py-2"
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
                class="px-4 py-2"
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
        <div v-if="activeTab === 'info'">
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
                            class="w-full border rounded-lg px-3 py-2"
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
                            class="w-full border rounded-lg px-3 py-2"
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
                            class="w-full border rounded-lg px-3 py-2"
                        />
                    </div>

                    <!-- RATE -->
                    <div>
                        <label class="block mb-1 text-sm font-medium">
                            Tỷ giá hiện tại
                        </label>

                        <input
                            type="number"
                            v-model="form.exchange_rate"
                            class="w-full border rounded-lg px-3 py-2"
                        />
                    </div>

                    <!-- ACTIVE -->
                    <div>
                        <label class="block mb-1 text-sm font-medium">
                            Trạng thái
                        </label>

                        <select
                            v-model="form.is_active"
                            class="w-full border rounded-lg px-3 py-2"
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
                        class="px-4 py-2 border rounded-lg"
                    >
                        Hủy
                    </button>

                    <button
                        type="submit"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg"
                    >
                        Lưu
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB RATE HISTORY -->
        <CurrencyRateHistory
            v-if="activeTab === 'rates' && currency?.id"
            :currency-id="currency.id"
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
    },
    {
        immediate: true,
    },
);

async function submit() {
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
