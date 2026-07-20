<template>
    <CategoryForm
        :category="category"
        api-base="/api/warehouse/categories"
        @saved="$emit('saved', $event)"
        @close="$emit('close')"
    />
</template>

<script setup>
import CategoryForm from "../../Purchase/Category/CategoryForm.vue";

defineProps({
    category: {
        type: Object,
        default: null,
    },
});

defineEmits(["saved", "close"]);
</script>
