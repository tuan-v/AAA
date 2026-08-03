<template>
    <form
        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end"
        @submit.prevent="$emit('apply')"
    >
        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2">
            <InputDate
                :model-value="dateFrom"
                label="Từ ngày"
                placeholder="Chọn ngày bắt đầu"
                required
                :clearable="false"
                :config="fromDateConfig"
                @update:model-value="$emit('update:dateFrom', $event)"
            />
            <InputDate
                :model-value="dateTo"
                label="Đến ngày"
                placeholder="Chọn ngày kết thúc"
                required
                :clearable="false"
                :config="toDateConfig"
                @update:model-value="$emit('update:dateTo', $event)"
            />
        </div>
        <button
            type="submit"
            :disabled="loading || !dateFrom || !dateTo"
            class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
            {{ loading ? 'Đang lọc...' : 'Xem dữ liệu' }}
        </button>
    </form>
</template>

<script setup>
import { computed } from "vue";
import InputDate from "@/components/InputDate.vue";

const today = new Date();
const localToday = new Date(today.getTime() - today.getTimezoneOffset() * 60000)
    .toISOString()
    .slice(0, 10);
const fromDateConfig = { maxDate: localToday };

const props = defineProps({
    dateFrom: { type: String, required: true },
    dateTo: { type: String, required: true },
    loading: { type: Boolean, default: false },
});

const toDateConfig = computed(() => ({
    minDate: props.dateFrom || undefined,
    maxDate: localToday,
}));

defineEmits(['update:dateFrom', 'update:dateTo', 'apply']);
</script>
