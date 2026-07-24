<template>
    <Link href="/dashboard" class="lg:hidden" :aria-label="companyName">
        <img
            class="h-9 w-9 rounded-lg border border-slate-200 object-contain shadow-sm dark:border-gray-700"
            :src="companyLogo"
            :alt="`Logo ${companyName}`"
        />
    </Link>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
const company = computed(() => page.props.auth?.user?.company);
const companyLogo = computed(
    () => company.value?.logo || "/resource/asfy-images/asfy-logo.png",
);
const companyName = computed(() => company.value?.name || "Công ty");
</script>
