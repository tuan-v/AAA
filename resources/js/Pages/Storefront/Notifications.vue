<template>
    <Head :title="`Thông báo - ${store.name}`" />
    <div class="min-h-screen bg-[#f6f7f9] text-slate-950">
        <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-20 max-w-6xl items-center gap-4 px-5 lg:px-8">
                <Link :href="`/shop/${store.slug}`" class="text-xl font-black tracking-[-.04em]">
                    {{ store.name }}
                </Link>
                <span class="hidden text-slate-300 sm:block">/</span>
                <span class="hidden text-sm font-semibold text-slate-500 sm:block">Thông báo</span>
                <Link
                    :href="`/shop/${store.slug}/my-account`"
                    class="ml-auto rounded-full border border-slate-200 px-4 py-2.5 text-sm font-bold transition hover:border-slate-900"
                >
                    Tài khoản của tôi
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-5 py-10 lg:px-8 lg:py-14">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[.2em] text-indigo-600">Trung tâm thông báo</p>
                    <h1 class="mt-2 text-4xl font-black tracking-[-.05em] sm:text-5xl">Thông báo của bạn</h1>
                    <p class="mt-3 text-sm text-slate-500">Theo dõi cập nhật đơn hàng và các thông tin quan trọng từ cửa hàng.</p>
                </div>
                <button
                    v-if="unreadCount"
                    class="rounded-full bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-600"
                    @click="markAllRead"
                >
                    Đánh dấu tất cả đã đọc
                </button>
            </div>

            <div class="mt-8 flex gap-2 overflow-x-auto border-b border-slate-200 pb-4">
                <button
                    v-for="item in filters"
                    :key="item.value"
                    class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-bold transition"
                    :class="status === item.value ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:text-slate-950'"
                    @click="changeStatus(item.value)"
                >
                    {{ item.label }}
                    <span v-if="item.value === 'unread' && unreadCount" class="ml-1 opacity-80">({{ unreadCount }})</span>
                </button>
            </div>

            <div v-if="loading" class="mt-8 space-y-3">
                <div v-for="i in 4" :key="i" class="h-28 animate-pulse rounded-2xl bg-white" />
            </div>

            <div v-else-if="notifications.length" class="mt-8 space-y-3">
                <article
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="group relative overflow-hidden rounded-2xl border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-6"
                    :class="notification.read_at ? 'border-slate-200' : 'border-indigo-200 ring-1 ring-indigo-100'"
                >
                    <span v-if="!notification.read_at" class="absolute inset-y-0 left-0 w-1 bg-indigo-600" />
                    <div class="flex gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl"
                            :class="iconClass(notification)"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                                <path d="M10 21h4" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="font-black text-slate-900">{{ notification.title }}</h2>
                                        <span v-if="!notification.read_at" class="h-2 w-2 rounded-full bg-indigo-600" title="Chưa đọc" />
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ notification.message }}</p>
                                </div>
                                <time class="shrink-0 text-xs font-medium text-slate-400">{{ formatDate(notification.created_at) }}</time>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-4">
                                <Link
                                    v-if="notification.url_link"
                                    :href="notification.url_link"
                                    class="text-sm font-black text-indigo-600 hover:text-indigo-800"
                                    @click="markRead(notification)"
                                >
                                    Xem chi tiết →
                                </Link>
                                <button
                                    v-if="!notification.read_at"
                                    class="text-sm font-bold text-slate-500 hover:text-slate-950"
                                    @click="markRead(notification)"
                                >
                                    Đánh dấu đã đọc
                                </button>
                                <button
                                    class="text-sm font-bold text-slate-400 hover:text-red-600"
                                    @click="askDelete(notification)"
                                >
                                    Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="mt-8 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" /><path d="M10 21h4" />
                    </svg>
                </div>
                <h2 class="mt-5 text-xl font-black">Không có thông báo</h2>
                <p class="mt-2 text-sm text-slate-500">Các cập nhật mới từ cửa hàng sẽ xuất hiện tại đây.</p>
            </div>

            <div v-if="pagination.last_page > 1" class="mt-8 flex items-center justify-center gap-3">
                <button class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold transition hover:border-slate-900 disabled:cursor-not-allowed disabled:opacity-40" :disabled="pagination.current_page === 1" @click="load(pagination.current_page - 1)">← Trước</button>
                <span class="text-sm font-semibold text-slate-500">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
                <button class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold transition hover:border-slate-900 disabled:cursor-not-allowed disabled:opacity-40" :disabled="pagination.current_page === pagination.last_page" @click="load(pagination.current_page + 1)">Sau →</button>
            </div>
        </main>

        <ActionModal
            :open="deleteModal.open"
            title="Xóa thông báo"
            message="Bạn có chắc muốn xóa thông báo này? Thao tác này không thể hoàn tác."
            confirm-text="Xóa thông báo"
            loading-text="Đang xóa..."
            :loading="deleteModal.loading"
            :error="deleteModal.error"
            @close="closeDeleteModal"
            @confirm="deleteNotification"
        />
    </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import axios from "axios";
import { onMounted, reactive, ref } from "vue";
import ActionModal from "@/components/Storefront/ActionModal.vue";
import { storefrontToast as toast } from "@/utils/storefrontToast";
import { useAutoApiRefresh } from "@/composables/useAutoApiRefresh";

const props = defineProps({ store: { type: Object, required: true } });
const base = `/shop/${props.store.slug}/account`;
const loading = ref(true);
const status = ref("all");
const notifications = ref([]);
const unreadCount = ref(0);
const pagination = reactive({ current_page: 1, last_page: 1 });
const filters = [
    { value: "all", label: "Tất cả" },
    { value: "unread", label: "Chưa đọc" },
    { value: "read", label: "Đã đọc" },
];
const deleteModal = reactive({ open: false, loading: false, error: "", target: null });

async function load(page = 1, options = {}) {
    if (!options.silent) loading.value = true;
    try {
        const { data } = await axios.get(`${base}/notification-history`, { params: { status: status.value, page } });
        notifications.value = data.notifications.data;
        unreadCount.value = data.unread_count;
        pagination.current_page = data.notifications.current_page;
        pagination.last_page = data.notifications.last_page;
    } catch (error) {
        if (error.response?.status === 401) window.location.href = `/shop/${props.store.slug}/my-account`;
        else toast.error(error.response?.data?.message || "Không thể tải danh sách thông báo.");
    } finally {
        if (!options.silent) loading.value = false;
    }
}

function changeStatus(value) {
    status.value = value;
    load(1);
}

async function markRead(notification) {
    if (notification.read_at) return;
    await axios.post(`${base}/notifications/${notification.id}/read`);
    notification.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
    window.dispatchEvent(new CustomEvent("storefront-notifications-changed"));
    if (status.value === "unread") notifications.value = notifications.value.filter((item) => item.id !== notification.id);
}

async function markAllRead() {
    await axios.post(`${base}/notifications/read-all`);
    unreadCount.value = 0;
    window.dispatchEvent(new CustomEvent("storefront-notifications-changed"));
    if (status.value === "unread") notifications.value = [];
    else notifications.value.forEach((item) => { item.read_at ||= new Date().toISOString(); });
    toast.success("Đã đọc tất cả thông báo.");
}

function askDelete(notification) {
    Object.assign(deleteModal, { open: true, loading: false, error: "", target: notification });
}
function closeDeleteModal() {
    if (!deleteModal.loading) deleteModal.open = false;
}
async function deleteNotification() {
    deleteModal.loading = true;
    deleteModal.error = "";
    try {
        const wasUnread = !deleteModal.target.read_at;
        await axios.delete(`${base}/notifications/${deleteModal.target.id}`);
        notifications.value = notifications.value.filter((item) => item.id !== deleteModal.target.id);
        if (wasUnread) unreadCount.value = Math.max(0, unreadCount.value - 1);
        window.dispatchEvent(new CustomEvent("storefront-notifications-changed"));
        deleteModal.open = false;
        toast.success("Đã xóa thông báo.");
    } catch (error) {
        deleteModal.error = error.response?.data?.message || "Không thể xóa thông báo.";
    } finally {
        deleteModal.loading = false;
    }
}

function formatDate(value) {
    if (!value) return "";
    return new Intl.DateTimeFormat("vi-VN", { dateStyle: "short", timeStyle: "short" }).format(new Date(value));
}
function iconClass(notification) {
    const type = notification.data?.toast_type;
    if (type === "success") return "bg-emerald-50 text-emerald-600";
    if (type === "error") return "bg-red-50 text-red-600";
    if (type === "warning") return "bg-amber-50 text-amber-600";
    return "bg-indigo-50 text-indigo-600";
}

onMounted(() => load());
useAutoApiRefresh(({ silent }) => load(pagination.current_page, { silent }), 15000);
</script>
