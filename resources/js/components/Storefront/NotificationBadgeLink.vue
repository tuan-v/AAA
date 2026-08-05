<template>
    <Link
        :href="`/shop/${slug}/my-account/notifications`"
        class="relative inline-flex items-center justify-center gap-2 rounded-full px-4 py-2.5 text-sm font-bold transition hover:bg-black/5"
        aria-label="Mở thông báo"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
            <path d="M10 21h4" />
        </svg>
        <span v-if="showLabel" class="hidden sm:inline">Thông báo</span>
        <span
            v-if="count > 0"
            class="absolute -right-1 -top-1 grid min-h-5 min-w-5 place-items-center rounded-full bg-red-600 px-1 text-[10px] font-black leading-none text-white ring-2 ring-white"
        >{{ count > 99 ? "99+" : count }}</span>
    </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, onUnmounted, ref } from "vue";
import { useAutoApiRefresh } from "@/composables/useAutoApiRefresh";

const props = defineProps({
    slug: { type: String, required: true },
    showLabel: { type: Boolean, default: true },
});
const count = ref(0);

async function loadCount() {
    try {
        const { data } = await axios.get(`/shop/${props.slug}/account/notifications/unread-count`);
        count.value = Number(data.count || 0);
    } catch (error) {
        if (![401, 403].includes(error.response?.status)) console.error("Không thể tải số thông báo.", error);
    }
}

onMounted(() => {
    loadCount();
    window.addEventListener("storefront-notifications-changed", loadCount);
});
onUnmounted(() => window.removeEventListener("storefront-notifications-changed", loadCount));
useAutoApiRefresh(loadCount, 15000);
</script>
