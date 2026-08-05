<template>
    <Head :title="`Đơn hàng ${order.code} - ${store.name}`" />
    <div class="min-h-screen bg-[#f4f2ed] text-[#111]">
        <div class="bg-black px-4 py-2 text-center text-[11px] font-bold uppercase tracking-[.18em] text-white">
            Chi tiết đơn hàng · {{ order.code }}
        </div>
        <header class="border-b border-black/10">
            <div class="mx-auto flex h-20 max-w-[1400px] items-center gap-4 px-5 lg:px-9">
                <Link :href="`/shop/${store.slug}`" class="text-2xl font-black tracking-[-.06em]">
                    {{ store.name }}
                </Link>
                <Link :href="`/shop/${store.slug}/my-account`" class="ml-auto rounded-full px-4 py-3 text-sm font-bold hover:bg-black/5">
                    ← Đơn hàng của tôi
                </Link>
                <NotificationBadgeLink :slug="store.slug" :show-label="false" />
                <Link :href="`/shop/${store.slug}/cart`" class="rounded-full bg-black px-5 py-3 text-sm font-bold text-white">
                    Giỏ hàng
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-[1200px] px-5 py-10 lg:px-9 lg:py-14">

            <section class="flex flex-col justify-between gap-5 border-b border-black/15 pb-8 md:flex-row md:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.22em] text-black/40">Đơn hàng của bạn</p>
                    <h1 class="mt-3 text-4xl font-black tracking-[-.05em] md:text-6xl">{{ order.code }}</h1>
                    <p class="mt-3 text-sm text-black/50">Đặt lúc {{ order.created_at }}</p>
                </div>
                <div class="md:text-right">
                    <span class="inline-flex rounded-full px-4 py-2 text-xs font-black" :class="statusClass(order.status)">
                        {{ order.status_label }}
                    </span>
                    <p class="mt-3 text-3xl font-black">{{ money(order.total) }}</p>
                </div>
            </section>

            <section v-if="!isCancelled" class="mt-8 border border-black/15 bg-white/55 p-6 md:p-8">
                <h2 class="text-xl font-black">Tiến trình đơn hàng</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-4">
                    <div v-for="(step, index) in order.timeline" :key="step.key" class="relative">
                        <div class="mb-3 h-1 rounded-full" :class="step.done ? 'bg-black' : 'bg-black/10'"></div>
                        <p class="text-sm font-black" :class="step.done ? 'text-black' : 'text-black/35'">{{ index + 1 }}. {{ step.label }}</p>
                        <p v-if="step.date" class="mt-1 text-xs text-black/45">{{ step.date }}</p>
                    </div>
                </div>
            </section>

            <section v-else class="mt-8 border border-red-200 bg-red-50 p-6">
                <p class="font-black text-red-800">Đơn hàng đã bị hủy</p>
                <p v-if="order.cancellation_reason" class="mt-2 text-sm text-red-700">Lý do: {{ order.cancellation_reason }}</p>
            </section>

            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div class="space-y-8">
                    <section class="border border-black/15 bg-white/55 p-6 md:p-8">
                        <h2 class="text-2xl font-black">Sản phẩm</h2>
                        <div class="mt-6 divide-y divide-black/10">
                            <div v-for="item in order.items" :key="`${item.sku}-${item.name}`" class="flex gap-4 py-5 first:pt-0 last:pb-0">
                                <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden bg-black/5">
                                    <img v-if="item.image" :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
                                    <span v-else class="text-2xl text-black/20">□</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black">{{ item.name }}</p>
                                    <p class="mt-1 text-xs text-black/45">
                                        <span v-if="item.sku">SKU: {{ item.sku }}</span>
                                        <span v-if="item.unit"> · Đơn vị: {{ item.unit }}</span>
                                    </p>
                                    <p class="mt-2 text-sm text-black/60">{{ money(item.unit_price) }} × {{ item.quantity }}</p>
                                </div>
                                <p class="shrink-0 font-black">{{ money(item.amount) }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="grid gap-6 sm:grid-cols-2">
                        <InfoCard title="Thông tin nhận hàng">
                            <p class="font-black">{{ order.recipient.name }}</p>
                            <p>{{ order.recipient.phone }}</p>
                            <p v-if="order.recipient.email">{{ order.recipient.email }}</p>
                            <p class="mt-2">{{ order.recipient.address }}</p>
                        </InfoCard>
                        <InfoCard title="Vận chuyển">
                            <p><b>Phương thức:</b> {{ shippingLabel }}</p>
                            <p v-if="order.expected_delivery_date"><b>Dự kiến giao:</b> {{ order.expected_delivery_date }}</p>
                            <p v-if="order.tracking_code"><b>Mã vận đơn:</b> {{ order.tracking_code }}</p>
                            <p v-else class="text-black/45">Chưa có mã vận đơn.</p>
                            <p v-if="order.shipping_partner"><b>Đơn vị vận chuyển:</b> {{ order.shipping_partner }}</p>
                            <a v-if="order.tracking_url" :href="order.tracking_url" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex rounded-full bg-black px-4 py-2 text-xs font-black text-white">Theo dõi vận đơn ↗</a>
                            <p v-if="order.shipping_note" class="mt-2 text-xs text-black/45">{{ order.shipping_note }}</p>
                        </InfoCard>
                        <InfoCard title="Thanh toán">
                            <p><b>Phương thức:</b> {{ paymentMethodLabel }}</p>
                            <p><b>Trạng thái:</b> {{ paymentStatusLabel }}</p>
                            <p v-if="order.paid_amount > 0"><b>Đã thanh toán:</b> {{ money(order.paid_amount) }}</p>
                            <p v-if="order.payment_reference"><b>Mã giao dịch:</b> {{ order.payment_reference }}</p>
                        </InfoCard>
                        <InfoCard title="Ghi chú">
                            <p v-if="order.note">{{ order.note }}</p>
                            <p v-else class="text-black/45">Không có ghi chú.</p>
                        </InfoCard>
                    </section>
                </div>

                <aside class="h-fit border border-black/15 bg-white/70 p-6 lg:sticky lg:top-6">
                    <h2 class="text-2xl font-black">Tổng thanh toán</h2>
                    <div class="mt-6 space-y-3 text-sm">
                        <PriceRow label="Tiền hàng" :value="order.subtotal" />
                        <PriceRow label="Thuế VAT" :value="order.vat_amount" />
                        <PriceRow v-if="order.discount_amount > 0" :label="order.coupon ? `Giảm giá (${order.coupon})` : 'Giảm giá'" :value="-order.discount_amount" discount />
                        <PriceRow label="Phí vận chuyển" :value="order.shipping_fee" />
                    </div>
                    <div class="mt-5 flex items-end justify-between border-t border-black/15 pt-5">
                        <span class="font-black">Tổng cộng</span>
                        <span class="text-2xl font-black">{{ money(order.total) }}</span>
                    </div>
                    <button v-if="order.cancelable" :disabled="cancelling" class="mt-6 w-full rounded-full border border-red-700 px-5 py-3 text-sm font-black text-red-700 hover:bg-red-700 hover:text-white disabled:opacity-50" @click="showCancelModal = true">
                        {{ cancelling ? 'Đang hủy...' : 'Hủy đơn hàng' }}
                    </button>
                    <button v-if="order.repurchasable" class="mt-3 w-full rounded-full bg-black px-5 py-3 text-sm font-black text-white transition hover:bg-[#d8ff43] hover:text-black" @click="repurchaseOrder">
                        Mua lại đơn hàng
                    </button>
                </aside>
            </div>
        </main>
        <ActionModal
            :open="showCancelModal"
            title="Hủy đơn hàng"
            :message="`Bạn đang yêu cầu hủy đơn ${order.code}. Thao tác này không thể hoàn tác.`"
            confirm-text="Xác nhận hủy đơn"
            loading-text="Đang hủy đơn..."
            :loading="cancelling"
            :error="modalError"
            require-reason
            :reason-options="cancelReasonOptions"
            reason-placeholder="Ví dụ: Tôi muốn thay đổi sản phẩm..."
            @close="closeCancelModal"
            @confirm="cancelOrder"
        />
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, defineComponent, h, onMounted, ref } from 'vue';
import ActionModal from '@/components/Storefront/ActionModal.vue';
import { storefrontToast as toast } from '@/utils/storefrontToast';
import { useStorefrontCart } from '@/composables/useStorefrontCart';
import { useStorefrontNotifications } from '@/composables/useStorefrontNotifications';
import { useAutoApiRefresh } from '@/composables/useAutoApiRefresh';
import NotificationBadgeLink from '@/components/Storefront/NotificationBadgeLink.vue';

const props = defineProps({
    store: { type: Object, required: true },
    order: { type: Object, required: true },
});
const cancelling = ref(false);
const error = ref('');
const notice = ref('');
const showCancelModal = ref(false);
const modalError = ref('');
const { add } = useStorefrontCart(props.store.slug);
const { start: startNotifications } = useStorefrontNotifications(props.store.slug);
const cancelReasonOptions = [
    { value: 'change_product', label: 'Muốn thay đổi sản phẩm' },
    { value: 'change_address', label: 'Muốn thay đổi địa chỉ nhận hàng' },
    { value: 'change_payment', label: 'Muốn thay đổi phương thức thanh toán' },
    { value: 'no_longer_needed', label: 'Không còn nhu cầu mua hàng' },
    { value: 'ordered_by_mistake', label: 'Đặt nhầm đơn hàng' },
    { value: 'other', label: 'Lý do khác' },
];
const isCancelled = computed(() => props.order.status === 'cancelled');
const shippingLabel = computed(() => ({ standard: 'Giao hàng tiêu chuẩn', express: 'Giao hàng nhanh' })[props.order.shipping_method] || props.order.shipping_method || 'Chưa xác định');
const paymentMethodLabel = computed(() => ({ cod: 'Thanh toán khi nhận hàng (COD)', bank_transfer: 'Chuyển khoản ngân hàng', cash: 'Tiền mặt' })[props.order.payment_method] || props.order.payment_method || 'Chưa xác định');
const paymentStatusLabel = computed(() => ({ paid: 'Đã thanh toán', partial: 'Thanh toán một phần', unpaid: 'Chưa thanh toán' })[props.order.payment_status]);
const money = (value) => `${new Intl.NumberFormat('vi-VN').format(Number(value || 0))} ${props.store.currency.symbol}`;

function closeCancelModal() {
    if (cancelling.value) return;
    showCancelModal.value = false;
    modalError.value = '';
}
async function cancelOrder(reason) {
    cancelling.value = true;
    error.value = '';
    modalError.value = '';
    try {
        const { data } = await axios.post(`/shop/${props.store.slug}/account/orders/${props.order.code}/cancel`, { reason });
        notice.value = data.message;
        toast.success(data.message);
        showCancelModal.value = false;
        router.reload();
    } catch (e) {
        modalError.value = Object.values(e.response?.data?.errors || {}).flat()[0] || e.response?.data?.message || 'Không thể hủy đơn hàng.';
    } finally {
        cancelling.value = false;
    }
}
function repurchaseOrder() {
    let added = 0;
    let unavailable = 0;
    let reduced = 0;
    for (const item of props.order.items || []) {
        const product = item.repurchase;
        if (!product?.available) {
            unavailable++;
            continue;
        }
        const quantity = Math.min(Number(product.requested_quantity || 1), Number(product.available_stock || 0));
        if (quantity <= 0) {
            unavailable++;
            continue;
        }
        if (quantity < Number(product.requested_quantity || 1)) reduced++;
        add(product, quantity);
        added++;
    }
    if (!added) return toast.warning('Các sản phẩm trong đơn hiện không còn khả dụng để mua lại.');
    if (unavailable || reduced) toast.warning('Một số sản phẩm đã hết hàng hoặc số lượng được điều chỉnh theo tồn kho hiện tại.');
    else toast.success('Đã thêm lại toàn bộ sản phẩm vào giỏ hàng.');
    router.visit(`/shop/${props.store.slug}/cart`);
}

const InfoCard = defineComponent({
    props: ['title'],
    setup: (p, { slots }) => () => h('article', { class: 'border border-black/15 bg-white/55 p-6' }, [
        h('h2', { class: 'text-lg font-black' }, p.title),
        h('div', { class: 'mt-4 space-y-1 text-sm leading-6 text-black/65' }, slots.default?.()),
    ]),
});
const PriceRow = defineComponent({
    props: ['label', 'value', 'discount'],
    setup: (p) => () => h('div', { class: 'flex justify-between gap-4' }, [
        h('span', { class: 'text-black/55' }, p.label),
        h('span', { class: p.discount ? 'font-bold text-emerald-700' : 'font-bold' }, money(p.value)),
    ]),
});
const statusClass = (status) => ({
    pending: 'bg-amber-100 text-amber-800', approved: 'bg-blue-100 text-blue-800', confirmed: 'bg-blue-100 text-blue-800',
    partial: 'bg-violet-100 text-violet-800', shipping: 'bg-violet-100 text-violet-800', completed: 'bg-emerald-100 text-emerald-800',
    delivered: 'bg-emerald-100 text-emerald-800', cancelled: 'bg-red-100 text-red-800', returned: 'bg-orange-100 text-orange-800',
})[status] || 'bg-neutral-200 text-neutral-700';
onMounted(startNotifications);
useAutoApiRefresh(() => new Promise((resolve) => {
    router.reload({ only: ['order'], preserveScroll: true, preserveState: true, onFinish: resolve });
}), 15000);
</script>
