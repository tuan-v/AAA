<template>
    <Head title="Phòng ban" />
    <AdminLayout>
        <PageBreadcrumb
            title="Phòng ban"
            :items="[{ text: 'Phòng ban', link: null }]"
        />

        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold">Danh sách phòng ban</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Quản lý cơ cấu phòng ban và trưởng phòng.
                </p>
            </div>
            <button
                v-if="can('nhan_su.them')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white"
                @click="openCreate"
            >
                + Phòng ban
            </button>
        </div>

        <div
            class="mb-5 flex flex-wrap gap-3 rounded-xl border bg-white p-4 shadow-sm"
        >
            <input
                v-model="filters.search"
                class="rounded-lg border px-3 py-2"
                placeholder="Tìm mã hoặc tên phòng ban..."
                @input="debouncedLoad"
            />
            <select
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
            :data="departments.data"
            :showIndex="true"
            :actions="actions"
            :indexOffset="(departments.current_page - 1) * departments.per_page"
            emptyMessage="Chưa có phòng ban"
        />
        <Pagination
            :totalItems="departments.total"
            :itemsPerPage="departments.per_page"
            :currentPage="departments.current_page"
            :doingShow="departments.data.length"
            @page-change="load"
        />
    </AdminLayout>

    <Modal v-if="showModal" @close="showModal = false">
        <template #body>
            <form
                novalidate
                class="asfy-modal-scroll max-h-[85vh] w-full max-w-xl space-y-5 overflow-y-auto rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl"
                @submit.prevent="save"
            >
                <div
                    class="-mx-6 -mt-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5"
                >
                    <h3 class="text-xl font-bold text-slate-800">
                        {{ selected ? "Cập nhật phòng ban" : "Thêm phòng ban" }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Thiết lập thông tin phòng ban và người phụ trách.
                    </p>
                </div>
                <div
                    class="grid gap-4"
                    :class="selected ? 'sm:grid-cols-2' : ''"
                >
                    <label v-if="selected" class="text-sm font-medium"
                        >Mã phòng ban
                        <input
                            :value="selected.code"
                            disabled
                            class="mt-1 w-full rounded-lg border bg-gray-100 px-3 py-2 text-gray-500"
                        />
                    </label>
                    <label class="text-sm font-medium"
                        >Tên phòng ban <span class="text-red-500">*</span>
                        <input
                            v-model.trim="form.name"
                            required
                            class="mt-1 w-full rounded-lg border px-3 py-2"
                            :class="fieldErrors.name ? 'border-red-400' : ''"
                            @input="clearFieldError('name')"
                        />
                        <span v-if="fieldErrors.name" class="mt-1 block text-xs font-normal text-red-600">{{ fieldErrors.name[0] }}</span>
                    </label>
                </div>
                <label class="block text-sm font-medium"
                    >Trưởng phòng
                    <FormSelect
                        v-model="form.manager_id"
                        class="mt-1"
                        :options="managerOptions"
                        searchable
                        can-clear
                        placeholder="Chọn hoặc tìm trưởng phòng"
                        no-options-text="Chưa có nhân sự phù hợp"
                        no-results-text="Không tìm thấy nhân sự"
                        :error="fieldErrors.manager_id"
                        @update:model-value="clearFieldError('manager_id')"
                    />
                </label>
                <label class="block text-sm font-medium"
                    >Mô tả
                    <textarea
                        v-model.trim="form.description"
                        rows="3"
                        class="mt-1 w-full rounded-lg border px-3 py-2"
                        :class="fieldErrors.description ? 'border-red-400' : ''"
                        @input="clearFieldError('description')"
                    ></textarea>
                    <span v-if="fieldErrors.description" class="mt-1 block text-xs font-normal text-red-600">{{ fieldErrors.description[0] }}</span>
                </label>
                <label class="block text-sm font-medium"
                    >Trạng thái
                    <select
                        v-model="form.status"
                        class="mt-1 w-full rounded-lg border px-3 py-2"
                        :class="fieldErrors.status ? 'border-red-400' : ''"
                        @change="clearFieldError('status')"
                    >
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Ngừng hoạt động</option>
                    </select>
                    <span v-if="fieldErrors.status" class="mt-1 block text-xs font-normal text-red-600">{{ fieldErrors.status[0] }}</span>
                </label>
                <p v-if="errorMessage" class="text-sm text-red-600">
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
                        Hủy
                    </button>
                    <button
                        :disabled="saving"
                        class="min-w-28 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ saving ? "Đang lưu..." : "Lưu thay đổi" }}
                    </button>
                </div>
            </form>
        </template>
    </Modal>
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
import FormSelect from "@/components/FormSelect.vue";
import EditButtonIcon from "@/icons/EditButtonIcon.vue";
import DeleteIcon from "@/icons/DeleteIcon.vue";
import { getValidationMessage } from "@/config/helpers";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

const permissions = usePage().props.auth.permissions || [];
const can = (permission) => permissions.includes(permission);
const departments = ref({ data: [], total: 0, per_page: 10, current_page: 1 });
const users = ref([]);
const managerOptions = computed(() =>
    users.value.map((user) => ({
        value: user.id,
        label: [user.name, user.is_company_owner ? "Giám đốc" : user.email]
            .filter(Boolean)
            .join(" — "),
    })),
);
const filters = reactive({ search: "", status: "" });
const showModal = ref(false);
const selected = ref(null);
const saving = ref(false);
const errorMessage = ref("");
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    description: "",
    status: "active",
    manager_id: "",
});

const columns = [
    { label: "Mã", key: "code" },
    { label: "Tên phòng ban", key: "name" },
    {
        label: "Trưởng phòng",
        render: (row) => h("span", row.manager?.name || "Chưa chỉ định"),
    },
    {
        label: "Số nhân sự",
        render: (row) =>
            h(
                "span",
                { class: "font-semibold text-blue-600" },
                String(row.users_count || 0),
            ),
    },
    { label: "Mô tả", render: (row) => h("span", row.description || "-") },
    {
        label: "Trạng thái",
        render: (row) =>
            h(
                "span",
                {
                    class: [
                        "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset",
                        row.status === "active"
                            ? "bg-green-50 text-green-700 ring-green-600/20"
                            : "bg-red-50 text-red-700 ring-red-600/20",
                    ],
                },
                row.status === "active" ? "Đang hoạt động" : "Ngừng hoạt động",
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
    const { data } = await axios.get("/api/departments", {
        params: { ...filters, page },
    });
    departments.value = data;
}
const debouncedLoad = debounce(() => load(1), 300);
function resetForm(department = null) {
    selected.value = department;
    Object.assign(form, {
        name: department?.name || "",
        description: department?.description || "",
        status: department?.status || "active",
        manager_id: department?.manager_id || "",
    });
    errorMessage.value = "";
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field]);
}
function clearFieldError(field) {
    delete fieldErrors[field];
}
function openCreate() {
    resetForm();
    showModal.value = true;
}
function openEdit(department) {
    resetForm(department);
    showModal.value = true;
}
async function save() {
    saving.value = true;
    errorMessage.value = "";
    Object.keys(fieldErrors).forEach((field) => delete fieldErrors[field]);
    try {
        if (selected.value)
            await axios.put(`/api/departments/${selected.value.id}`, form);
        else await axios.post("/api/departments", form);
        toast.success(
            selected.value ? "Đã cập nhật phòng ban" : "Đã thêm phòng ban",
        );
        showModal.value = false;
        await load(departments.value.current_page);
    } catch (error) {
        const validationErrors = error.response?.data?.errors;
        if (validationErrors && typeof validationErrors === "object") {
            Object.entries(validationErrors).forEach(([field, messages]) => {
                fieldErrors[field] = Array.isArray(messages) ? messages : [String(messages)];
            });
        } else {
            errorMessage.value = getValidationMessage(error);
        }
    } finally {
        saving.value = false;
    }
}
async function remove(department) {
    if (!confirm(`Xóa phòng ban “${department.name}”?`)) return;
    try {
        await axios.delete(`/api/departments/${department.id}`);
        toast.success("Đã xóa phòng ban");
        await load(departments.value.current_page);
    } catch (error) {
        toast.error(getValidationMessage(error));
    }
}
useRealtimeRefresh(() => load(departments.value.current_page || 1));

onMounted(async () => {
    const { data } = await axios.get("/api/departments/managers");
    users.value = data || [];
    await load();
});
</script>
