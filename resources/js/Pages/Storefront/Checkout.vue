<template>
    <Head title="Thanh toán" />
    <main class="min-h-screen bg-slate-50">
        <nav class="border-b bg-white">
            <div class="mx-auto flex max-w-6xl justify-between px-5 py-4">
                <Link
                    :href="`/shop/${store.slug}/cart`"
                    class="font-black text-indigo-700"
                    >← Quay lại giỏ hàng</Link
                >
                <div class="flex items-center gap-3">
                    <NotificationBadgeLink :slug="store.slug" :show-label="false" />
                    <b>Thanh toán an toàn</b>
                </div>
            </div>
        </nav>

        <div class="mx-auto grid max-w-6xl gap-7 px-5 py-10 lg:grid-cols-3">
            <form class="space-y-6 lg:col-span-2" @submit.prevent="submit">
                <section class="rounded-3xl border bg-white p-6">
                    <h2 class="text-xl font-black">Thông tin nhận hàng</h2>
                    <div v-if="account" class="mt-4">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <select
                                v-model="selectedAddressId"
                                class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 font-semibold text-black outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50"
                                @change="selectAddress(selectedAddressId)"
                            >
                                <option value="">
                                    {{
                                        addresses.length
                                            ? "Chọn địa chỉ đã lưu"
                                            : "Chưa có địa chỉ đã lưu"
                                    }}
                                </option>
                                <option
                                    v-for="address in addresses"
                                    :key="address.id"
                                    :value="address.id"
                                >
                                    {{ address.label }} — {{ address.address }}
                                </option>
                            </select>
                            <button
                                type="button"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-700 transition hover:border-indigo-300 hover:bg-indigo-100"
                                @click="toggleNewAddressForm"
                            >
                                <span class="text-lg leading-none">{{
                                    showNewAddressForm ? "×" : "+"
                                }}</span>
                                {{
                                    showNewAddressForm
                                        ? "Đóng"
                                        : "Thêm địa chỉ mới"
                                }}
                            </button>
                        </div>

                        <div
                            v-if="showNewAddressForm"
                            class="mt-4 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5"
                        >
                            <div class="mb-4">
                                <h3 class="font-black text-slate-900">
                                    Thêm địa chỉ nhận hàng
                                </h3>
                                <p class="mt-1 text-xs text-slate-500">
                                    Địa chỉ sẽ được lưu vào tài khoản và tự động
                                    chọn cho đơn hàng này.
                                </p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input
                                    v-model.trim="addressForm.label"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-black"
                                    placeholder="Tên địa chỉ, ví dụ: Nhà riêng *"
                                />
                                <input
                                    v-model.trim="addressForm.recipient_name"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-black"
                                    placeholder="Tên người nhận *"
                                />
                                <input
                                    v-model.trim="addressForm.phone"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-black sm:col-span-2"
                                    placeholder="Số điện thoại *"
                                />
                                <select
                                    v-model="addressForm.province_id"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-black"
                                    @change="loadWards(addressForm.province_id)"
                                >
                                    <option value="">
                                        Chọn Tỉnh/Thành phố *
                                    </option>
                                    <option
                                        v-for="province in provinces"
                                        :key="province.id"
                                        :value="province.id"
                                    >
                                        {{ province.name }}
                                    </option>
                                </select>
                                <select
                                    v-model="addressForm.ward_id"
                                    :disabled="
                                        !addressForm.province_id || loadingWards
                                    "
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-black disabled:bg-slate-100 disabled:text-slate-400"
                                >
                                    <option value="">
                                        {{
                                            loadingWards
                                                ? "Đang tải..."
                                                : "Chọn Xã/Phường *"
                                        }}
                                    </option>
                                    <option
                                        v-for="ward in wards"
                                        :key="ward.id"
                                        :value="ward.id"
                                    >
                                        {{ ward.name }}
                                    </option>
                                </select>
                                <textarea
                                    v-model.trim="addressForm.address_detail"
                                    rows="2"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-black sm:col-span-2"
                                    placeholder="Số nhà, tên đường, tòa nhà, thôn/xóm... *"
                                ></textarea>
                                <label
                                    class="flex items-center gap-2 text-sm text-slate-600 sm:col-span-2"
                                >
                                    <input
                                        v-model="addressForm.is_default"
                                        type="checkbox"
                                        class="rounded border-slate-300 text-indigo-600"
                                    />
                                    Đặt làm địa chỉ mặc định
                                </label>
                            </div>
                            <p
                                v-if="addressError"
                                class="mt-3 text-sm font-medium text-red-600"
                            >
                                {{ addressError }}
                            </p>
                            <div class="mt-4 flex justify-end">
                                <button
                                    type="button"
                                    :disabled="savingAddress"
                                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                                    @click="saveNewAddress"
                                >
                                    {{
                                        savingAddress
                                            ? "Đang lưu..."
                                            : "Lưu và sử dụng địa chỉ"
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <input
                            v-model="form.name"
                            required
                            class="rounded-xl border px-4 py-3 font-medium text-black placeholder:text-slate-500"
                            placeholder="Họ và tên *"
                        />
                        <input
                            v-model="form.phone"
                            required
                            class="rounded-xl border px-4 py-3 font-medium text-black placeholder:text-slate-500"
                            placeholder="Số điện thoại *"
                        />
                        <input
                            v-model="form.email"
                            type="email"
                            class="rounded-xl border px-4 py-3 font-medium text-black placeholder:text-slate-500 sm:col-span-2"
                            placeholder="Email"
                        />
                        <textarea
                            v-model="form.address"
                            required
                            rows="3"
                            class="rounded-xl border px-4 py-3 font-semibold text-black placeholder:text-slate-500 sm:col-span-2"
                            placeholder="Địa chỉ đầy đủ *"
                        />
                    </div>
                </section>

                <section class="rounded-3xl border bg-white p-6">
                    <h2 class="text-xl font-black">Giao hàng và thanh toán</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="rounded-xl border p-4"
                            ><input
                                v-model="form.shipping_method"
                                type="radio"
                                value="standard"
                            />
                            <b>Giao tiêu chuẩn</b>
                            <p class="ml-5 text-sm text-slate-500">
                                Dự kiến 2–3 ngày · Miễn phí
                            </p></label
                        >
                        <label class="rounded-xl border p-4"
                            ><input
                                v-model="form.shipping_method"
                                type="radio"
                                value="express"
                            />
                            <b>Giao nhanh</b>
                            <p class="ml-5 text-sm text-slate-500">
                                Dự kiến 1 ngày · {{ money(30000) }}
                            </p></label
                        >
                        <label class="rounded-xl border p-4"
                            ><input
                                v-model="form.payment_method"
                                type="radio"
                                value="cod"
                            />
                            <b>COD</b>
                            <p class="ml-5 text-sm text-slate-500">
                                Thanh toán khi nhận hàng
                            </p></label
                        >
                        <label class="rounded-xl border p-4"
                            ><input
                                v-model="form.payment_method"
                                type="radio"
                                value="bank_transfer"
                            />
                            <b>Chuyển khoản</b>
                            <p class="ml-5 text-sm text-slate-500">
                                Cửa hàng xác nhận sau
                            </p></label
                        >
                    </div>
                    <textarea
                        v-model="form.note"
                        rows="2"
                        class="mt-4 w-full rounded-xl border px-4 py-3"
                        placeholder="Ghi chú"
                    />
                </section>
            </form>

            <aside class="h-fit rounded-3xl border bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black">Đơn hàng</h2>
                <div
                    v-for="item in cart"
                    :key="item.product_id"
                    class="mt-3 flex justify-between gap-3 text-sm"
                >
                    <span>{{ item.name }} × {{ item.quantity }}</span
                    ><b>{{ money(item.price * item.quantity) }}</b>
                </div>
                <div class="mt-5 space-y-2 border-t pt-4">
                    <div class="flex justify-between">
                        <span>Tạm tính</span><span>{{ money(total) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>VAT (10%)</span
                        ><span>{{ money(vatAmount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Phí giao</span
                        ><span>{{ money(shippingFee) }}</span>
                    </div>
                    <div
                        v-if="discount"
                        class="flex justify-between text-emerald-600"
                    >
                        <span>Giảm giá</span><span>-{{ money(discount) }}</span>
                    </div>
                </div>
                <div class="mt-5">
                    <label class="text-sm font-bold">Mã giảm giá</label>
                    <div class="mt-2 flex">
                        <input
                            v-model="form.coupon_code"
                            class="min-w-0 flex-1 rounded-l-xl border px-3 py-2"
                            placeholder="Nhập mã"
                        /><button
                            type="button"
                            class="rounded-r-xl bg-slate-900 px-3 text-white"
                            @click="calculateVoucher"
                        >
                            Áp dụng
                        </button>
                    </div>
                    <button
                        v-for="voucher in vouchers"
                        :key="voucher.code"
                        type="button"
                        class="mt-2 mr-2 rounded-full bg-indigo-50 px-3 py-1 text-xs text-indigo-700"
                        @click="
                            form.coupon_code = voucher.code;
                            calculateVoucher();
                        "
                    >
                        {{ voucher.code }}
                    </button>
                </div>
                <div class="mt-6 flex justify-between text-xl font-black">
                    <span>Tổng cộng</span><span>{{ money(grandTotal) }}</span>
                </div>
                <button
                    :disabled="submitting || !cart.length"
                    class="mt-6 w-full rounded-xl bg-indigo-600 py-3.5 font-black text-white disabled:opacity-50"
                    @click="submit"
                >
                    {{ submitting ? "Đang xử lý..." : "Đặt hàng COD" }}
                </button>
            </aside>
        </div>
    </main>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import axios from "axios";
import { computed, onMounted, reactive, ref } from "vue";
import { storefrontToast as toast } from "@/utils/storefrontToast";
import NotificationBadgeLink from "@/components/Storefront/NotificationBadgeLink.vue";
import { useStorefrontCart } from "@/composables/useStorefrontCart";

const props = defineProps({ store: Object });
const { cart, total, clear } = useStorefrontCart(props.store.slug);
const addresses = ref([]);
const account = ref(null);
const selectedAddressId = ref("");
const showNewAddressForm = ref(false);
const provinces = ref([]);
const wards = ref([]);
const loadingWards = ref(false);
const savingAddress = ref(false);
const addressError = ref("");
const vouchers = ref([]);
const submitting = ref(false);
const error = ref("");
const discount = ref(0);
const form = reactive({
    name: "",
    phone: "",
    email: "",
    address: "",
    shipping_method: "standard",
    payment_method: "cod",
    coupon_code: "",
    note: "",
});
const addressForm = reactive({
    label: "Nhà riêng",
    recipient_name: "",
    phone: "",
    province_id: "",
    ward_id: "",
    address_detail: "",
    is_default: false,
});
const shippingFee = computed(() =>
    form.shipping_method === "express" ? 30000 : 0,
);
const vatAmount = computed(() => total.value * 0.1);
const grandTotal = computed(
    () =>
        Math.max(0, total.value + vatAmount.value - discount.value) +
        shippingFee.value,
);
const money = (value) =>
    `${new Intl.NumberFormat("vi-VN").format(Number(value || 0))} ${props.store.currency.symbol}`;

function selectAddress(id) {
    const address = addresses.value.find((item) => item.id == id);
    if (address) {
        form.name = address.recipient_name;
        form.phone = address.phone;
        form.address = address.address;
    }
}

async function toggleNewAddressForm() {
    showNewAddressForm.value = !showNewAddressForm.value;
    addressError.value = "";
    if (!showNewAddressForm.value) return;
    addressForm.recipient_name = form.name || account.value?.name || "";
    addressForm.phone = form.phone || account.value?.phone || "";
    if (!provinces.value.length) {
        provinces.value = (await axios.get("/shop/locations/provinces")).data;
    }
}

async function loadWards(provinceId) {
    addressForm.ward_id = "";
    wards.value = [];
    if (!provinceId) return;
    loadingWards.value = true;
    try {
        wards.value = (
            await axios.get(`/shop/locations/provinces/${provinceId}/wards`)
        ).data;
    } finally {
        loadingWards.value = false;
    }
}

async function saveNewAddress() {
    addressError.value = "";
    if (
        !addressForm.label ||
        !addressForm.recipient_name ||
        !addressForm.phone ||
        !addressForm.province_id ||
        !addressForm.ward_id ||
        !addressForm.address_detail
    ) {
        addressError.value = "Vui lòng nhập đầy đủ thông tin địa chỉ.";
        return;
    }
    if (!/^0[35789][0-9]{8}$/.test(String(addressForm.phone).trim())) {
        addressError.value =
            "Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.";
        return;
    }
    savingAddress.value = true;
    try {
        const { data } = await axios.post(
            `/shop/${props.store.slug}/account/addresses`,
            addressForm,
        );
        const createdAddress = data.address;
        if (createdAddress.is_default) {
            addresses.value.forEach((item) => {
                item.is_default = false;
            });
        }
        addresses.value.unshift(createdAddress);
        selectedAddressId.value = createdAddress.id;
        selectAddress(createdAddress.id);
        showNewAddressForm.value = false;
        toast.success("Đã thêm và chọn địa chỉ nhận hàng mới.");
        Object.assign(addressForm, {
            label: "Nhà riêng",
            recipient_name: form.name,
            phone: form.phone,
            province_id: "",
            ward_id: "",
            address_detail: "",
            is_default: false,
        });
        wards.value = [];
    } catch (exception) {
        addressError.value =
            Object.values(exception.response?.data?.errors || {}).flat()[0] ||
            exception.response?.data?.message ||
            "Không thể lưu địa chỉ mới.";
    } finally {
        savingAddress.value = false;
    }
}

function calculateVoucher() {
    const voucher = vouchers.value.find(
        (item) => item.code === form.coupon_code,
    );
    discount.value =
        !voucher || total.value < voucher.minimum_order_amount
            ? 0
            : Math.min(
                  total.value,
                  voucher.type === "percent"
                      ? (total.value * voucher.value) / 100
                      : voucher.value,
                  voucher.maximum_discount ?? Infinity,
              );
}

async function submit() {
    if (!form.name || !form.phone || !form.address || !cart.value.length) {
        error.value = "Vui lòng nhập đủ thông tin nhận hàng.";
        return toast.warning(error.value);
    }
    if (!/^0[35789][0-9]{8}$/.test(String(form.phone).trim())) {
        error.value = "Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.";
        return toast.warning(error.value);
    }
    submitting.value = true;
    error.value = "";
    try {
        const { data } = await axios.post(
            `/shop/${props.store.slug}/checkout`,
            {
                customer: {
                    name: form.name,
                    phone: form.phone,
                    email: form.email || null,
                    address: form.address,
                },
                shipping_method: form.shipping_method,
                payment_method: form.payment_method,
                coupon_code: form.coupon_code || null,
                note: form.note || null,
                items: cart.value.map((item) => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                })),
            },
        );
        clear();
        toast.success("Đặt hàng thành công.");
        router.visit(
            `/shop/${props.store.slug}/order-success?code=${encodeURIComponent(data.order.code)}`,
        );
    } catch (exception) {
        error.value =
            Object.values(exception.response?.data?.errors || {}).flat()[0] ||
            exception.response?.data?.message ||
            "Không thể đặt hàng.";
        toast.error(error.value);
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    if (!cart.value.length)
        return router.visit(`/shop/${props.store.slug}/cart`);
    const [voucherResponse, accountResponse] = await Promise.all([
        axios.get(`/shop/${props.store.slug}/vouchers`),
        axios.get(`/shop/${props.store.slug}/account/me`),
    ]);
    vouchers.value = voucherResponse.data.vouchers;
    if (accountResponse.data.account) {
        account.value = accountResponse.data.account;
        form.name = account.value.name;
        form.phone = account.value.phone;
        form.email = account.value.email;
        addresses.value = (
            await axios.get(`/shop/${props.store.slug}/account/addresses`)
        ).data.addresses;
        const defaultAddress =
            addresses.value.find((item) => item.is_default) ||
            addresses.value[0];
        if (defaultAddress) {
            selectedAddressId.value = defaultAddress.id;
            selectAddress(defaultAddress.id);
        }
    }
});
</script>
