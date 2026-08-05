<template>
    <Head :title="`Giỏ hàng - ${store.name}`" />
    <div class="min-h-screen bg-[#f4f2ed] text-[#111]">
        <div
            class="bg-black px-4 py-2 text-center text-[11px] font-bold uppercase tracking-[0.18em] text-white"
        >
            Đổi trả trong 30 ngày · Giao hàng toàn quốc
        </div>
        <header class="border-b border-black/10">
            <div
                class="mx-auto flex h-20 max-w-[1500px] items-center gap-5 px-5 lg:px-9"
            >
                <Link
                    :href="`/shop/${store.slug}`"
                    class="text-2xl font-black tracking-[-0.06em]"
                    >{{ store.name }}</Link
                >
                <span class="hidden text-sm text-black/35 sm:block"
                    >/ Giỏ hàng</span
                >
                <Link
                    :href="`/shop/${store.slug}`"
                    class="ml-auto rounded-full px-4 py-3 text-sm font-bold transition hover:bg-black/5"
                    >Tiếp tục mua sắm</Link
                >
                <NotificationBadgeLink :slug="store.slug" />
            </div>
        </header>

        <main class="mx-auto max-w-[1500px] px-5 py-12 lg:px-9 lg:py-20">
            <div
                class="flex items-end justify-between border-b border-black/20 pb-8"
            >
                <div>
                    <p
                        class="text-xs font-black uppercase tracking-[0.22em] text-black/45"
                    >
                        Your selection
                    </p>
                    <h1
                        class="mt-3 text-5xl font-black tracking-[-0.06em] md:text-7xl"
                    >
                        Giỏ hàng
                    </h1>
                </div>
                <p v-if="cart.length" class="pb-2 text-sm text-black/50">
                    {{ count }} sản phẩm
                </p>
            </div>

            <section
                v-if="!cart.length"
                class="grid min-h-[55vh] place-items-center text-center"
            >
                <div>
                    <div
                        class="mx-auto grid h-20 w-20 place-items-center rounded-full border border-black/15 text-3xl"
                    >
                        ○
                    </div>
                    <h2 class="mt-7 text-3xl font-black tracking-[-0.04em]">
                        Giỏ hàng đang trống
                    </h2>
                    <p class="mt-3 text-black/50">
                        Hãy khám phá bộ sưu tập và chọn sản phẩm phù hợp với
                        bạn.
                    </p>
                    <Link
                        :href="`/shop/${store.slug}`"
                        class="mt-7 inline-block rounded-full bg-black px-8 py-4 text-sm font-black text-white transition hover:bg-[#d8ff43] hover:text-black"
                        >Khám phá sản phẩm →</Link
                    >
                </div>
            </section>

            <div
                v-else
                class="mt-10 grid items-start gap-12 lg:grid-cols-[minmax(0,1fr)_400px] xl:gap-20"
            >
                <section>
                    <article
                        v-for="item in cart"
                        :key="item.product_id"
                        class="grid grid-cols-[110px_minmax(0,1fr)] gap-5 border-b border-black/15 py-6 first:pt-0 sm:grid-cols-[170px_minmax(0,1fr)] sm:gap-8"
                    >
                        <Link
                            :href="`/shop/${store.slug}/product/${item.product_id}`"
                            class="aspect-square overflow-hidden bg-[#e5e1d9]"
                            ><img
                                v-if="item.image"
                                :src="item.image"
                                :alt="item.name"
                                class="h-full w-full object-cover mix-blend-multiply transition duration-500 hover:scale-105"
                            />
                            <div
                                v-else
                                class="grid h-full place-items-center text-4xl text-black/10"
                            >
                                ◒
                            </div></Link
                        >
                        <div class="flex min-w-0 flex-col py-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p
                                        class="text-[10px] font-black uppercase tracking-[0.18em] text-black/35"
                                    >
                                        Sản phẩm
                                    </p>
                                    <Link
                                        :href="`/shop/${store.slug}/product/${item.product_id}`"
                                        class="mt-2 block text-lg font-black leading-tight hover:underline sm:text-2xl"
                                        >{{ item.name }}</Link
                                    >
                                    <p class="mt-2 text-xs text-black/40">
                                        Còn {{ item.stock }} sản phẩm
                                    </p>
                                </div>
                                <p class="shrink-0 font-black">
                                    {{ money(item.price * item.quantity) }}
                                </p>
                            </div>
                            <div
                                class="mt-auto flex flex-wrap items-center gap-4 pt-6"
                            >
                                <div
                                    class="flex h-11 items-center rounded-full border border-black/20"
                                >
                                    <button
                                        class="h-full w-11 text-lg transition hover:bg-black/5 disabled:opacity-25"
                                        :disabled="item.quantity <= 1"
                                        aria-label="Giảm số lượng"
                                        @click="decrease(item)"
                                    >
                                        −</button
                                    ><input
                                        :value="item.quantity"
                                        readonly
                                        class="w-10 bg-transparent text-center text-sm font-black outline-none"
                                    /><button
                                        class="h-full w-11 text-lg transition hover:bg-black/5 disabled:opacity-25"
                                        :disabled="item.quantity >= item.stock"
                                        aria-label="Tăng số lượng"
                                        @click="increase(item)"
                                    >
                                        +
                                    </button>
                                </div>
                                <button
                                    class="text-xs font-bold text-black/45 underline decoration-black/25 underline-offset-4 transition hover:text-black"
                                    @click="remove(item.product_id)"
                                >
                                    Xóa sản phẩm
                                </button>
                                <p
                                    class="ml-auto text-sm text-black/45 sm:hidden"
                                >
                                    {{ money(item.price) }} / sản phẩm
                                </p>
                            </div>
                        </div>
                    </article>
                    <button
                        class="mt-6 text-xs font-bold text-black/45 underline decoration-black/20 underline-offset-4 hover:text-black"
                        @click="showClearCartModal = true"
                    >
                        Xóa toàn bộ giỏ hàng
                    </button>
                </section>

                <aside class="sticky top-6 bg-[#111] p-7 text-white sm:p-9">
                    <p
                        class="text-xs font-black uppercase tracking-[0.22em] text-white/40"
                    >
                        Order summary
                    </p>
                    <h2 class="mt-3 text-3xl font-black tracking-[-0.04em]">
                        Tóm tắt đơn hàng
                    </h2>
                    <div
                        class="mt-8 space-y-4 border-b border-white/15 pb-6 text-sm"
                    >
                        <div class="flex justify-between">
                            <span class="text-white/55"
                                >Tạm tính · {{ count }} sản phẩm</span
                            ><span class="font-bold">{{ money(total) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/55">Giao hàng</span
                            ><span class="font-bold text-[#d8ff43]"
                                >Tính ở bước sau</span
                            >
                        </div>
                    </div>
                    <div class="mt-6 flex items-end justify-between">
                        <span class="font-bold">Tổng tạm tính</span
                        ><span class="text-3xl font-black tracking-[-0.04em]">{{
                            money(total)
                        }}</span>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-white/40">
                        Thuế và phí giao hàng sẽ được xác nhận tại bước thanh
                        toán.
                    </p>
                    <Link
                        :href="`/shop/${store.slug}/checkout`"
                        class="mt-8 flex w-full items-center justify-between rounded-full bg-[#d8ff43] px-6 py-4 font-black text-black transition hover:bg-white"
                        ><span>Tiến hành thanh toán</span><span>→</span></Link
                    >
                    <div
                        class="mt-7 grid grid-cols-2 gap-3 border-t border-white/15 pt-6 text-xs text-white/50"
                    >
                        <p>✓ Thanh toán COD</p>
                        <p>✓ Chuyển khoản</p>
                        <p>✓ Đổi trả 30 ngày</p>
                        <p>✓ Theo dõi đơn</p>
                    </div>
                </aside>
            </div>
        </main>
        <ActionModal
            :open="showClearCartModal"
            title="Xóa toàn bộ giỏ hàng"
            message="Tất cả sản phẩm đang chọn sẽ bị xóa khỏi giỏ hàng. Bạn có muốn tiếp tục?"
            confirm-text="Xóa toàn bộ"
            @close="showClearCartModal = false"
            @confirm="clearCart"
        />
    </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { useStorefrontCart } from "@/composables/useStorefrontCart";
import NotificationBadgeLink from "@/components/Storefront/NotificationBadgeLink.vue";
import ActionModal from "@/components/Storefront/ActionModal.vue";
const props = defineProps({ store: { type: Object, required: true } });
const { cart, count, total, remove, clear } = useStorefrontCart(
    props.store.slug,
);
const showClearCartModal = ref(false);
const money = (v) =>
    `${new Intl.NumberFormat("vi-VN").format(Number(v || 0))} ${props.store.currency.symbol}`;
function decrease(item) {
    item.quantity = Math.max(1, Number(item.quantity) - 1);
}
function increase(item) {
    item.quantity = Math.min(Number(item.stock), Number(item.quantity) + 1);
}
function clearCart() {
    clear();
    showClearCartModal.value = false;
}
</script>
