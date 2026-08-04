<template>
    <Head :title="store.name" />
    <div class="min-h-screen bg-[#f4f2ed] text-[#111]">
        <div
            class="bg-[#111] px-4 py-2 text-center text-[11px] font-bold uppercase tracking-[0.18em] text-white"
        >
            Miễn phí giao hàng cho đơn từ 1.500.000đ · Đổi trả trong 30 ngày
        </div>
        <header
            class="sticky top-0 z-40 border-b border-black/10 bg-[#f4f2ed]/95 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex h-20 max-w-[1500px] items-center gap-6 px-5 lg:px-9"
            >
                <Link
                    :href="`/shop/${store.slug}`"
                    class="flex min-w-0 items-center gap-3"
                >
                    <span
                        class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-full border border-black/10 bg-white"
                    >
                        <img
                            v-if="store.logo"
                            :src="store.logo"
                            :alt="`Logo ${store.name}`"
                            class="h-full w-full object-contain p-1.5"
                        />
                        <span v-else class="text-lg font-black">{{
                            store.name.slice(0, 1).toUpperCase()
                        }}</span>
                    </span>
                    <span
                        class="hidden truncate text-xl font-black tracking-[-0.05em] sm:block"
                        >{{ store.name }}</span
                    >
                </Link>
                <nav
                    class="hidden items-center gap-7 text-sm font-bold lg:flex"
                >
                    <button @click="selectCategory('')">Mới nhất</button>
                    <button
                        v-for="category in categories.slice(0, 4)"
                        :key="category.id"
                        @click="selectCategory(category.id)"
                    >
                        {{ category.name }}
                    </button>
                </nav>
                <div class="ml-auto flex items-center gap-2">
                    <button
                        class="rounded-full p-3 hover:bg-black/5"
                        aria-label="Tìm kiếm"
                        @click="searchOpen = !searchOpen"
                    >
                        ⌕
                    </button>
                    <Link
                        :href="`/shop/${store.slug}/my-account`"
                        class="hidden rounded-full px-4 py-2 text-sm font-bold hover:bg-black/5 sm:block"
                        >Tài khoản</Link
                    >
                    <Link
                        href="/shop"
                        class="hidden rounded-full px-4 py-2 text-sm font-bold hover:bg-black/5 md:block"
                        >Đổi cửa hàng</Link
                    >
                    <Link
                        :href="`/shop/${store.slug}/cart`"
                        class="rounded-full bg-[#111] px-5 py-3 text-sm font-bold text-white"
                        >Giỏ hàng ({{ cartCount }})</Link
                    >
                </div>
            </div>
            <form
                v-if="searchOpen"
                class="border-t border-black/10 px-5 py-4"
                @submit.prevent="loadProducts(1)"
            >
                <div class="mx-auto flex max-w-3xl gap-3">
                    <input
                        v-model="search"
                        autofocus
                        class="w-full border-b border-black bg-transparent px-1 py-3 text-xl outline-none"
                        placeholder="Bạn đang tìm sản phẩm nào?"
                    /><button
                        class="rounded-full bg-black px-6 text-sm font-bold text-white"
                    >
                        Tìm
                    </button>
                </div>
            </form>
        </header>

        <section class="relative min-h-[76vh] overflow-hidden">
            <img
                src="/storage/storefront/electronics-hero.png"
                alt="Không gian làm việc với các thiết bị điện tử hiện đại"
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div
                class="absolute inset-0 bg-gradient-to-r from-black/65 via-black/15 to-transparent"
            ></div>
            <div
                class="relative mx-auto flex min-h-[76vh] max-w-[1500px] items-end px-5 pb-14 text-white lg:px-9 lg:pb-20"
            >
                <div class="max-w-3xl">
                    <p class="text-xs font-black uppercase tracking-[0.3em]">
                        Công nghệ cho mỗi ngày
                    </p>
                    <h1
                        class="mt-5 text-6xl font-black leading-[0.86] tracking-[-0.065em] sm:text-7xl lg:text-[7.5rem]"
                    >
                        Làm việc tốt hơn.<br />Chơi hết mình.
                    </h1>
                    <p class="mt-7 max-w-md text-base leading-7 text-white/80">
                        Thiết bị điện tử hiện đại, hiệu năng ổn định và thiết kế
                        tối giản cho không gian của bạn.
                    </p>
                    <button
                        class="mt-8 rounded-full bg-white px-7 py-4 text-sm font-black text-black transition hover:bg-[#d8ff43]"
                        @click="scrollToProducts"
                    >
                        Khám phá sản phẩm →
                    </button>
                </div>
            </div>
        </section>

        <section class="border-b border-black/10 bg-[#d8ff43]">
            <div
                class="mx-auto grid max-w-[1500px] divide-y divide-black/20 px-5 md:grid-cols-3 md:divide-x md:divide-y-0 lg:px-9"
            >
                <div
                    v-for="item in benefits"
                    :key="item.title"
                    class="py-7 md:px-8 md:first:pl-0"
                >
                    <p class="text-xs font-black uppercase tracking-[0.18em]">
                        {{ item.title }}
                    </p>
                    <p class="mt-2 text-sm text-black/65">{{ item.text }}</p>
                </div>
            </div>
        </section>

        <main
            ref="productSection"
            class="mx-auto max-w-[1500px] px-5 py-16 lg:px-9 lg:py-24"
        >
            <div
                class="flex flex-col justify-between gap-6 border-b border-black/20 pb-7 md:flex-row md:items-end"
            >
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em]">
                        Shop the collection
                    </p>
                    <h2
                        class="mt-3 text-4xl font-black tracking-[-0.05em] md:text-6xl"
                    >
                        Sản phẩm nổi bật
                    </h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        class="filter-pill"
                        :class="{ active: categoryId === '' }"
                        @click="selectCategory('')"
                    >
                        Tất cả</button
                    ><button
                        v-for="category in categories"
                        :key="category.id"
                        class="filter-pill"
                        :class="{ active: categoryId === category.id }"
                        @click="selectCategory(category.id)"
                    >
                        {{ category.name }}</button
                    ><select
                        v-model="sort"
                        class="filter-pill outline-none"
                        @change="loadProducts(1)"
                    >
                        <option value="newest">Mới nhất</option>
                        <option value="price_asc">Giá tăng dần</option>
                        <option value="price_desc">Giá giảm dần</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="py-24 text-center text-black/50">
                Đang chuẩn bị bộ sưu tập...
            </div>
            <div
                v-else-if="!products.length"
                class="py-24 text-center text-black/50"
            >
                Không tìm thấy sản phẩm phù hợp.
            </div>
            <div
                v-else
                class="mt-10 grid grid-cols-2 gap-x-4 gap-y-12 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-6"
            >
                <article
                    v-for="(product, index) in products"
                    :key="product.id"
                    class="group"
                    :class="{ 'md:col-span-2 md:row-span-2': index === 0 }"
                >
                    <Link
                        :href="`/shop/${store.slug}/product/${product.id}`"
                        class="relative block overflow-hidden bg-[#e8e5df]"
                        :class="index === 0 ? 'aspect-[1.04]' : 'aspect-square'"
                    >
                        <span
                            v-if="product.has_promotion"
                            class="absolute left-3 top-3 z-10 rounded-full bg-[#d8ff43] px-3 py-1.5 text-[10px] font-black uppercase"
                            >Sale</span
                        >
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.035]"
                        />
                        <div
                            v-else
                            class="flex h-full items-center justify-center text-6xl text-black/10"
                        >
                            ◒
                        </div>
                        <button
                            class="absolute bottom-4 right-4 grid h-11 w-11 translate-y-3 place-items-center rounded-full bg-white text-xl opacity-0 shadow-lg transition group-hover:translate-y-0 group-hover:opacity-100"
                            @click.prevent="addToCart(product)"
                        >
                            +
                        </button>
                    </Link>
                    <div class="mt-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-black/45">
                                {{ product.category?.name || "Sản phẩm" }}
                            </p>
                            <Link
                                :href="`/shop/${store.slug}/product/${product.id}`"
                                class="mt-1 block font-bold leading-tight hover:underline"
                                >{{ product.name }}</Link
                            >
                            <p class="mt-1 text-xs text-black/45">
                                {{ number(product.available_stock) }} sản phẩm
                                có sẵn
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p
                                v-if="product.has_promotion"
                                class="text-xs text-black/40 line-through"
                            >
                                {{ money(product.sell_price) }}
                            </p>
                            <p class="font-black">
                                {{ money(product.selling_price) }}
                            </p>
                        </div>
                    </div>
                </article>
            </div>
            <button
                v-if="nextPage"
                class="mx-auto mt-14 block rounded-full border border-black px-8 py-4 text-sm font-black transition hover:bg-black hover:text-white"
                @click="loadProducts(nextPage, true)"
            >
                Xem thêm
            </button>
        </main>

        <section class="grid bg-[#181818] text-white lg:grid-cols-2">
            <div
                class="flex min-h-[520px] flex-col justify-between p-8 sm:p-14 lg:p-20"
            >
                <p
                    class="text-xs font-black uppercase tracking-[0.25em] text-[#d8ff43]"
                >
                    Built for your setup
                </p>
                <div>
                    <h2
                        class="max-w-xl text-5xl font-black leading-[0.92] tracking-[-0.055em] md:text-7xl"
                    >
                        Gọn trên bàn.<br />Mạnh trong việc.
                    </h2>
                    <p class="mt-7 max-w-md leading-7 text-white/55">
                        Thiết bị được chọn cho hiệu năng, độ bền và trải nghiệm
                        sử dụng liền mạch từ công việc đến giải trí.
                    </p>
                </div>
                <Link
                    :href="`/shop/${store.slug}/my-account`"
                    class="mt-10 w-fit border-b border-white pb-1 text-sm font-black"
                    >Theo dõi đơn hàng →</Link
                >
            </div>
            <div
                class="grid min-h-[520px] place-items-center bg-[#c9c3b8] p-12"
            >
                <img
                    src="/storage/storefront/keyboard-black.png"
                    alt="Bàn phím cơ tối giản"
                    class="w-full max-w-xl mix-blend-multiply"
                />
            </div>
        </section>

        <footer class="bg-[#0d0d0d] px-5 py-14 text-white lg:px-9">
            <div class="mx-auto grid max-w-[1500px] gap-10 md:grid-cols-4">
                <div class="md:col-span-2">
                    <p class="text-3xl font-black tracking-[-0.05em]">
                        {{ store.name }}
                    </p>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-white/50">
                        {{ store.address }}<br />{{ store.phone }}
                    </p>
                </div>
                <div>
                    <p
                        class="text-xs font-black uppercase tracking-widest text-white/40"
                    >
                        Mua sắm
                    </p>
                    <div class="mt-4 space-y-3 text-sm">
                        <p
                            v-for="category in categories.slice(0, 4)"
                            :key="category.id"
                        >
                            {{ category.name }}
                        </p>
                    </div>
                </div>
                <div>
                    <p
                        class="text-xs font-black uppercase tracking-widest text-white/40"
                    >
                        Hỗ trợ
                    </p>
                    <div class="mt-4 space-y-3 text-sm">
                        <Link
                            :href="`/shop/${store.slug}/my-account`"
                            class="block"
                            >Tài khoản</Link
                        ><Link :href="`/shop/${store.slug}/cart`" class="block"
                            >Giỏ hàng</Link
                        ><Link href="/shop" class="block">Đổi cửa hàng</Link>
                    </div>
                </div>
            </div>
            <div
                class="mx-auto mt-14 flex max-w-[1500px] justify-between border-t border-white/10 pt-6 text-xs text-white/35"
            >
                <span>© 2026 {{ store.name }}</span
                ><span>Giao hàng toàn quốc</span>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import axios from "axios";
import { computed, onMounted, ref } from "vue";
import { useStorefrontCart } from "@/composables/useStorefrontCart";

const props = defineProps({ store: { type: Object, required: true } });
const products = ref([]),
    categories = ref([]),
    loading = ref(false),
    nextPage = ref(null),
    search = ref(""),
    categoryId = ref(""),
    sort = ref("newest"),
    searchOpen = ref(false),
    productSection = ref(null);
const { count: addCount, add } = useStorefrontCart(props.store.slug);
const cartCount = computed(() => addCount.value);
const benefits = [
    {
        title: "Giao hàng toàn quốc",
        text: "Theo dõi hành trình đơn hàng dễ dàng.",
    },
    {
        title: "Đổi trả 30 ngày",
        text: "Mua sắm thoải mái, quy trình minh bạch.",
    },
    { title: "Thanh toán an toàn", text: "COD và chuyển khoản ngân hàng." },
];
const number = (v) =>
    new Intl.NumberFormat("vi-VN", { maximumFractionDigits: 0 }).format(
        Number(v || 0),
    );
const money = (v) =>
    `${new Intl.NumberFormat("vi-VN").format(Number(v || 0))} ${props.store.currency.symbol}`;
async function loadProducts(page = 1, append = false) {
    loading.value = !append;
    try {
        const { data } = await axios.get(`/shop/${props.store.slug}/products`, {
            params: {
                page,
                search: search.value || undefined,
                category_id: categoryId.value || undefined,
                sort: sort.value,
            },
        });
        products.value = append
            ? [...products.value, ...data.products.data]
            : data.products.data;
        categories.value = data.categories;
        nextPage.value = data.products.next_page_url
            ? data.products.current_page + 1
            : null;
        searchOpen.value = false;
    } finally {
        loading.value = false;
    }
}
function selectCategory(id) {
    categoryId.value = id;
    loadProducts(1);
    if (products.value.length) scrollToProducts();
}
function scrollToProducts() {
    productSection.value?.scrollIntoView({ behavior: "smooth" });
}
function addToCart(product) {
    add(product, 1);
}
onMounted(loadProducts);
</script>

<style scoped>
.filter-pill {
    border: 1px solid rgb(0 0 0 / 20%);
    border-radius: 999px;
    background: transparent;
    padding: 0.65rem 1rem;
    font-size: 0.75rem;
    font-weight: 800;
    transition: 0.2s;
}
.filter-pill:hover,
.filter-pill.active {
    border-color: #111;
    background: #111;
    color: #fff;
}
</style>
