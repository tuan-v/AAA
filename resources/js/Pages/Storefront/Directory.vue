<template>
    <Head title="Chọn cửa hàng" />
    <main class="min-h-screen bg-[#f6f6f2] text-neutral-950">
        <section
            class="relative overflow-hidden bg-[#0a0a0a] px-6 py-28 text-white md:py-40"
        >
            <div
                class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#c6ff3d]/20 blur-3xl"
            ></div>
            <div class="mx-auto max-w-6xl">
                <p
                    class="text-xs font-bold uppercase tracking-[0.35em] text-[#c6ff3d]"
                >
                    Marketplace / Multi-store
                </p>
                <h1
                    class="mt-7 max-w-4xl text-5xl font-black leading-[0.95] tracking-[-0.05em] md:text-8xl"
                >
                    Chọn đúng nơi.<br />Mua đúng sản phẩm.
                </h1>
                <p class="mt-8 max-w-xl text-base leading-7 text-neutral-400">
                    Mỗi cửa hàng vận hành độc lập với sản phẩm, giá bán và đơn
                    hàng riêng.
                </p>
            </div>
        </section>
        <section class="mx-auto max-w-6xl px-6 py-12">
            <div
                v-if="!stores.length"
                class="rounded-3xl border border-dashed bg-white p-12 text-center text-slate-500"
            >
                Chưa có cửa hàng nào đang mở bán.
            </div>
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="store in stores"
                    :key="store.slug"
                    :href="`/shop/${store.slug}`"
                    class="group rounded-2xl border border-neutral-200 bg-white p-6 transition duration-200 hover:-translate-y-1 hover:border-neutral-950"
                >
                    <div class="flex items-center gap-4">
                        <img
                            v-if="store.logo"
                            :src="store.logo"
                            :alt="store.name"
                            class="h-16 w-16 rounded-2xl border object-cover"
                        />
                        <div
                            v-else
                            class="flex h-16 w-16 items-center justify-center rounded-2xl bg-neutral-950 text-2xl font-black text-[#c6ff3d]"
                        >
                            {{ store.name.slice(0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h2
                                class="truncate text-xl font-bold group-hover:underline"
                            >
                                {{ store.name }}
                            </h2>
                            <p class="mt-1 truncate text-sm text-slate-500">
                                {{ store.address || "Cửa hàng trực tuyến" }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-8 flex items-center justify-between border-t border-neutral-100 pt-4 text-sm"
                    >
                        <span class="text-neutral-500">{{
                            store.phone || store.email || ""
                        }}</span
                        ><span class="font-bold">Vào cửa hàng →</span>
                    </div>
                </Link>
            </div>
        </section>
    </main>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
defineProps({ stores: { type: Array, default: () => [] } });
</script>
