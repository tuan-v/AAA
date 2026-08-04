<template>
    <Head title="Đặt hàng thành công" />
    <main
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-emerald-50 to-indigo-50 p-5"
    >
        <section
            class="w-full max-w-xl rounded-3xl bg-white p-10 text-center shadow-xl"
        >
            <div
                class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 text-4xl text-emerald-600"
            >
                ✓
            </div>
            <h1 class="mt-6 text-3xl font-black">Đặt hàng thành công!</h1>
            <p class="mt-3 text-slate-500">
                Cửa hàng đã nhận được đơn
                <b class="text-slate-800">{{ code }}</b> và sẽ sớm xác nhận.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <Link
                    :href="`/shop/${store.slug}`"
                    class="flex-1 rounded-xl border px-5 py-3 font-bold"
                    >Tiếp tục mua hàng</Link
                ><Link
                    :href="`/shop/${store.slug}/my-account`"
                    class="flex-1 rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white"
                    >Theo dõi đơn hàng</Link
                >
            </div>
        </section>
    </main>
</template>
<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
defineProps({ store: Object });
const code = computed(
    () =>
        new URLSearchParams(usePage().url.split("?")[1] || "").get("code") ||
        "",
);
</script>
