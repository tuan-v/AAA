<template>
    <div class="w-full max-w-6xl overflow-hidden rounded-3xl bg-slate-50 shadow-2xl">
        <div class="relative bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-700 px-6 py-7 text-white sm:px-8">
            <button type="button" class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-xl transition hover:bg-white/20" aria-label="Đóng" @click="emit('close')">×</button>

            <div v-if="loading" class="flex min-h-36 items-center justify-center gap-3 text-indigo-100">
                <span class="h-6 w-6 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                Đang tải hồ sơ nhân sự...
            </div>

            <div v-else-if="user" class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-3xl bg-white/15 text-3xl font-bold ring-1 ring-white/20">
                    <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="h-full w-full object-cover" />
                    <span v-else>{{ initials(user.name) }}</span>
                </div>
                <div class="min-w-0 pr-12">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="truncate text-2xl font-bold sm:text-3xl">{{ user.name }}</h2>
                        <span class="rounded-full px-3 py-1 text-xs font-bold" :class="statusClass(user.status)">{{ statusText(user.status) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-indigo-100">{{ user.position_record?.name || 'Chưa có chức vụ' }} · {{ user.department_record?.name || 'Chưa có phòng ban' }}</p>
                    <p class="mt-1 text-sm text-indigo-200">{{ user.company?.name || 'Chưa xác định công ty' }}</p>
                </div>
            </div>
        </div>

        <div v-if="error" class="m-6 rounded-2xl border border-red-200 bg-red-50 p-6 text-center text-red-700">
            <p class="font-semibold">Không thể tải chi tiết người dùng</p>
            <p class="mt-1 text-sm">{{ error }}</p>
            <button class="mt-4 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white" @click="loadUser">Thử lại</button>
        </div>

        <div v-else-if="user && !loading" class="max-h-[70vh] overflow-y-auto p-5 sm:p-7">
            <div class="grid gap-5 lg:grid-cols-3">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
                    <h3 class="text-base font-bold text-slate-900">Thông tin tài khoản</h3>
                    <dl class="mt-4 divide-y divide-slate-100">
                        <div v-for="item in profileItems" :key="item.label" class="py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ item.label }}</dt>
                            <dd class="mt-1 break-words text-sm font-medium text-slate-800">{{ item.value || 'Chưa cập nhật' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Vai trò</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span v-for="role in user.roles" :key="role.id" class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ role.name }}</span>
                            <span v-if="!user.roles?.length" class="text-sm text-slate-500">Chưa phân quyền</span>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Lịch sử hoạt động</h3>
                            <p class="mt-1 text-sm text-slate-500">Các thao tác gần nhất do tài khoản này thực hiện</p>
                        </div>
                        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ user.activity_count || 0 }} hoạt động</span>
                    </div>

                    <div v-if="user.activities?.length" class="divide-y divide-slate-100">
                        <article v-for="log in user.activities" :key="log.id" class="flex gap-4 p-5 transition hover:bg-slate-50">
                            <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold" :class="actionClass(log.action)">{{ actionIcon(log.action) }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-slate-900">{{ actionText(log.action) }}</span>
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ modelText(log.model_type) }} #{{ log.model_id }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">{{ log.description || activityDescription(log) }}</p>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                                    <span>{{ formatDate(log.created_at) }}</span>
                                    <span v-if="log.ip_address">IP: {{ log.ip_address }}</span>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div v-else class="flex min-h-56 flex-col items-center justify-center p-8 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">⌛</div>
                        <p class="mt-4 font-semibold text-slate-700">Chưa có lịch sử hoạt động</p>
                        <p class="mt-1 text-sm text-slate-500">Các thao tác mới của tài khoản sẽ xuất hiện tại đây.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { useRealtimeRefresh } from '@/composables/useRealtimeRefresh';

const props = defineProps({ userId: { type: Number, required: true } });
const emit = defineEmits(['close']);
const user = ref(null);
const loading = ref(false);
const error = ref('');

const profileItems = computed(() => [
    { label: 'Tên đăng nhập', value: user.value?.username },
    { label: 'Email', value: user.value?.email },
    { label: 'Số điện thoại', value: user.value?.phone },
    { label: 'Phòng ban', value: user.value?.department_record?.name },
    { label: 'Chức vụ', value: user.value?.position_record?.name },
    { label: 'Người tạo', value: user.value?.creator?.name },
    { label: 'Đăng nhập gần nhất', value: formatDate(user.value?.last_login_at) },
    { label: 'IP đăng nhập gần nhất', value: user.value?.last_login_ip },
    { label: 'Ngày tạo tài khoản', value: formatDate(user.value?.created_at) },
]);

async function loadUser() {
    if (!props.userId) return;
    loading.value = true;
    error.value = '';
    try {
        const response = await axios.get(`/api/users/user/${props.userId}`);
        user.value = response.data;
    } catch (exception) {
        user.value = null;
        error.value = exception.response?.status === 404
            ? 'Không tìm thấy người dùng hoặc bạn không có quyền xem người dùng này.'
            : (exception.response?.data?.message || 'Đã có lỗi xảy ra, vui lòng thử lại.');
    } finally {
        loading.value = false;
    }
}

useRealtimeRefresh(loadUser);
watch(() => props.userId, loadUser, { immediate: true });

function initials(name) {
    return String(name || 'U').trim().split(/\s+/).slice(-2).map(part => part[0]).join('').toUpperCase();
}
function formatDate(value) {
    if (!value) return null;
    return new Intl.DateTimeFormat('vi-VN', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date(value));
}
function statusText(status) {
    return ({ active: 'Đang hoạt động', inactive: 'Ngừng hoạt động', blocked: 'Đã khóa', pending: 'Chờ kích hoạt' })[status] || 'Chưa xác định';
}
function statusClass(status) {
    return ({ active: 'bg-emerald-400/20 text-emerald-100', inactive: 'bg-slate-400/20 text-slate-100', blocked: 'bg-red-400/20 text-red-100', pending: 'bg-amber-400/20 text-amber-100' })[status] || 'bg-white/10 text-white';
}
function actionText(action) {
    return ({ them: 'Tạo mới', create: 'Tạo mới', sua: 'Cập nhật', update: 'Cập nhật', xoa: 'Xóa', delete: 'Xóa', xem: 'Xem dữ liệu', xem_chi_tiet: 'Xem chi tiết', duyet: 'Phê duyệt', approve: 'Phê duyệt', tu_choi: 'Từ chối', reject: 'Từ chối', khoa: 'Khóa', lock: 'Khóa', mo_khoa: 'Mở khóa', unlock: 'Mở khóa', huy: 'Hủy' })[action] || action || 'Thao tác';
}
function actionIcon(action) {
    if (['them', 'create'].includes(action)) return '+';
    if (['xoa', 'delete', 'tu_choi', 'reject'].includes(action)) return '×';
    if (['duyet', 'approve'].includes(action)) return '✓';
    if (['xem', 'xem_chi_tiet'].includes(action)) return '◉';
    return '↻';
}
function actionClass(action) {
    if (['them', 'create', 'duyet', 'approve'].includes(action)) return 'bg-emerald-100 text-emerald-700';
    if (['xoa', 'delete', 'tu_choi', 'reject'].includes(action)) return 'bg-red-100 text-red-700';
    if (['xem', 'xem_chi_tiet'].includes(action)) return 'bg-sky-100 text-sky-700';
    return 'bg-indigo-100 text-indigo-700';
}
function modelText(type) {
    const name = String(type || '').split('\\').pop();
    return ({ User: 'Nhân sự', Role: 'Vai trò', Permission: 'Quyền', Warehouse: 'Kho', WarehouseSlip: 'Phiếu kho', Product: 'Sản phẩm', PurchaseOrder: 'Đơn mua', SalesOrder: 'Đơn bán', Customer: 'Khách hàng', Supplier: 'Nhà cung cấp', Transaction: 'Giao dịch' })[name] || name || 'Dữ liệu';
}
function activityDescription(log) {
    return `${actionText(log.action)} ${modelText(log.model_type).toLowerCase()}`;
}
</script>
