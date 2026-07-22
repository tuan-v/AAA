<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5 my-5">
        <!-- Metric Cards -->
        <div v-for="metric in metrics" :key="metric.title"
            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-gray-800 dark:text-white/90">
                        {{ metric.value }}
                    </h4>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ metric.title }}</span>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-sm" :class="metric.bgColor">
                    <component :is="metric.icon" class="text-white" />
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
const props = defineProps({
    metrics: {
        type: Array,
        default: () => []
    },
})
</script>
