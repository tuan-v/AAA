<template>
    <Head title="Bán hàng tại quầy" />
    <AdminLayout>
        <PageBreadcrumb title="Bán hàng tại quầy" :items="[{ text: 'Bán hàng tại quầy', link: null }]" />

        <div v-if="loading" class="rounded-2xl border bg-white p-10 text-center text-gray-500">Đang tải dữ liệu bán hàng...</div>
        <div v-else>
            <div class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border bg-white p-3 shadow-sm">
                <span class="mr-1 text-sm font-semibold text-gray-600">Hóa đơn chờ:</span>
                <div v-for="item in pendingDrafts" :key="item.id" class="flex overflow-hidden rounded-xl border" :class="draft?.id === item.id ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:border-indigo-300'">
                    <button type="button" class="px-4 py-2 text-sm font-semibold" @click="loadDraft(item)">
                        {{ item.code }} <span v-if="item.items.length">({{ item.items.length }})</span>
                    </button>
                    <button type="button" class="border-l px-3 text-lg leading-none text-red-500 hover:bg-red-50" :disabled="cancellingDraftId === item.id" title="Hủy hóa đơn chờ" @click.stop="cancelDraft(item)">×</button>
                </div>
                <span v-if="!pendingDrafts.length" class="text-sm text-gray-400">Không có</span>
                <span v-if="savingDraft" class="ml-auto text-xs text-gray-400">Đang lưu...</span>
            </div>
            <div class="grid gap-6 xl:grid-cols-[1fr_420px]">
            <section class="space-y-4">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="text-sm font-medium text-gray-700">
                            Kho bán hàng
                            <select v-model="warehouseId" class="mt-1 w-full rounded-xl border-gray-300">
                                <option value="">Chọn kho</option>
                                <option v-for="warehouse in options.warehouses" :key="warehouse.id" :value="warehouse.id">
                                    {{ warehouse.code }} - {{ warehouse.name }}
                                </option>
                            </select>
                        </label>
                        <label class="text-sm font-medium text-gray-700">
                            Tìm sản phẩm
                            <input v-model.trim="search" class="mt-1 w-full rounded-xl border-gray-300" placeholder="Tên, SKU hoặc mã vạch" />
                        </label>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-3">
                    <button
                        v-for="product in filteredProducts"
                        :key="product.id"
                        type="button"
                        :disabled="!draft || !warehouseId || stockOf(product) <= 0"
                        class="rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:border-indigo-300 hover:shadow disabled:cursor-not-allowed disabled:opacity-50"
                        @click="addProduct(product)"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ product.name }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ product.sku || product.barcode || 'Chưa có mã' }}</p>
                            </div>
                            <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">Còn {{ stockOf(product) }}</span>
                        </div>
                        <p class="mt-4 text-lg font-bold text-indigo-700">{{ money(product.sell_price) }}</p>
                    </button>
                </div>
            </section>

            <aside class="h-fit rounded-2xl border bg-white shadow-sm xl:sticky xl:top-5">
                <div class="border-b p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Đơn tại quầy</h2>
                            <p class="text-sm text-gray-500">{{ draft ? `${draft.code} · ${cart.length} mặt hàng` : 'Chưa tạo hóa đơn chờ' }}</p>
                        </div>
                        <button v-if="!draft" type="button" class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white" :disabled="creatingDraft" @click="createDraft">
                            {{ creatingDraft ? 'Đang tạo...' : 'Tạo hóa đơn chờ' }}
                        </button>
                    </div>
                </div>

                <div v-if="!cart.length" class="p-10 text-center text-sm text-gray-400">Chưa có sản phẩm trong giỏ.</div>
                <div v-else class="max-h-72 divide-y overflow-y-auto">
                    <div v-for="item in cart" :key="item.product_id" class="p-4">
                        <div class="flex justify-between gap-3">
                            <p class="font-medium text-gray-800">{{ item.name }}</p>
                            <button class="text-sm text-red-500" @click="removeItem(item.product_id)">Xóa</button>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex items-center rounded-lg border">
                                <button class="px-3 py-1" @click="changeQuantity(item, -1)">−</button>
                                <input v-model.number="item.quantity" type="number" min="1" :max="item.stock" class="w-16 border-0 p-1 text-center text-sm" @change="normalizeQuantity(item)" />
                                <button class="px-3 py-1" @click="changeQuantity(item, 1)">+</button>
                            </div>
                            <span class="font-semibold">{{ money(item.quantity * item.unit_price) }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 border-t p-5">
                    <label class="block text-sm font-medium text-gray-700">
                        <span class="flex items-center justify-between">Khách hàng <button type="button" class="text-indigo-600" @click="showCustomerForm = !showCustomerForm">+ Thêm nhanh</button></span>
                        <select v-model="customerId" class="mt-1 w-full rounded-xl border-gray-300">
                            <option value="">Khách lẻ (không cần chọn)</option>
                            <option v-for="customer in options.customers" :key="customer.id" :value="customer.id">
                                {{ customer.code }} - {{ customer.name }}
                            </option>
                        </select>
                    </label>

                    <div v-if="showCustomerForm" class="space-y-2 rounded-xl border bg-slate-50 p-3">
                        <input v-model.trim="newCustomer.name" class="w-full rounded-lg border-gray-300" placeholder="Tên khách hàng *" />
                        <input v-model.trim="newCustomer.phone" class="w-full rounded-lg border-gray-300" placeholder="Số điện thoại" />
                        <input v-model.trim="newCustomer.email" type="email" class="w-full rounded-lg border-gray-300" placeholder="Email" />
                        <button type="button" class="w-full rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white" :disabled="savingCustomer || !newCustomer.name" @click="createCustomer">{{ savingCustomer ? 'Đang lưu...' : 'Lưu và chọn khách hàng' }}</button>
                    </div>

                    <div class="rounded-xl p-3 text-sm" :class="selectedCustomer?.debt_eligible ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                        {{ selectedCustomer?.debt_eligible ? 'Khách đã từng mua hàng: được phép ghi nợ.' : 'Khách lẻ hoặc khách mới: phải thanh toán đủ, chưa được ghi nợ.' }}
                    </div>

                    <div class="rounded-xl bg-blue-50 p-3 text-sm font-semibold text-blue-700">
                        Hóa đơn VAT (10%)
                    </div>

                    <label class="block text-sm font-medium text-gray-700">
                        Phiếu giảm giá
                        <select v-model="couponCode" class="mt-1 w-full rounded-xl border-gray-300">
                            <option value="">Không áp dụng</option>
                            <option v-for="coupon in options.coupons" :key="coupon.id" :value="coupon.code">
                                {{ coupon.code }} - {{ coupon.name }}
                            </option>
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        Phương thức thanh toán
                        <select v-model="paymentMethod" class="mt-1 w-full rounded-xl border-gray-300">
                            <option value="cash">Tiền mặt</option>
                            <option value="momo">MoMo</option>
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        Tiền tệ thanh toán
                        <select v-model="paymentCurrencyId" class="mt-1 w-full rounded-xl border-gray-300">
                            <option v-for="currency in options.currencies" :key="currency.id" :value="currency.id">
                                {{ currency.code }} - {{ currency.name }}
                            </option>
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        Khách thanh toán ({{ selectedPaymentCurrency?.code }})
                        <div class="relative mt-1">
                            <input :value="paidAmountDisplay" inputmode="decimal" class="w-full rounded-xl border-gray-300 pr-14 text-right font-semibold" @input="onPaidAmountInput" @blur="formatPaidAmount" />
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">{{ selectedPaymentCurrency?.symbol }}</span>
                        </div>
                    </label>

                    <div v-if="paymentRate !== 1" class="rounded-xl bg-blue-50 p-3 text-sm text-blue-800">
                        <div class="flex justify-between"><span>Tỷ giá</span><strong>1 {{ selectedPaymentCurrency?.code }} = {{ formatNumber(paymentRate) }} {{ options.currency.code }}</strong></div>
                        <div class="mt-1 flex justify-between"><span>Quy đổi</span><strong>{{ money(paymentAmountBase) }}</strong></div>
                    </div>

                    <div class="space-y-2 rounded-xl bg-slate-50 p-4 text-sm">
                        <div class="flex justify-between"><span>Tạm tính</span><span>{{ money(subtotal) }}</span></div>
                        <div v-if="discount" class="flex justify-between text-green-600"><span>Giảm giá</span><span>-{{ money(discount) }}</span></div>
                        <div v-if="vatAmount" class="flex justify-between"><span>VAT (10%)</span><span>{{ money(vatAmount) }}</span></div>
                        <div class="flex justify-between text-base font-bold"><span>Tổng cộng</span><span>{{ money(total) }}</span></div>
                        <div class="flex justify-between font-semibold" :class="debt > 0 ? 'text-red-600' : 'text-green-600'">
                            <span>Còn nợ</span><span>{{ money(debt) }}</span>
                        </div>
                        <div v-if="change > 0" class="flex justify-between font-semibold text-indigo-600">
                            <span>Tiền thừa trả khách</span><span>{{ paymentMoney(changePayment) }}</span>
                        </div>
                    </div>

                    <p v-if="error" class="rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>
                    <button
                        class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                        :disabled="submitting || !canCheckout"
                        @click="checkout"
                    >
                        {{ submitting ? 'Đang tạo đơn...' : 'Hoàn tất bán hàng' }}
                    </button>
                    <button class="w-full rounded-xl border px-5 py-2.5 font-semibold text-gray-700" @click="openHistory">Lịch sử hóa đơn</button>
                </div>
            </aside>
            </div>
        </div>

        <div v-if="receipt" class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/50 p-4" @click.self="receipt = null">
            <div class="receipt-print max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl print:max-h-none print:shadow-none">
                <div class="text-center"><h2 class="text-2xl font-bold">HÓA ĐƠN BÁN HÀNG</h2><p class="mt-1 text-gray-500">{{ receipt.code }}</p></div>
                <div class="mt-5 space-y-1 text-sm"><p>Khách hàng: {{ receipt.customer?.name || 'Khách lẻ' }}</p><p>Thanh toán: {{ receipt.payment_method === 'momo' ? 'MoMo' : 'Tiền mặt' }} · {{ receipt.payment_currency?.code || receipt.currency?.code }}</p><p v-if="receipt.payment_exchange_rate !== 1">Tỷ giá: 1 {{ receipt.payment_currency?.code }} = {{ formatNumber(receipt.payment_exchange_rate) }} {{ receipt.currency?.code }}</p></div>
                <table class="mt-4 w-full text-sm"><tbody><tr v-for="item in receipt.items" :key="item.name" class="border-t"><td class="py-2">{{ item.name }} × {{ item.quantity }}</td><td class="py-2 text-right">{{ money(item.amount) }}</td></tr></tbody></table>
                <div class="mt-4 space-y-2 border-t pt-4 text-sm"><div class="flex justify-between"><span>Tạm tính</span><span>{{ money(receipt.subtotal) }}</span></div><div class="flex justify-between"><span>Giảm giá</span><span>-{{ money(receipt.discount_amount) }}</span></div><div class="flex justify-between text-lg font-bold"><span>Tổng cộng</span><span>{{ money(receipt.total_amount) }}</span></div><div class="flex justify-between"><span>Khách đưa</span><span>{{ money(receipt.tendered_amount) }}</span></div><div v-if="receipt.change_amount > 0" class="flex justify-between font-semibold"><span>Tiền thừa</span><span>{{ money(receipt.change_amount) }}</span></div><div v-if="receipt.debt_amount > 0" class="flex justify-between font-semibold text-red-600"><span>Còn nợ</span><span>{{ money(receipt.debt_amount) }}</span></div></div>
                <div class="mt-6 flex gap-3 print:hidden"><button class="flex-1 rounded-xl border px-4 py-2" @click="receipt = null">Đóng</button><button class="flex-1 rounded-xl bg-indigo-600 px-4 py-2 font-semibold text-white" @click="printReceipt">In hóa đơn</button></div>
            </div>
        </div>

        <div v-if="showHistory" class="fixed inset-0 z-[999] bg-black/40 p-4" @click.self="showHistory = false">
            <div class="ml-auto h-full w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"><div class="flex justify-between"><h2 class="text-xl font-bold">Lịch sử hóa đơn POS</h2><button @click="showHistory = false">✕</button></div><div class="mt-5 divide-y"><button v-for="order in history" :key="order.id" class="flex w-full justify-between gap-4 py-3 text-left" @click="loadReceipt(order.id)"><span><b>{{ order.code }}</b><small class="block text-gray-500">{{ order.customer?.name }}</small></span><span class="flex shrink-0 flex-col items-end gap-1"><span class="font-semibold">{{ money(order.total_amount) }}</span><small class="rounded-full px-2.5 py-1 font-medium" :class="orderStatus(order).class">{{ orderStatus(order).label }}</small></span></button></div></div>
        </div>
    </AdminLayout>
</template>

<script setup>
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { formatMoney } from "@/config/helpers";

const loading = ref(true);
const submitting = ref(false);
const creatingDraft = ref(false);
const error = ref("");
const search = ref("");
const warehouseId = ref("");
const customerId = ref("");
const paymentMethod = ref("cash");
const invoiceType = ref("vat");
const couponCode = ref("");
const paidAmount = ref(0);
const paidAmountDisplay = ref('0');
const paymentCurrencyId = ref('');
const paymentReference = ref('');
const cart = ref([]);
const receipt = ref(null);
const showHistory = ref(false);
const history = ref([]);
const draft = ref(null);
const showCustomerForm = ref(false);
const savingCustomer = ref(false);
const savingDraft = ref(false);
const pendingDrafts = ref([]);
const hydratingDraft = ref(false);
const cancellingDraftId = ref(null);
let saveTimer = null;
const newCustomer = reactive({ name: "", phone: "", email: "" });
const options = reactive({ customers: [], warehouses: [], products: [], coupons: [], currencies: [], currency: { code: "VND", symbol: "₫" } });

const money = (value) => formatMoney(value || 0, options.currency);
const orderStatuses = {
    pending: { label: 'Chờ xác nhận', class: 'bg-yellow-100 text-yellow-700' },
    approved: { label: 'Đã xác nhận', class: 'bg-blue-100 text-blue-700' },
    partial: { label: 'Đang giao hàng', class: 'bg-purple-100 text-purple-700' },
    completed: { label: 'Hoàn thành', class: 'bg-green-100 text-green-700' },
    cancelled: { label: 'Đã hủy', class: 'bg-red-100 text-red-700' },
    return_pending_warehouse: { label: 'Chờ kho nhận hàng hoàn', class: 'bg-orange-100 text-orange-700' },
    return_pending_accountant: { label: 'Chờ duyệt hoàn', class: 'bg-orange-100 text-orange-700' },
    returned: { label: 'Đã hoàn hàng', class: 'bg-orange-100 text-orange-700' },
};
const orderStatus = (order) => orderStatuses[order.effective_status || order.status]
    || { label: 'Không rõ', class: 'bg-gray-100 text-gray-600' };
const formatNumber = (value) => Number(value || 0).toLocaleString('vi-VN', { maximumFractionDigits: 8 });
const selectedPaymentCurrency = computed(() => options.currencies.find((item) => Number(item.id) === Number(paymentCurrencyId.value)) || options.currency);
const paymentRate = computed(() => Number(selectedPaymentCurrency.value?.rate || 1));
const paymentMoney = (value) => formatMoney(value || 0, selectedPaymentCurrency.value);
const selectedCustomer = computed(() => options.customers.find((item) => Number(item.id) === Number(customerId.value)));
const filteredProducts = computed(() => {
    const keyword = search.value.toLowerCase();
    return options.products.filter((product) => !keyword || [product.name, product.sku, product.barcode].some((value) => String(value || "").toLowerCase().includes(keyword)));
});
const stockOf = (product) => Number(product.stocks?.[warehouseId.value] || 0);
const subtotal = computed(() => cart.value.reduce((sum, item) => sum + item.quantity * item.unit_price, 0));
const selectedCoupon = computed(() => options.coupons.find((item) => item.code === couponCode.value));
const discount = computed(() => {
    const coupon = selectedCoupon.value;
    if (!coupon || subtotal.value < Number(coupon.minimum_order_amount || 0)) return 0;
    const value = coupon.type === "percent" ? subtotal.value * Number(coupon.value) / 100 : Number(coupon.value);
    return Math.min(subtotal.value, coupon.maximum_discount == null ? value : Math.min(value, Number(coupon.maximum_discount)));
});
const vatAmount = computed(() => subtotal.value * 0.1);
const total = computed(() => Math.max(0, subtotal.value + vatAmount.value - discount.value));
const paymentAmountBase = computed(() => Number(paidAmount.value || 0) * paymentRate.value);
const settledAmount = computed(() => Math.min(total.value, paymentAmountBase.value));
const debt = computed(() => Math.max(0, total.value - settledAmount.value));
const change = computed(() => paymentMethod.value === 'cash' ? Math.max(0, paymentAmountBase.value - total.value) : 0);
const changePayment = computed(() => change.value / paymentRate.value);
const canCheckout = computed(() => draft.value && cart.value.length && warehouseId.value && paymentCurrencyId.value && (paymentMethod.value === 'cash' || paymentAmountBase.value <= total.value) && (debt.value === 0 || selectedCustomer.value?.debt_eligible));

watch(total, (value, oldValue) => {
    if (Math.abs(paymentAmountBase.value - Number(oldValue || 0)) < 0.01 || !selectedCustomer.value?.debt_eligible) setPaidAmount(value / paymentRate.value);
});
watch(customerId, () => {
    if (!selectedCustomer.value?.debt_eligible) setPaidAmount(total.value / paymentRate.value);
});
watch(paymentCurrencyId, () => setPaidAmount(total.value / paymentRate.value));
watch(warehouseId, (value, oldValue) => {
    if (!hydratingDraft.value && oldValue && value !== oldValue) cart.value = [];
});
watch([cart, customerId, warehouseId, couponCode, invoiceType], scheduleDraftSave, { deep: true });

function addProduct(product) {
    if (!draft.value) return;
    const existing = cart.value.find((item) => item.product_id === product.id);
    if (existing) changeQuantity(existing, 1);
    else cart.value.push({ product_id: product.id, name: product.name, quantity: 1, unit_price: Number(product.sell_price), stock: stockOf(product) });
}
function changeQuantity(item, delta) { item.quantity = Math.max(1, Math.min(item.stock, Number(item.quantity) + delta)); }
function normalizeQuantity(item) { item.quantity = Math.max(1, Math.min(item.stock, Number(item.quantity) || 1)); }
function removeItem(productId) { cart.value = cart.value.filter((item) => item.product_id !== productId); }
function setPaidAmount(value) { paidAmount.value = Math.max(0, Number(value || 0)); formatPaidAmount(); }
function formatPaidAmount() { paidAmountDisplay.value = Number(paidAmount.value || 0).toLocaleString('vi-VN', { minimumFractionDigits: 0, maximumFractionDigits: 2 }); }
function onPaidAmountInput(event) {
    const input = event.target;
    const rawValue = String(input.value || '');
    const caret = input.selectionStart ?? rawValue.length;
    const rawBeforeCaret = rawValue.slice(0, caret);
    const hasDecimalSeparator = rawValue.includes(',');
    const [rawInteger = '', ...rawDecimals] = rawValue.split(',');
    const integerDigits = rawInteger.replace(/\D/g, '') || '0';
    const decimalDigits = rawDecimals.join('').replace(/\D/g, '').slice(0, 2);
    const groupedInteger = integerDigits
        .replace(/^0+(?=\d)/, '')
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const formatted = `${groupedInteger}${hasDecimalSeparator ? `,${decimalDigits}` : ''}`;

    paidAmountDisplay.value = formatted;
    paidAmount.value = Math.max(0, Number(`${integerDigits}.${decimalDigits || 0}`));
    input.value = formatted;

    nextTick(() => {
        let nextCaret = 0;
        if (rawBeforeCaret.includes(',')) {
            const decimalCaret = rawBeforeCaret.split(',').slice(1).join('').replace(/\D/g, '').length;
            nextCaret = groupedInteger.length + 1 + Math.min(decimalCaret, decimalDigits.length);
        } else {
            const integerCaret = rawBeforeCaret.replace(/\D/g, '').length;
            let seenDigits = 0;
            while (nextCaret < groupedInteger.length && seenDigits < integerCaret) {
                if (/\d/.test(groupedInteger[nextCaret])) seenDigits += 1;
                nextCaret += 1;
            }
        }
        input.setSelectionRange(nextCaret, nextCaret);
    });
}

async function createDraft() {
    creatingDraft.value = true;
    error.value = '';
    try {
        const created = (await axios.post('/api/sale/pos/drafts')).data.data;
        pendingDrafts.value.unshift(created);
        await loadDraft(created);
    } catch (e) {
        error.value = e.response?.data?.message || 'Không thể tạo hóa đơn chờ.';
    } finally { creatingDraft.value = false; }
}

async function loadDraft(item) {
    if (draft.value && draft.value.id !== item.id) {
        if (draft.value?.id === item.id) clearTimeout(saveTimer);
        await saveDraft();
    }
    hydratingDraft.value = true;
    draft.value = item;
    warehouseId.value = item.warehouse_id || '';
    customerId.value = item.customer?.code === 'KH_LE' ? '' : (item.customer?.id || '');
    couponCode.value = item.coupon_code || '';
    invoiceType.value = 'vat';
    cart.value = (item.items || []).map((line) => {
        const product = options.products.find((candidate) => Number(candidate.id) === Number(line.product_id));
        return {
            product_id: line.product_id,
            name: line.name,
            quantity: Number(line.quantity),
            unit_price: Number(line.unit_price),
            stock: product ? stockOf(product) : Number(line.quantity),
        };
    });
    paidAmount.value = Number(item.total_amount || 0);
    await nextTick();
    hydratingDraft.value = false;
}

function scheduleDraftSave() {
    if (hydratingDraft.value || !draft.value) return;
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveDraft, 400);
}

async function saveDraft() {
    if (!draft.value || hydratingDraft.value) return;
    savingDraft.value = true;
    try {
        const saved = (await axios.put(`/api/sale/pos/drafts/${draft.value.id}`, {
            customer_id: customerId.value || null,
            warehouse_id: warehouseId.value || null,
            invoice_type: 'vat',
            coupon_code: couponCode.value || null,
            items: cart.value.map((item) => ({ product_id: item.product_id, quantity: item.quantity })),
        })).data.data;
        const index = pendingDrafts.value.findIndex((item) => item.id === saved.id);
        if (index >= 0) pendingDrafts.value[index] = saved;
    } catch (e) {
        error.value = Object.values(e.response?.data?.errors || {}).flat()[0] || e.response?.data?.message || 'Không thể lưu hóa đơn chờ.';
    } finally { savingDraft.value = false; }
}

async function cancelDraft(item) {
    if (!window.confirm(`Hủy hóa đơn chờ ${item.code}?`)) return;
    cancellingDraftId.value = item.id;
    error.value = '';
    try {
        clearTimeout(saveTimer);
        await axios.delete(`/api/sale/pos/drafts/${item.id}`);
        pendingDrafts.value = pendingDrafts.value.filter((candidate) => candidate.id !== item.id);
        if (draft.value?.id === item.id) {
            hydratingDraft.value = true;
            draft.value = null;
            cart.value = [];
            customerId.value = '';
            warehouseId.value = '';
            couponCode.value = '';
            invoiceType.value = 'vat';
            await nextTick();
            hydratingDraft.value = false;
        }
    } catch (e) {
        error.value = Object.values(e.response?.data?.errors || {}).flat()[0] || e.response?.data?.message || 'Không thể hủy hóa đơn chờ.';
    } finally { cancellingDraftId.value = null; }
}

async function createCustomer() {
    savingCustomer.value = true;
    error.value = '';
    try {
        const customer = (await axios.post('/api/sale/pos/customers', newCustomer)).data.data;
        options.customers.push(customer);
        customerId.value = customer.id;
        Object.assign(newCustomer, { name: '', phone: '', email: '' });
        showCustomerForm.value = false;
    } catch (e) {
        error.value = Object.values(e.response?.data?.errors || {}).flat()[0] || e.response?.data?.message || 'Không thể thêm khách hàng.';
    } finally { savingCustomer.value = false; }
}

async function checkout() {
    if (paymentMethod.value === 'momo' && !paymentReference.value) {
        paymentReference.value = window.prompt('Mã giao dịch MoMo', '') || '';
        if (!paymentReference.value) return;
    }
    submitting.value = true;
    error.value = "";
    try {
        clearTimeout(saveTimer);
        await saveDraft();
        const response = await axios.post("/api/sale/pos/orders", {
            draft_id: draft.value?.id,
            customer_id: customerId.value,
            warehouse_id: warehouseId.value,
            payment_method: paymentMethod.value,
            payment_currency_id: paymentCurrencyId.value,
            invoice_type: 'vat',
            coupon_code: couponCode.value || null,
            paid_amount: paidAmount.value,
            payment_reference: paymentMethod.value === 'momo' ? paymentReference.value : null,
            items: cart.value.map((item) => ({ product_id: item.product_id, quantity: item.quantity, vat_percent: 10 })),
        });
        receipt.value = response.data.data;
        pendingDrafts.value = pendingDrafts.value.filter((item) => item.id !== draft.value?.id);
        cart.value = [];
        customerId.value = "";
        couponCode.value = "";
        invoiceType.value = 'vat';
        draft.value = null;
    } catch (e) {
        error.value = Object.values(e.response?.data?.errors || {}).flat()[0] || e.response?.data?.message || "Không thể tạo đơn bán tại quầy.";
    } finally { submitting.value = false; }
}

async function openHistory() { history.value = (await axios.get("/api/sale/pos/history")).data.data; showHistory.value = true; }
async function loadReceipt(id) { receipt.value = (await axios.get(`/api/sale/pos/orders/${id}`)).data.data; showHistory.value = false; }
function printReceipt() { window.print(); }

onMounted(async () => {
    try {
        Object.assign(options, (await axios.get("/api/sale/pos/options")).data);
        paymentCurrencyId.value = options.currency.id;
        pendingDrafts.value = (await axios.get('/api/sale/pos/drafts')).data.data;
    }
    catch (e) { error.value = e.response?.data?.message || "Không thể tải dữ liệu bán hàng."; }
    finally { loading.value = false; }
});
</script>

<style scoped>
@media print {
    :global(body *) { visibility: hidden; }
    .receipt-print, .receipt-print * { visibility: visible; }
    .receipt-print { position: fixed; inset: 0; margin: auto; width: 100%; }
}
</style>
