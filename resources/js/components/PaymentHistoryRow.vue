<template><div class="flex justify-between gap-4 rounded-xl bg-slate-50 p-3"><span class="text-gray-500">{{ label }}</span><span class="text-right font-semibold text-gray-800">{{ value }}</span></div></template>
<script setup>defineProps({ label: String, value: [String, Number] });</script>
