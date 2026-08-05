<template>
    <Head :title="`Tài khoản - ${store.name}`" />
    <div class="min-h-screen bg-[#f4f2ed] text-[#111]">
        <div
            class="bg-black px-4 py-2 text-center text-[11px] font-bold uppercase tracking-[.18em] text-white"
        >
            Tài khoản khách hàng · Mua sắm và theo dõi đơn hàng
        </div>
        <header class="border-b border-black/10">
            <div
                class="mx-auto flex h-20 max-w-[1500px] items-center gap-5 px-5 lg:px-9"
            >
                <Link
                    :href="`/shop/${store.slug}`"
                    class="text-2xl font-black tracking-[-.06em]"
                    >{{ store.name }}</Link
                ><span class="hidden text-sm text-black/35 sm:block"
                    >/ Tài khoản</span
                ><Link
                    :href="`/shop/${store.slug}`"
                    class="ml-auto rounded-full px-4 py-3 text-sm font-bold hover:bg-black/5"
                    >Tiếp tục mua sắm</Link
                ><NotificationBadgeLink :slug="store.slug" class="border border-black/15" /><Link
                    :href="`/shop/${store.slug}/cart`"
                    class="rounded-full bg-black px-5 py-3 text-sm font-bold text-white"
                    >Giỏ hàng</Link
                >
            </div>
        </header>

        <main class="mx-auto max-w-[1400px] px-5 py-10 lg:px-9 lg:py-16">
            <div v-if="loading" class="py-24 text-center text-black/45">
                Đang tải tài khoản...
            </div>
            <CustomerAuthPanel
                v-else-if="!account"
                :base="base"
                :store-name="store.name"
                @authenticated="refresh"
            />
            <template v-else>
                <section
                    class="mb-10 flex flex-col justify-between gap-5 border-b border-black/20 pb-9 md:flex-row md:items-end"
                >
                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[.22em] text-black/40"
                        >
                            My account
                        </p>
                        <h1
                            class="mt-3 text-5xl font-black tracking-[-.06em] md:text-7xl"
                        >
                            Xin chào, {{ firstName }}
                        </h1>
                        <p class="mt-3 text-sm text-black/45">
                            Quản lý hồ sơ, địa chỉ và hành trình đơn hàng tại
                            {{ store.name }}.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <div
                            class="rounded-full border border-black/15 px-5 py-3 text-sm"
                        >
                            <b>{{ orders.length }}</b> đơn hàng
                        </div>
                        <button
                            class="rounded-full border border-black/15 px-5 py-3 text-sm font-bold hover:bg-black hover:text-white"
                            @click="logout"
                        >
                            Đăng xuất
                        </button>
                    </div>
                </section>

                <div
                    class="grid gap-8 lg:grid-cols-[270px_minmax(0,1fr)] lg:gap-14"
                >
                    <aside>
                        <div
                            class="flex items-center gap-4 border-b border-black/15 pb-6"
                        >
                            <div
                                class="grid h-14 w-14 shrink-0 place-items-center rounded-full bg-[#d8ff43] text-xl font-black"
                            >
                                {{ initials }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-black">
                                    {{ account.name }}
                                </p>
                                <p class="truncate text-xs text-black/45">
                                    {{ account.email }}
                                </p>
                            </div>
                        </div>
                        <nav
                            class="mt-5 grid grid-cols-2 gap-1 sm:grid-cols-5 lg:grid-cols-1"
                        >
                            <button
                                v-for="item in navItems"
                                :key="item.key"
                                class="account-nav"
                                :class="{ active: tab === item.key }"
                                @click="tab = item.key"
                            >
                                <span>{{ item.icon }}</span
                                ><span>{{ item.label }}</span
                                ><span class="ml-auto hidden lg:block">→</span>
                            </button>
                        </nav>
                    </aside>

                    <section class="min-w-0">
                        <div v-if="tab === 'overview'">
                            <SectionTitle
                                eyebrow="Tổng quan"
                                title="Tài khoản của bạn"
                                text="Truy cập nhanh các thông tin quan trọng."
                            />
                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <button
                                    class="summary-card"
                                    @click="tab = 'orders'"
                                >
                                    <span class="text-3xl">◫</span
                                    ><b class="mt-8 text-3xl">{{
                                        orders.length
                                    }}</b
                                    ><span class="text-sm text-black/45"
                                        >Đơn hàng</span
                                    ></button
                                ><button
                                    class="summary-card"
                                    @click="tab = 'addresses'"
                                >
                                    <span class="text-3xl">⌖</span
                                    ><b class="mt-8 text-3xl">{{
                                        addresses.length
                                    }}</b
                                    ><span class="text-sm text-black/45"
                                        >Địa chỉ đã lưu</span
                                    ></button
                                ><button
                                    class="summary-card"
                                    @click="tab = 'profile'"
                                >
                                    <span class="text-3xl">○</span
                                    ><b class="mt-8 text-base">{{
                                        account.phone
                                    }}</b
                                    ><span class="text-sm text-black/45"
                                        >Thông tin liên hệ</span
                                    >
                                </button>
                            </div>
                            <div class="mt-10 border-t border-black/15 pt-8">
                                <div class="flex items-center justify-between">
                                    <h3
                                        class="text-2xl font-black tracking-[-.04em]"
                                    >
                                        Đơn hàng gần đây
                                    </h3>
                                    <button
                                        class="text-sm font-bold underline underline-offset-4"
                                        @click="tab = 'orders'"
                                    >
                                        Xem tất cả
                                    </button>
                                </div>
                                <OrderCard
                                    v-if="orders[0]"
                                    class="mt-5"
                                    :order="orders[0]"
                                    :store="store"
                                    @cancel="openCancelModal"
                                    @repurchase="repurchaseOrder"
                                />
                                <p
                                    v-else
                                    class="mt-5 bg-white/50 p-8 text-center text-black/45"
                                >
                                    Bạn chưa có đơn hàng nào.
                                </p>
                            </div>
                        </div>

                        <div v-else-if="tab === 'orders'">
                            <SectionTitle
                                eyebrow="Đơn mua"
                                title="Lịch sử đơn hàng"
                                text="Theo dõi trạng thái và chi tiết các đơn đã đặt."
                            />
                            <div v-if="orders.length" class="mt-8 space-y-5">
                                <OrderCard
                                    v-for="order in orders"
                                    :key="order.code"
                                    :order="order"
                                    :store="store"
                                    @cancel="openCancelModal"
                                    @repurchase="repurchaseOrder"
                                />
                            </div>
                            <div
                                v-else
                                class="mt-8 bg-white/60 p-12 text-center"
                            >
                                <p class="text-black/45">Chưa có đơn hàng.</p>
                                <Link
                                    :href="`/shop/${store.slug}`"
                                    class="mt-5 inline-block rounded-full bg-black px-6 py-3 text-sm font-black text-white"
                                    >Mua sắm ngay</Link
                                >
                            </div>
                        </div>

                        <div v-else-if="tab === 'profile'">
                            <SectionTitle
                                eyebrow="Hồ sơ"
                                title="Thông tin cá nhân"
                                text="Quản lý thông tin dùng khi đặt và nhận hàng."
                            />
                            <form
                                class="mt-8 max-w-2xl space-y-5"
                                @submit.prevent="updateProfile"
                            >
                                <label class="field-label"
                                    >Họ và tên<input
                                        v-model.trim="profileForm.name"
                                        required
                                        class="field-input" /></label
                                ><label class="field-label"
                                    >Email<input
                                        :value="account.email"
                                        disabled
                                        class="field-input bg-black/5 text-black/40"
                                    /><small
                                        class="mt-2 block font-normal text-black/40"
                                        >Email đăng nhập không thể thay đổi tại
                                        đây.</small
                                    ></label
                                ><label class="field-label"
                                    >Số điện thoại<input
                                        v-model.trim="profileForm.phone"
                                        required
                                        class="field-input" /></label
                                ><button
                                    :disabled="saving"
                                    class="action-button"
                                >
                                    {{
                                        saving ? "Đang lưu..." : "Lưu thay đổi"
                                    }}
                                </button>
                            </form>
                        </div>

                        <div v-else-if="tab === 'addresses'">
                            <div
                                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
                            >
                                <SectionTitle
                                    eyebrow="Địa chỉ"
                                    title="Sổ địa chỉ"
                                    text="Chọn tỉnh, xã/phường và nhập địa chỉ chi tiết."
                                /><button
                                    class="rounded-full bg-black px-6 py-3 text-sm font-black text-white"
                                    @click="showAddressForm ? closeAddressForm() : openCreateAddress()"
                                >
                                    {{
                                        showAddressForm
                                            ? "Đóng"
                                            : "Thêm địa chỉ"
                                    }}
                                </button>
                            </div>
                            <form
                                v-if="showAddressForm"
                                class="mt-7 grid gap-4 border border-black/15 bg-white/60 p-6 sm:grid-cols-2"
                                @submit.prevent="saveAddress"
                            >
                                <input
                                    v-model="addressForm.label"
                                    required
                                    class="field-input mt-0"
                                    placeholder="Tên địa chỉ, ví dụ: Nhà riêng"
                                /><input
                                    v-model="addressForm.recipient_name"
                                    required
                                    class="field-input mt-0"
                                    placeholder="Tên người nhận"
                                /><input
                                    v-model="addressForm.phone"
                                    required
                                    class="field-input mt-0 sm:col-span-2"
                                    placeholder="Số điện thoại"
                                /><label
                                    class="text-xs font-black uppercase tracking-wider text-black/80"
                                    >Tỉnh/Thành phố<select
                                        v-model="addressForm.province_id"
                                        required
                                        class="field-input text-sm normal-case"
                                        @change="onProvinceChange"
                                    >
                                        <option value="">
                                            Chọn Tỉnh/Thành phố
                                        </option>
                                        <option
                                            v-for="province in provinces"
                                            :key="province.id"
                                            :value="province.id"
                                        >
                                            {{ province.name }}
                                        </option>
                                    </select></label
                                ><label
                                    class="text-xs font-black uppercase tracking-wider text-black/80"
                                    >Xã/Phường<select
                                        v-model="addressForm.ward_id"
                                        required
                                        :disabled="
                                            !addressForm.province_id ||
                                            loadingWards
                                        "
                                        class="field-input text-sm normal-case disabled:opacity-50"
                                    >
                                        <option value="">
                                            {{
                                                loadingWards
                                                    ? "Đang tải..."
                                                    : "Chọn Xã/Phường"
                                            }}
                                        </option>
                                        <option
                                            v-for="ward in wards"
                                            :key="ward.id"
                                            :value="ward.id"
                                        >
                                            {{ ward.name }}
                                        </option>
                                    </select></label
                                ><label
                                    class="text-xs font-black uppercase tracking-wider text-black/80 sm:col-span-2"
                                    >Địa chỉ chi tiết<textarea
                                        v-model.trim="
                                            addressForm.address_detail
                                        "
                                        required
                                        class="field-input text-sm normal-case"
                                        rows="3"
                                        placeholder="Số nhà, tên đường, tòa nhà, thôn/xóm..."
                                    ></textarea></label
                                ><label class="flex items-center gap-2 text-sm"
                                    ><input
                                        v-model="addressForm.is_default"
                                        type="checkbox"
                                    />
                                    Đặt làm địa chỉ mặc định</label
                                ><button
                                    class="action-button mt-0 sm:justify-self-end"
                                >
                                    {{ editingAddressId ? "Cập nhật địa chỉ" : "Lưu địa chỉ" }}
                                </button>
                            </form>
                            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                <article
                                    v-for="address in addresses"
                                    :key="address.id"
                                    class="border border-black/15 bg-white/45 p-6"
                                >
                                    <div class="flex justify-between gap-3">
                                        <div>
                                            <span
                                                v-if="address.is_default"
                                                class="rounded-full bg-[#d8ff43] px-2 py-1 text-[10px] font-black uppercase"
                                                >Mặc định</span
                                            >
                                            <h3 class="mt-3 text-xl font-black">
                                                {{ address.label }}
                                            </h3>
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="button" class="text-xs font-bold text-blue-700 underline" @click="editAddress(address)">Sửa</button>
                                            <button type="button" class="text-xs font-bold text-red-600 underline" @click="openDeleteAddressModal(address)">Xóa</button>
                                        </div>
                                    </div>
                                    <p class="mt-5 text-sm font-black text-black">
                                        {{ address.recipient_name }} ·
                                        {{ address.phone }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold leading-6 text-black"
                                    >
                                        {{ address.address }}
                                    </p>
                                </article>
                                <p
                                    v-if="!addresses.length"
                                    class="text-black/45"
                                >
                                    Bạn chưa lưu địa chỉ nào.
                                </p>
                            </div>
                        </div>

                        <div v-else>
                            <SectionTitle
                                eyebrow="Bảo mật"
                                title="Đổi mật khẩu"
                                text="Sử dụng mật khẩu mạnh và không dùng chung với dịch vụ khác."
                            />
                            <form
                                class="mt-8 max-w-2xl space-y-5"
                                @submit.prevent="updatePassword"
                            >
                                <label class="field-label"
                                    >Mật khẩu hiện tại<input
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        required
                                        class="field-input"
                                        autocomplete="current-password" /></label
                                ><label class="field-label"
                                    >Mật khẩu mới<input
                                        v-model="passwordForm.password"
                                        type="password"
                                        required
                                        minlength="8"
                                        class="field-input"
                                        autocomplete="new-password"
                                    /><small
                                        class="mt-2 block font-normal text-black/40"
                                        >Tối thiểu 8 ký tự, gồm chữ và
                                        số.</small
                                    ></label
                                ><label class="field-label"
                                    >Nhập lại mật khẩu mới<input
                                        v-model="
                                            passwordForm.password_confirmation
                                        "
                                        type="password"
                                        required
                                        class="field-input"
                                        autocomplete="new-password" /></label
                                ><button
                                    :disabled="saving"
                                    class="action-button"
                                >
                                    {{
                                        saving
                                            ? "Đang cập nhật..."
                                            : "Cập nhật mật khẩu"
                                    }}
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </template>
        </main>
        <ActionModal
            :open="actionModal.open"
            :title="actionModal.title"
            :message="actionModal.message"
            :confirm-text="actionModal.confirmText"
            :loading-text="actionModal.loadingText"
            :loading="actionModal.loading"
            :error="actionModal.error"
            :require-reason="actionModal.type === 'cancel-order'"
            :reason-options="actionModal.type === 'cancel-order' ? cancelReasonOptions : []"
            reason-placeholder="Ví dụ: Tôi muốn thay đổi sản phẩm..."
            @close="closeActionModal"
            @confirm="confirmModalAction"
        />
    </div>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import axios from "axios";
import { computed, defineComponent, h, nextTick, onMounted, reactive, ref } from "vue";
import CustomerAuthPanel from "@/components/Storefront/CustomerAuthPanel.vue";
import ActionModal from "@/components/Storefront/ActionModal.vue";
import { storefrontToast as toast } from "@/utils/storefrontToast";
import { useStorefrontCart } from "@/composables/useStorefrontCart";
import { useStorefrontNotifications } from "@/composables/useStorefrontNotifications";
import { useAutoApiRefresh } from "@/composables/useAutoApiRefresh";
import NotificationBadgeLink from "@/components/Storefront/NotificationBadgeLink.vue";
const props = defineProps({ store: { type: Object, required: true } }),
    base = `/shop/${props.store.slug}/account`;
const { add } = useStorefrontCart(props.store.slug);
const { start: startNotifications } = useStorefrontNotifications(props.store.slug);
const account = ref(null),
    orders = ref([]),
    addresses = ref([]),
    provinces = ref([]),
    wards = ref([]),
    loading = ref(true),
    loadingWards = ref(false),
    saving = ref(false),
    error = ref(""),
    notice = ref(""),
    tab = ref("overview"),
    showAddressForm = ref(false),
    editingAddressId = ref(null);
const actionModal = reactive({
    open: false, type: "", target: null, title: "", message: "",
    confirmText: "Xác nhận", loadingText: "Đang xử lý...", loading: false, error: "",
});
const cancelReasonOptions = [
    { value: "change_product", label: "Muốn thay đổi sản phẩm" },
    { value: "change_address", label: "Muốn thay đổi địa chỉ nhận hàng" },
    { value: "change_payment", label: "Muốn thay đổi phương thức thanh toán" },
    { value: "no_longer_needed", label: "Không còn nhu cầu mua hàng" },
    { value: "ordered_by_mistake", label: "Đặt nhầm đơn hàng" },
    { value: "other", label: "Lý do khác" },
];
const profileForm = reactive({ name: "", phone: "" }),
    addressForm = reactive({
        label: "Nhà riêng",
        recipient_name: "",
        phone: "",
        province_id: "",
        ward_id: "",
        address_detail: "",
        is_default: false,
    }),
    passwordForm = reactive({
        current_password: "",
        password: "",
        password_confirmation: "",
    });
const navItems = [
    { key: "overview", icon: "⌂", label: "Tổng quan" },
    { key: "orders", icon: "◫", label: "Đơn mua" },
    { key: "profile", icon: "○", label: "Hồ sơ" },
    { key: "addresses", icon: "⌖", label: "Địa chỉ" },
    { key: "security", icon: "◇", label: "Bảo mật" },
];
const firstName = computed(
        () => account.value?.name?.trim().split(/\s+/).pop() || "bạn",
    ),
    initials = computed(
        () =>
            account.value?.name
                ?.trim()
                .split(/\s+/)
                .slice(-2)
                .map((x) => x[0])
                .join("")
                .toUpperCase() || "KH",
    );
const message = (e) =>
    Object.values(e.response?.data?.errors || {}).flat()[0] ||
    e.response?.data?.message ||
    "Có lỗi xảy ra.";
async function refresh() {
    const { data } = await axios.get(`${base}/me`);
    account.value = data.account;
    if (account.value) {
        Object.assign(profileForm, {
            name: account.value.name,
            phone: account.value.phone,
        });
        const [o, a] = await Promise.all([
            axios.get(`${base}/orders`),
            axios.get(`${base}/addresses`),
        ]);
        orders.value = o.data.data;
        addresses.value = a.data.addresses;
    }
}
async function logout() {
    await axios.post(`${base}/logout`);
    account.value = null;
    orders.value = [];
    addresses.value = [];
    tab.value = "overview";
}
async function updateProfile() {
    if (!validPhone(profileForm.phone))
        return toast.warning("Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.");
    await act(async () => {
        const { data } = await axios.put(`${base}/profile`, profileForm);
        account.value = data.account;
        notice.value = data.message;
        toast.success(data.message);
    });
}
async function updatePassword() {
    if (passwordForm.password !== passwordForm.password_confirmation)
        return toast.warning("Mật khẩu nhập lại chưa trùng khớp.");
    await act(async () => {
        const { data } = await axios.put(`${base}/password`, passwordForm);
        Object.assign(passwordForm, {
            current_password: "",
            password: "",
            password_confirmation: "",
        });
        notice.value = data.message;
        toast.success(data.message);
    });
}
async function loadProvinces() {
    const { data } = await axios.get("/shop/locations/provinces");
    provinces.value = data;
}
async function onProvinceChange() {
    addressForm.ward_id = "";
    await loadWards(addressForm.province_id);
}
async function loadWards(provinceId) {
    wards.value = [];
    if (!provinceId) return;
    loadingWards.value = true;
    try {
        const { data } = await axios.get(
            `/shop/locations/provinces/${provinceId}/wards`,
        );
        wards.value = data;
    } finally {
        loadingWards.value = false;
    }
}
function resetAddressForm() {
    Object.assign(addressForm, {
        label: "Nhà riêng",
        recipient_name: account.value?.name || "",
        phone: account.value?.phone || "",
        province_id: "",
        ward_id: "",
        address_detail: "",
        is_default: false,
    });
    wards.value = [];
    editingAddressId.value = null;
}
function openCreateAddress() {
    resetAddressForm();
    showAddressForm.value = true;
}
function closeAddressForm() {
    showAddressForm.value = false;
    resetAddressForm();
}
async function editAddress(address) {
    const provinceId = Number(address.province_id);
    const wardId = Number(address.ward_id);
    editingAddressId.value = address.id;
    Object.assign(addressForm, {
        label: address.label,
        recipient_name: address.recipient_name,
        phone: address.phone,
        province_id: provinceId,
        ward_id: "",
        address_detail: address.address_detail,
        is_default: Boolean(address.is_default),
    });
    showAddressForm.value = true;
    await loadWards(provinceId);
    await nextTick();
    addressForm.ward_id = wardId;
}
async function saveAddress() {
    if (!validPhone(addressForm.phone))
        return toast.warning("Số điện thoại người nhận phải gồm 10 chữ số và đúng đầu số di động Việt Nam.");
    await act(async () => {
        const updating = Boolean(editingAddressId.value);
        if (updating) {
            await axios.put(`${base}/addresses/${editingAddressId.value}`, addressForm);
        } else {
            await axios.post(`${base}/addresses`, addressForm);
        }
        closeAddressForm();
        await refresh();
        notice.value = updating ? "Đã cập nhật địa chỉ nhận hàng." : "Đã thêm địa chỉ nhận hàng.";
        toast.success(notice.value);
    });
}
function openDeleteAddressModal(address) {
    Object.assign(actionModal, {
        open: true, type: "delete-address", target: address, error: "",
        title: "Xóa địa chỉ đã lưu",
        message: `Bạn có chắc muốn xóa địa chỉ “${address.label}”? Thao tác này không thể hoàn tác.`,
        confirmText: "Xóa địa chỉ", loadingText: "Đang xóa...",
    });
}
function openCancelModal(order) {
    Object.assign(actionModal, {
        open: true, type: "cancel-order", target: order, error: "",
        title: "Hủy đơn hàng",
        message: `Bạn đang yêu cầu hủy đơn ${order.code}. Thao tác này không thể hoàn tác.`,
        confirmText: "Xác nhận hủy đơn", loadingText: "Đang hủy đơn...",
    });
}
function closeActionModal() {
    if (actionModal.loading) return;
    actionModal.open = false;
    actionModal.error = "";
}
async function confirmModalAction(reason) {
    actionModal.loading = true;
    actionModal.error = "";
    try {
        if (actionModal.type === "delete-address") {
            await axios.delete(`${base}/addresses/${actionModal.target.id}`);
            await refresh();
            notice.value = "Đã xóa địa chỉ.";
            toast.success(notice.value);
        } else if (actionModal.type === "cancel-order") {
            await axios.post(`${base}/orders/${actionModal.target.code}/cancel`, { reason });
            await refresh();
            notice.value = "Đã hủy đơn hàng.";
            toast.success(notice.value);
        }
        closeActionModal();
    } catch (e) {
        actionModal.error = message(e);
    } finally {
        actionModal.loading = false;
        if (!actionModal.error) actionModal.open = false;
    }
}
function repurchaseOrder(order) {
    let added = 0;
    let unavailable = 0;
    let reduced = 0;
    for (const item of order.items || []) {
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
    if (!added) return toast.warning("Các sản phẩm trong đơn hiện không còn khả dụng để mua lại.");
    if (unavailable || reduced) toast.warning("Một số sản phẩm đã hết hàng hoặc số lượng được điều chỉnh theo tồn kho hiện tại.");
    else toast.success("Đã thêm lại toàn bộ sản phẩm vào giỏ hàng.");
    router.visit(`/shop/${props.store.slug}/cart`);
}
async function act(callback) {
    saving.value = true;
    error.value = "";
    notice.value = "";
    try {
        await callback();
    } catch (e) {
        error.value = message(e);
        toast.error(error.value);
    } finally {
        saving.value = false;
    }
}
const validPhone = (value) =>
    /^0[35789][0-9]{8}$/.test(String(value || "").trim());
const SectionTitle = defineComponent({
    props: ["eyebrow", "title", "text"],
    setup: (p) => () =>
        h("div", [
            h(
                "p",
                {
                    class: "text-xs font-black uppercase tracking-[.22em] text-black/40",
                },
                p.eyebrow,
            ),
            h(
                "h2",
                {
                    class: "mt-3 text-4xl font-black tracking-[-.05em] md:text-5xl",
                },
                p.title,
            ),
            h("p", { class: "mt-3 text-sm text-black/45" }, p.text),
        ]),
});
const OrderCard = defineComponent({
    props: ["order", "store"],
    emits: ["cancel", "repurchase"],
    setup: (p, { emit }) =>
        () =>
            h("article", { class: "border border-black/15 bg-white/55 p-6" }, [
                h(
                    "div",
                    {
                        class: "flex flex-wrap items-start justify-between gap-4",
                    },
                    [
                        h("div", [
                            h(
                                "p",
                                { class: "text-xs text-black/40" },
                                p.order.date,
                            ),
                            h(
                                "h3",
                                { class: "mt-1 text-xl font-black" },
                                p.order.code,
                            ),
                        ]),
                        h("div", { class: "text-right" }, [
                            h(
                                "p",
                                {
                                    class: [
                                        "inline-flex rounded-full px-3 py-1 text-xs font-black",
                                        statusClass(p.order.status),
                                    ],
                                },
                                p.order.status_label || "Đang xử lý",
                            ),
                            h(
                                "p",
                                { class: "mt-2 text-xl font-black" },
                                `${new Intl.NumberFormat("vi-VN").format(p.order.total)} ${p.store.currency.symbol}`,
                            ),
                        ]),
                    ],
                ),
                h("div", { class: "mt-5 flex flex-wrap items-center gap-4 border-t border-black/10 pt-4" }, [
                    h(
                        Link,
                        {
                            href: `/shop/${p.store.slug}/my-account/orders/${p.order.code}`,
                            class: "rounded-full bg-black px-5 py-2.5 text-xs font-black text-white transition hover:bg-black/75",
                        },
                        () => "Xem chi tiết đơn hàng",
                    ),
                    p.order.cancelable
                        ? h(
                              "button",
                              {
                                  type: "button",
                                  class: "text-xs font-bold text-red-700 underline",
                                  onClick: () => emit("cancel", p.order),
                              },
                              "Hủy đơn hàng",
                          )
                        : null,
                    p.order.repurchasable
                        ? h(
                              "button",
                              {
                                  type: "button",
                                  class: "rounded-full border border-black/20 px-5 py-2.5 text-xs font-black transition hover:bg-[#d8ff43]",
                                  onClick: () => emit("repurchase", p.order),
                              },
                              "Mua lại",
                          )
                        : null,
                ]),
            ]),
});
const statusClass = (status) =>
    ({
        pending: "bg-amber-100 text-amber-800",
        approved: "bg-blue-100 text-blue-800",
        confirmed: "bg-blue-100 text-blue-800",
        partial: "bg-violet-100 text-violet-800",
        shipping: "bg-violet-100 text-violet-800",
        completed: "bg-emerald-100 text-emerald-800",
        delivered: "bg-emerald-100 text-emerald-800",
        cancelled: "bg-red-100 text-red-800",
        returned: "bg-orange-100 text-orange-800",
    })[status] || "bg-neutral-200 text-neutral-700";
onMounted(async () => {
    try {
        await Promise.all([refresh(), loadProvinces()]);
    } finally {
        loading.value = false;
    }
    if (account.value) startNotifications();
});
useAutoApiRefresh(async () => {
    if (account.value) await refresh();
}, 20000);
</script>

<style scoped>
.account-nav {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.9rem 1rem;
    border-radius: 0.75rem;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 700;
    color: rgb(0 0 0 / 55%);
    transition: 0.2s;
}
.account-nav:hover,
.account-nav.active {
    background: #111;
    color: #fff;
}
.summary-card {
    display: flex;
    min-height: 190px;
    flex-direction: column;
    align-items: flex-start;
    border: 1px solid rgb(0 0 0 / 15%);
    background: rgb(255 255 255 / 45%);
    padding: 1.5rem;
    text-align: left;
    transition: 0.2s;
}
.summary-card:hover {
    background: #fff;
    transform: translateY(-2px);
}
.field-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 800;
}
.field-input {
    display: block;
    width: 100%;
    margin-top: 0.5rem;
    border: 1px solid rgb(0 0 0 / 22%);
    border-radius: 0.75rem;
    background: rgb(255 255 255 / 65%);
    padding: 0.9rem 1rem;
    outline: none;
}
.field-input:focus {
    border-color: #111;
    box-shadow: 0 0 0 2px rgb(0 0 0 / 8%);
}
.action-button {
    display: inline-flex;
    border-radius: 999px;
    background: #111;
    padding: 0.9rem 1.75rem;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 900;
}
.action-button:hover {
    background: #d8ff43;
    color: #111;
}
.action-button:disabled {
    opacity: 0.5;
}
</style>
