<template>
    <Head title="Phòng ban" />
    <AdminLayout>
        <PageBreadcrumb title="Phòng ban" :items="[{ text: 'Phòng ban', link: null }]" />

        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Danh sách phòng ban</h2>
                <p class="mt-1 text-sm text-gray-500">Quản lý cơ cấu phòng ban và trưởng phòng.</p>
            </div>
            <button v-if="can('nhan_su.them')" class="rounded-lg bg-blue-600 px-4 py-2 text-white" @click="openCreate">
                + Thêm phòng ban
            </button>
        </div>

        <div class="mb-5 flex flex-wrap gap-3 rounded-xl border bg-white p-4 shadow-sm">
            <input v-model="filters.search" class="rounded-lg border px-3 py-2" placeholder="Tìm mã hoặc tên phòng ban..." @input="debouncedLoad" />
            <select v-model="filters.status" class="rounded-lg border px-3 py-2" @change="load(1)">
                <option value="">Tất cả trạng thái</option>
                <option value="active">Đang hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
            </select>
        </div>

        <DataTable :columns="columns" :data="departments.data" :showIndex="true" :actions="actions"
            :indexOffset="(departments.current_page - 1) * departments.per_page" emptyMessage="Chưa có phòng ban" />
        <Pagination :totalItems="departments.total" :itemsPerPage="departments.per_page"
            :currentPage="departments.current_page" :doingShow="departments.data.length" @page-change="load" />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <form class="asfy-modal-scroll max-h-[90vh] w-full max-w-xl space-y-4 overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" @submit.prevent="save">
                <div class="border-b pb-4">
                    <h3 class="text-xl font-bold">{{ selected ? 'Cập nhật phòng ban' : 'Thêm phòng ban' }}</h3>
                </div>
                <div class="grid gap-4" :class="selected ? 'sm:grid-cols-2' : ''">
                    <label v-if="selected" class="text-sm font-medium">Mã phòng ban
                        <input :value="selected.code" disabled class="mt-1 w-full rounded-lg border bg-gray-100 px-3 py-2 text-gray-500" />
                    </label>
                    <label class="text-sm font-medium">Tên phòng ban <span class="text-red-500">*</span>
                        <input v-model.trim="form.name" required class="mt-1 w-full rounded-lg border px-3 py-2" />
                    </label>
                </div>
                <label class="block text-sm font-medium">Trưởng phòng
                    <select v-model="form.manager_id" class="mt-1 w-full rounded-lg border px-3 py-2">
                        <option value="">Chưa chỉ định</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} — {{ user.email }}</option>
                    </select>
                </label>
                <label class="block text-sm font-medium">Mô tả
                    <textarea v-model.trim="form.description" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2"></textarea>
                </label>
                <label class="block text-sm font-medium">Trạng thái
                    <select v-model="form.status" class="mt-1 w-full rounded-lg border px-3 py-2">
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Ngừng hoạt động</option>
                    </select>
                </label>
                <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
                <div class="flex justify-end gap-2 border-t pt-4">
                    <button type="button" class="rounded-lg border px-4 py-2" @click="showModal = false">Hủy</button>
                    <button :disabled="saving" class="rounded-lg bg-blue-600 px-4 py-2 text-white disabled:opacity-50">{{ saving ? 'Đang lưu...' : 'Lưu' }}</button>
                </div>
            </form>
        </template>
    </Modal>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed, h, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { toast } from 'vue3-toastify';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import DataTable from '@/components/DataTable.vue';
import Pagination from '@/components/Pagination.vue';
import Modal from '@/components/Modal.vue';
import EditButtonIcon from '@/icons/EditButtonIcon.vue';
import DeleteIcon from '@/icons/DeleteIcon.vue';
import { getValidationMessage } from '@/config/helpers';

const permissions = usePage().props.auth.permissions || [];
const can = permission => permissions.includes(permission);
const departments = ref({ data: [], total: 0, per_page: 10, current_page: 1 });
const users = ref([]);
const filters = reactive({ search: '', status: '' });
const showModal = ref(false);
const selected = ref(null);
const saving = ref(false);
const errorMessage = ref('');
const form = reactive({ name: '', description: '', status: 'active', manager_id: '' });

const columns = [
    { label: 'Mã', key: 'code' },
    { label: 'Tên phòng ban', key: 'name' },
    { label: 'Trưởng phòng', render: row => h('span', row.manager?.name || 'Chưa chỉ định') },
    { label: 'Số nhân sự', render: row => h('span', { class: 'font-semibold text-blue-600' }, String(row.users_count || 0)) },
    { label: 'Mô tả', render: row => h('span', row.description || '-') },
    { label: 'Trạng thái', render: row => h('span', { class: ['rounded-full px-2.5 py-1 text-xs font-semibold', row.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'] }, row.status === 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động') },
];
const actions = computed(() => [
    { icon: EditButtonIcon, type: 'edit', hidden: () => !can('nhan_su.sua'), onClick: openEdit },
    { icon: DeleteIcon, type: 'delete', hidden: () => !can('nhan_su.xoa'), onClick: remove },
]);

async function load(page = 1) {
    const { data } = await axios.get('/api/departments', { params: { ...filters, page } });
    departments.value = data;
}
const debouncedLoad = debounce(() => load(1), 300);
function resetForm(department = null) {
    selected.value = department;
    Object.assign(form, { name: department?.name || '', description: department?.description || '', status: department?.status || 'active', manager_id: department?.manager_id || '' });
    errorMessage.value = '';
}
function openCreate() { resetForm(); showModal.value = true; }
function openEdit(department) { resetForm(department); showModal.value = true; }
async function save() {
    saving.value = true; errorMessage.value = '';
    try {
        if (selected.value) await axios.put(`/api/departments/${selected.value.id}`, form);
        else await axios.post('/api/departments', form);
        toast.success(selected.value ? 'Đã cập nhật phòng ban' : 'Đã thêm phòng ban');
        showModal.value = false; await load(departments.value.current_page);
    } catch (error) { errorMessage.value = getValidationMessage(error); }
    finally { saving.value = false; }
}
async function remove(department) {
    if (!confirm(`Xóa phòng ban “${department.name}”?`)) return;
    try { await axios.delete(`/api/departments/${department.id}`); toast.success('Đã xóa phòng ban'); await load(departments.value.current_page); }
    catch (error) { toast.error(getValidationMessage(error)); }
}
onMounted(async () => {
    const { data } = await axios.get('/api/users/user', { params: { per_page: 100 } });
    users.value = data.data || [];
    await load();
});
</script>
