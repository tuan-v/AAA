<template>
    <Head :title="product?.name || store.name" />
    <div class="min-h-screen bg-[#f4f2ed] text-[#111]">
        <header class="border-b border-black/10">
            <div
                class="mx-auto flex h-20 max-w-[1500px] items-center gap-5 px-5 lg:px-9"
            >
                <Link
                    :href="`/shop/${store.slug}`"
                    class="text-2xl font-black tracking-[-0.06em]"
                    >{{ store.name }}</Link
                ><span class="hidden text-sm text-black/35 sm:block"
                    >/ {{ product?.category?.name || "Sản phẩm" }}</span
                >
                <div class="ml-auto flex gap-2">
                    <NotificationBadgeLink :slug="store.slug" /><Link
                        :href="`/shop/${store.slug}/cart`"
                        class="rounded-full bg-black px-5 py-3 text-sm font-bold text-white"
                        >Giỏ hàng ({{ count }})</Link
                    >
                </div>
            </div>
        </header>
        <main
            v-if="product"
            class="mx-auto grid max-w-[1500px] lg:grid-cols-[1.2fr_.8fr]"
        >
            <section
                class="relative grid place-items-center bg-[#e7e3db] p-8 sm:p-16 lg:min-h-[calc(100vh-5rem)]"
            >
                <Link
                    :href="`/shop/${store.slug}`"
                    class="absolute left-6 top-6 rounded-full bg-white/80 px-4 py-2 text-xs font-black backdrop-blur"
                    >← Trở lại</Link
                ><img
                    v-if="product.image"
                    :src="product.image"
                    :alt="product.name"
                    class="aspect-square w-full max-w-3xl object-cover mix-blend-multiply"
                />
                <div v-else class="text-8xl text-black/10">◒</div>
            </section>
            <section
                class="flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16"
            >
                <p
                    class="text-xs font-black uppercase tracking-[0.22em] text-black/45"
                >
                    {{ product.category?.name }}
                </p>
                <h1
                    class="mt-4 text-5xl font-black leading-[.95] tracking-[-0.055em] lg:text-6xl"
                >
                    {{ product.name }}
                </h1>
                <p class="mt-4 text-xs text-black/40">
                    Mã sản phẩm · {{ product.sku }}
                </p>
                <div class="mt-8 flex items-baseline gap-3">
                    <span class="text-3xl font-black">{{
                        money(product.selling_price)
                    }}</span
                    ><span
                        v-if="product.has_promotion"
                        class="text-base text-black/35 line-through"
                        >{{ money(product.sell_price) }}</span
                    >
                </div>
                <p class="mt-5 max-w-lg leading-7 text-black/60">
                    {{
                        product.description ||
                        "Sản phẩm được lựa chọn kỹ cho độ bền, sự thoải mái và khả năng sử dụng mỗi ngày."
                    }}
                </p>
                <div class="mt-8 border-y border-black/15 py-5">
                    <div class="flex justify-between text-sm">
                        <span class="font-bold">Tình trạng</span
                        ><span class="text-emerald-700"
                            >Còn {{ product.available_stock }}
                            {{ product.unit?.name }}</span
                        >
                    </div>
                </div>
                <div class="mt-7 flex gap-3">
                    <div class="flex rounded-full border border-black/20">
                        <button
                            class="w-11"
                            @click="quantity = Math.max(1, quantity - 1)"
                        >
                            −</button
                        ><input
                            v-model.number="quantity"
                            type="number"
                            min="1"
                            :max="product.available_stock"
                            class="w-12 bg-transparent text-center font-bold outline-none"
                        /><button
                            class="w-11"
                            @click="
                                quantity = Math.min(
                                    product.available_stock,
                                    quantity + 1,
                                )
                            "
                        >
                            +
                        </button>
                    </div>
                    <button
                        class="flex-1 rounded-full bg-black px-7 py-4 font-black text-white transition hover:bg-[#d8ff43] hover:text-black"
                        @click="addProduct"
                    >
                        Thêm vào giỏ
                    </button>
                </div>
                <div
                    class="mt-8 grid gap-3 text-sm text-black/55 sm:grid-cols-2"
                >
                    <p>✓ Đổi trả trong 30 ngày</p>
                    <p>✓ Giao hàng toàn quốc</p>
                    <p>✓ Thanh toán COD</p>
                    <p>✓ Theo dõi đơn hàng</p>
                </div>
            </section>
        </main>
    </div>
</template>
<script setup>
import { Head, Link } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, ref } from "vue";
import { useStorefrontCart } from "@/composables/useStorefrontCart";
import { useAutoApiRefresh } from "@/composables/useAutoApiRefresh";
import NotificationBadgeLink from "@/components/Storefront/NotificationBadgeLink.vue";
const props = defineProps({ store: Object, productId: Number });
const product = ref(null),
    quantity = ref(1);
const { count, add } = useStorefrontCart(props.store.slug);
const money = (v) =>
    `${new Intl.NumberFormat("vi-VN").format(Number(v || 0))} ${props.store.currency.symbol}`;
function addProduct() {
    add(
        product.value,
        Math.max(
            1,
            Math.min(Number(product.value.available_stock), quantity.value),
        ),
    );
}
async function loadProduct() {
    product.value = (
        await axios.get(`/shop/${props.store.slug}/products/${props.productId}`)
    ).data.product;
}
onMounted(loadProduct);
useAutoApiRefresh(loadProduct, 30000);
</script>
