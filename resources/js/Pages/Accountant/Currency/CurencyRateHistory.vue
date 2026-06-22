<template>
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold">Lịch sử tỷ giá</h3>

            <button
                @click="showCreate = true"
                class="bg-blue-600 text-white px-3 py-2 rounded"
            >
                + Tỷ giá mới
            </button>
        </div>

        <table class="w-full border rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-sm">
                <tr>
                    <th class="p-3 text-left">Ngày hiệu lực</th>

                    <th class="p-3 text-right">Tỷ giá</th>

                    <th class="p-3 text-left">Người cập nhật</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="rate in rates" :key="rate.id" class="border-t">
                    <td class="p-3">
                        {{ rate.effective_date }}
                    </td>

                    <td class="p-3 text-right font-medium">
                        {{ Number(rate.rate).toLocaleString("vi-VN") }}
                    </td>

                    <td class="p-3">
                        {{ rate.creator?.name ?? "-" }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- CREATE RATE -->

        <div
            v-if="showCreate"
            class="fixed inset-0 bg-black/40 flex items-center justify-center"
        >
            <div class="bg-white rounded-xl p-5 w-[500px]">
                <h3 class="font-semibold mb-4">Thêm tỷ giá</h3>

                <div class="space-y-3">
                    <input
                        v-model="newRate.rate"
                        type="number"
                        placeholder="Tỷ giá"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <input
                        v-model="newRate.effective_date"
                        type="date"
                        class="w-full border rounded-lg px-3 py-2"
                    />
                </div>

                <div class="flex justify-end gap-2 mt-5">
                    <button
                        @click="showCreate = false"
                        class="border px-4 py-2 rounded"
                    >
                        Hủy
                    </button>

                    <button
                        @click="saveRate"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        Lưu
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    currencyId: Number,
});

const rates = ref([]);

const showCreate = ref(false);

const newRate = ref({
    rate: "",
    effective_date: "",
});

async function getRates() {
    const res = await axios.get(
        `/api/accountant/currencies/${props.currencyId}/rates`,
    );

    rates.value = res.data;
}

async function saveRate() {
    await axios.post(
        `/api/accountant/currencies/${props.currencyId}/rates`,
        newRate.value,
    );

    showCreate.value = false;

    newRate.value = {
        rate: "",
        effective_date: "",
    };

    getRates();
}

onMounted(() => {
    getRates();
});
</script>
