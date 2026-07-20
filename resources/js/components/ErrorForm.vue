<template>
    <p v-if="message" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd" />
        </svg>
        {{ normalizedMessage }}
    </p>
</template>
<script setup>
import { computed } from "vue";

const props = defineProps({
    message: {
        type: [String, Array, Object],
        default: ""
    }
});

const normalizedMessage = computed(() => {
    if (Array.isArray(props.message)) return props.message[0] ?? "";
    if (props.message && typeof props.message === "object") {
        return Object.values(props.message).flat()[0] ?? "";
    }
    return props.message;
});
</script>

<!-- 
 * Hiển thị thông báo lỗi cho các từng phần tử trong form
 import ErrorForm from '@/components/ErrorForm.vue'
 <ErrorForm :message="errors.name" />
-->
