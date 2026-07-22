<template>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">

        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                {{ title }}
            </h2>

            <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ description }}
            </p>
        </div>

        <!-- Content -->
        <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div v-for="(item, index) in items" :key="index" class="flex items-start gap-3">

                <!-- Icon -->
                <div 
                    v-if="item.icons.icon" 
                    :class="['flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-lg', ...item.icons.class]"
                >
                    <component :is="item.icons.icon" class="w-4 h-4" />
                </div>

                <!-- Text -->
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">
                        {{ item.title }}
                    </p>

                    <!-- HTML content -->
                    <div 
                        v-if="item.isHtml" 
                        class="text-sm text-gray-900 dark:text-white" 
                        v-html="item.content"
                    ></div>

                    <!-- Text content -->
                    <p v-else class="text-sm text-gray-900 dark:text-white">
                        {{ item.content }}
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    items: {
        type: Array,
        required: true
    }
})
</script>
