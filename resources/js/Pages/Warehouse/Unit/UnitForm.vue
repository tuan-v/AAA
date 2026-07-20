<template>
    <UnitForm
        :unit="unit"
        api-base="/api/warehouse/units"
        @saved="$emit('saved', $event)"
        @close="$emit('close')"
    />
</template>

<script setup>
import UnitForm from "../../Purchase/Unit/UnitForm.vue";

defineProps({
    unit: {
        type: Object,
        default: null,
    },
});

defineEmits(["saved", "close"]);
</script>
