<template>
    <Head title="Chức vụ" /><AdminLayout>
        <PageBreadcrumb
            title="Chức vụ"
            :items="[{ text: 'Chức vụ', link: null }]"
        />
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Danh sách chức vụ</h2>
                <p class="text-sm text-gray-500">
                    Quản lý chức vụ theo từng phòng ban.
                </p>
            </div>
            <button
                v-if="can('nhan_su.them')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white"
                @click="openCreate"
            >
                + Chức vụ
            </button>
        </div>
        <div class="mb-5 flex flex-wrap gap-3 rounded-xl border bg-white p-4">
            <input
                v-model="filters.search"
                class="rounded-lg border px-3 py-2"
                placeholder="Tìm mã hoặc tên..."
                @input="debouncedLoad"
            /><select
                v-model="filters.department_id"
                class="rounded-lg border px-3 py-2"
                @change="load(1)"
            >
                <option value="">Tất cả phòng ban</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">
                    {{ d.name }}
                </option></select
            ><select
                v-model="filters.status"
                class="rounded-lg border px-3 py-2"
                @change="load(1)"
            >
                <option value="">Tất cả trạng thái</option>
                <option value="active">Đang hoạt động</option>
                <option value="inactive">Ngừng hoạt động</option>
            </select>
        </div>
        <DataTable
            :columns="columns"
            :data="positions.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(positions.current_page - 1) * positions.per_page"
            emptyMessage="Chưa có chức vụ"
        />
        <Pagination
            :totalItems="positions.total"
            :itemsPerPage="positions.per_page"
            :currentPage="positions.current_page"
            :doingShow="positions.data.length"
            @page-change="load"
        />
    </AdminLayout>
    <Modal v-if="showModal" @close="showModal = false"
        ><template #body
            ><form
                novalidate
                class="max-h-[85vh] w-full max-w-xl space-y-5 overflow-y-auto rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl"
                @submit.prevent="save"
            >
                <div
                    class="-mx-6 -mt-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5"
                >
                    <h3 class="text-xl font-bold text-slate-800">
                        {{ selected ? "Cập nhật chức vụ" : "Thêm chức vụ" }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Thiết lập chức vụ và phòng ban trực thuộc.
                    </p>
                </div>
                <label class="block text-sm font-medium"
                    >Phòng ban <span class="text-red-500">*</span><select
                        v-model="form.department_id"
                        required
                        class="mt-1 w-full rounded-lg border px-3 py-2"
                        :class="
                            fieldErrors.department_id ? 'border-red-500' : ''
                        "
                        @change="clearFieldError('department_id')"
                    >
                        <option value="" disabled>Chọn phòng ban</option>
                        <option
                            v-for="d in departments"
                            :key="d.id"
                            :value="d.id"
                        >
                            {{ d.code }} — {{ d.name }}
                        </option></select
                    ><span
                        v-if="fieldErrors.department_id"
                        class="mt-1 block text-xs font-normal text-red-600"
                        >{{ fieldErrors.department_id }}</span
                    ></label
                >
                <label v-if="selected" class="block text-sm font-medium"
                    >Mã chức vụ<input
                        :value="selected.code"
                        disabled
                        class="mt-1 w-full rounded-lg border bg-gray-100 px-3 py-2"
                /></label>
                <label class="block text-sm font-medium"
                    >Tên chức vụ <span class="text-red-500">*</span><input
                        v-model.trim="form.name"
                        required
                        class="mt-1 w-full rounded-lg border px-3 py-2"
                        :class="fieldErrors.name ? 'border-red-500' : ''"
                        @input="clearFieldError('name')"
                    /><span
                        v-if="fieldErrors.name"
                        class="mt-1 block text-xs font-normal text-red-600"
                        >{{ fieldErrors.name }}</span
                    ></label
                >
                <label class="block text-sm font-medium"
                    >Mô tả<textarea
                        v-model.trim="form.description"
                        rows="3"
                        class="mt-1 w-full rounded-lg border px-3 py-2"
                        :class="fieldErrors.description ? 'border-red-500' : ''"
                        @input="clearFieldError('description')"
                    /><span
                        v-if="fieldErrors.description"
                        class="mt-1 block text-xs font-normal text-red-600"
                        >{{ fieldErrors.description }}</span
                    ></label
                >
                <label class="block text-sm font-medium"
                    >Trạng thái<select
                        v-model="form.status"
                        class="mt-1 w-full rounded-lg border px-3 py-2"
                        :class="fieldErrors.status ? 'border-red-500' : ''"
                        @change="clearFieldError('status')"
                    >
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">
                            Ngừng hoạt động
                        </option></select
                    ><span
                        v-if="fieldErrors.status"
                        class="mt-1 block text-xs font-normal text-red-600"
                        >{{ fieldErrors.status }}</span
                    ></label
                >
                <p
                    v-if="errorMessage"
                    class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
                >
                    {{ errorMessage }}
                </p>
                <div
                    class="-mx-6 -mb-6 flex justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4"
                >
                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                        @click="showModal = false"
                    >
                        Hủy</button
                    ><button
                        :disabled="saving"
                        class="min-w-28 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ saving ? "Đang lưu..." : "Lưu thay đổi" }}
                    </button>
                </div>
            </form></template
        ></Modal
    >
</template>
<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { computed, h, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { debounce } from "lodash";
import { toast } from "vue3-toastify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import Pagination from "@/components/Pagination.vue";
import Modal from "@/components/Modal.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import { getValidationMessage } from "@/config/helpers";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";
const permissions = usePage().props.auth.permissions || [],
    can = (p) => permissions.includes(p);
const departments = ref([]),
    positions = ref({ data: [], total: 0, per_page: 10, current_page: 1 });
const filters = reactive({ search: "", status: "", department_id: "" });
const showModal = ref(false),
    selected = ref(null),
    saving = ref(false),
    errorMessage = ref("");
const fieldErrors = reactive({});
const form = reactive({
    department_id: "",
    name: "",
    description: "",
    status: "active",
});
const columns = [
    { label: "Mã", key: "code" },
    { label: "Tên chức vụ", key: "name" },
    { label: "Phòng ban", render: (r) => h("span", r.department?.name || "-") },
    {
        label: "Số nhân sự",
        render: (r) =>
            h(
                "span",
                { class: "font-semibold text-blue-600" },
                String(r.users_count || 0),
            ),
    },
    {
        label: "Trạng thái",
        render: (r) =>
            h(
                "span",
                {
                    class: [
                        "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset",
                        r.status === "active"
                            ? "bg-green-50 text-green-700 ring-green-600/20"
                            : "bg-red-50 text-red-700 ring-red-600/20",
                    ],
                },
                r.status === "active" ? "Đang hoạt động" : "Ngừng hoạt động",
            ),
    },
];
const actions = computed(() => [
    {
        icon: EditButtonIcon,
        type: "edit",
        hidden: () => !can("nhan_su.sua"),
        onClick: openEdit,
    },
    {
        icon: DeleteIcon,
        type: "delete",
        hidden: () => !can("nhan_su.xoa"),
        onClick: remove,
    },
]);
async function load(page = 1) {
    positions.value = (
        await axios.get("/api/positions", { params: { ...filters, page } })
    ).data;
}
const debouncedLoad = debounce(() => load(1), 300);
function clearFieldError(field) {
    delete fieldErrors[field];
}
function reset(p = null) {
    selected.value = p;
    Object.assign(form, {
        department_id: p?.department_id || "",
        name: p?.name || "",
        description: p?.description || "",
        status: p?.status || "active",
    });
    Object.keys(fieldErrors).forEach(clearFieldError);
    errorMessage.value = "";
}
function openCreate() {
    reset();
    showModal.value = true;
}
function openEdit(p) {
    reset(p);
    showModal.value = true;
}
async function save() {
    saving.value = true;
    Object.keys(fieldErrors).forEach(clearFieldError);
    errorMessage.value = "";
    try {
        selected.value
            ? await axios.put(`/api/positions/${selected.value.id}`, form)
            : await axios.post("/api/positions", form);
        toast.success("Đã lưu chức vụ");
        showModal.value = false;
        await load(positions.value.current_page);
    } catch (e) {
        const errors = e?.response?.data?.errors;
        if (errors && typeof errors === "object") {
            Object.entries(errors).forEach(([field, messages]) => {
                fieldErrors[field] = Array.isArray(messages)
                    ? messages[0]
                    : String(messages);
            });
        } else {
            errorMessage.value = getValidationMessage(e);
        }
    } finally {
        saving.value = false;
    }
}
async function remove(p) {
    if (!confirm(`Xóa chức vụ “${p.name}”?`)) return;
    try {
        await axios.delete(`/api/positions/${p.id}`);
        toast.success("Đã xóa chức vụ");
        await load(positions.value.current_page);
    } catch (e) {
        toast.error(getValidationMessage(e));
    }
}
useRealtimeRefresh(() => load(positions.value.current_page || 1));

onMounted(async () => {
    departments.value = (await axios.get("/api/departments/all")).data;
    await load();
});
</script>
