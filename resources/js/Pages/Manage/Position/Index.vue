<template>
    <Head title="Chức vụ" /><AdminLayout>
        <PageBreadcrumb title="Chức vụ" :items="[{ text: 'Chức vụ', link: null }]" />
        <div class="mb-5 flex items-center justify-between"><div><h2 class="text-2xl font-bold">Danh sách chức vụ</h2><p class="text-sm text-gray-500">Quản lý chức vụ theo từng phòng ban.</p></div><button v-if="can('nhan_su.them')" class="rounded-lg bg-blue-600 px-4 py-2 text-white" @click="openCreate">+ Thêm chức vụ</button></div>
        <div class="mb-5 flex flex-wrap gap-3 rounded-xl border bg-white p-4"><input v-model="filters.search" class="rounded-lg border px-3 py-2" placeholder="Tìm mã hoặc tên..." @input="debouncedLoad" /><select v-model="filters.department_id" class="rounded-lg border px-3 py-2" @change="load(1)"><option value="">Tất cả phòng ban</option><option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option></select><select v-model="filters.status" class="rounded-lg border px-3 py-2" @change="load(1)"><option value="">Tất cả trạng thái</option><option value="active">Đang hoạt động</option><option value="inactive">Ngừng hoạt động</option></select></div>
        <DataTable :columns="columns" :data="positions.data" :showIndex="true" :actions="actions" :indexOffset="(positions.current_page - 1) * positions.per_page" emptyMessage="Chưa có chức vụ" />
        <Pagination :totalItems="positions.total" :itemsPerPage="positions.per_page" :currentPage="positions.current_page" :doingShow="positions.data.length" @page-change="load" />
    </AdminLayout>
    <Modal v-if="showModal" @close="showModal = false"><template #body><form class="max-h-[90vh] w-full max-w-xl space-y-4 overflow-y-auto rounded-2xl bg-white p-6" @submit.prevent="save">
        <h3 class="border-b pb-4 text-xl font-bold">{{ selected ? 'Cập nhật chức vụ' : 'Thêm chức vụ' }}</h3>
        <label class="block text-sm font-medium">Phòng ban *<select v-model="form.department_id" required class="mt-1 w-full rounded-lg border px-3 py-2"><option value="" disabled>Chọn phòng ban</option><option v-for="d in departments" :key="d.id" :value="d.id">{{ d.code }} — {{ d.name }}</option></select></label>
        <label v-if="selected" class="block text-sm font-medium">Mã chức vụ<input :value="selected.code" disabled class="mt-1 w-full rounded-lg border bg-gray-100 px-3 py-2" /></label>
        <label class="block text-sm font-medium">Tên chức vụ *<input v-model.trim="form.name" required class="mt-1 w-full rounded-lg border px-3 py-2" /></label>
        <label class="block text-sm font-medium">Mô tả<textarea v-model.trim="form.description" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2" /></label>
        <label class="block text-sm font-medium">Trạng thái<select v-model="form.status" class="mt-1 w-full rounded-lg border px-3 py-2"><option value="active">Đang hoạt động</option><option value="inactive">Ngừng hoạt động</option></select></label>
        <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p><div class="flex justify-end gap-2 border-t pt-4"><button type="button" class="rounded-lg border px-4 py-2" @click="showModal = false">Hủy</button><button :disabled="saving" class="rounded-lg bg-blue-600 px-4 py-2 text-white">{{ saving ? 'Đang lưu...' : 'Lưu' }}</button></div>
    </form></template></Modal>
</template>
<script setup>
import { Head, usePage } from '@inertiajs/vue3'; import { computed, h, onMounted, reactive, ref } from 'vue'; import axios from 'axios'; import { debounce } from 'lodash'; import { toast } from 'vue3-toastify';
import AdminLayout from '@/Layouts/AdminLayout.vue'; import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'; import DataTable from '@/components/DataTable.vue'; import Pagination from '@/components/Pagination.vue'; import Modal from '@/components/Modal.vue'; import EditButtonIcon from '@/icons/EditButtonIcon.vue'; import DeleteIcon from '@/icons/DeleteIcon.vue'; import { getValidationMessage } from '@/config/helpers';
const permissions = usePage().props.auth.permissions || [], can = p => permissions.includes(p); const departments = ref([]), positions = ref({ data: [], total: 0, per_page: 10, current_page: 1 }); const filters = reactive({ search: '', status: '', department_id: '' }); const showModal = ref(false), selected = ref(null), saving = ref(false), errorMessage = ref(''); const form = reactive({ department_id: '', name: '', description: '', status: 'active' });
const columns = [{ label: 'Mã', key: 'code' }, { label: 'Tên chức vụ', key: 'name' }, { label: 'Phòng ban', render: r => h('span', r.department?.name || '-') }, { label: 'Số nhân sự', render: r => h('span', { class: 'font-semibold text-blue-600' }, String(r.users_count || 0)) }, {
    label: 'Trạng thái',
    render: r => h('span', {
        class: [
            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
            r.status === 'active'
                ? 'bg-green-50 text-green-700 ring-green-600/20'
                : 'bg-red-50 text-red-700 ring-red-600/20',
        ],
    }, r.status === 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động'),
}];
const actions = computed(() => [{ icon: EditButtonIcon, type: 'edit', hidden: () => !can('nhan_su.sua'), onClick: openEdit }, { icon: DeleteIcon, type: 'delete', hidden: () => !can('nhan_su.xoa'), onClick: remove }]);
async function load(page = 1) { positions.value = (await axios.get('/api/positions', { params: { ...filters, page } })).data; } const debouncedLoad = debounce(() => load(1), 300);
function reset(p = null) { selected.value = p; Object.assign(form, { department_id: p?.department_id || '', name: p?.name || '', description: p?.description || '', status: p?.status || 'active' }); errorMessage.value = ''; } function openCreate() { reset(); showModal.value = true; } function openEdit(p) { reset(p); showModal.value = true; }
async function save() { saving.value = true; try { selected.value ? await axios.put(`/api/positions/${selected.value.id}`, form) : await axios.post('/api/positions', form); toast.success('Đã lưu chức vụ'); showModal.value = false; await load(positions.value.current_page); } catch (e) { errorMessage.value = getValidationMessage(e); } finally { saving.value = false; } }
async function remove(p) { if (!confirm(`Xóa chức vụ “${p.name}”?`)) return; try { await axios.delete(`/api/positions/${p.id}`); toast.success('Đã xóa chức vụ'); await load(positions.value.current_page); } catch (e) { toast.error(getValidationMessage(e)); } }
onMounted(async () => { departments.value = (await axios.get('/api/departments/all')).data; await load(); });
</script>
