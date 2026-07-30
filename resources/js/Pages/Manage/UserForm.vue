<template>
    <div
        class="relative w-full max-w-3xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20"
    >
        <!-- HEADER -->
        <div
            class="flex items-start justify-between bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-800 px-6 py-6 text-white sm:px-8"
        >
            <div class="flex items-center gap-3">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200">Hồ sơ nhân sự</p>
                    <h2 class="text-2xl font-bold leading-tight">
                        {{ props.user ? "Cập nhật nhân sự" : "Thêm nhân sự" }}
                    </h2>
                    <p class="mt-1 text-sm text-indigo-100/70">
                        {{
                            props.user
                                ? "Chỉnh sửa thông tin tài khoản nhân sự"
                                : "Tạo tài khoản nhân sự mới"
                        }}
                    </p>
                </div>
            </div>

            <button
                @click="$emit('close')"
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-indigo-100 transition-colors hover:bg-white/20 hover:text-white"
            >
                <i class="ti ti-x text-xl">X</i>
            </button>
        </div>

        <form novalidate @submit.prevent="saveUser">
            <!-- BODY -->
            <div class="asfy-modal-scroll max-h-[70vh] overflow-y-auto bg-slate-50/70 px-6 py-6 sm:px-8">
                <div v-if="errors.general" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ errors.general[0] }}
                </div>
                <!-- SECTION: THÔNG TIN CÁ NHÂN -->
                <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-id-badge-2 text-base"></i>
                        Thông tin cá nhân
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Họ tên <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    v-model="form.name"
                                    placeholder="Nhập họ tên"
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
                                    :class="errors.name ? 'border-red-300' : ''"
                                />
                            </div>
                            <p
                                v-if="errors.name"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.name[0] }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Số điện thoại
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    v-model="form.phone"
                                    placeholder="Nhập số điện thoại"
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
                                    :class="
                                        errors.phone ? 'border-red-300' : ''
                                    "
                                />
                            </div>
                            <p
                                v-if="errors.phone"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.phone[0] }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Email
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    v-model="form.email"
                                    placeholder="ten@congty.com"
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
                                    :class="
                                        errors.email ? 'border-red-300' : ''
                                    "
                                />
                            </div>
                            <p
                                v-if="errors.email"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.email[0] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SECTION: TÀI KHOẢN & BẢO MẬT -->
                <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-shield-lock text-base"></i>
                        Tài khoản &amp; bảo mật
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Tên đăng nhập
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-at absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    v-model="form.username"
                                    placeholder="Nhập tên đăng nhập"
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
                                    :class="
                                        errors.username ? 'border-red-300' : ''
                                    "
                                />
                            </div>
                            <p
                                v-if="errors.username"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.username[0] }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Mật khẩu
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    type="password"
                                    v-model="form.password"
                                    :placeholder="
                                        props.user
                                            ? 'Để trống nếu không đổi'
                                            : 'Nhập mật khẩu'
                                    "
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
                                    :class="
                                        errors.password ? 'border-red-300' : ''
                                    "
                                />
                            </div>
                            <p
                                v-if="errors.password"
                                class="text-red-500 text-xs mt-1 flex items-center gap-1"
                            >
                                <i class="ti ti-alert-circle"></i
                                >{{ errors.password[0] }}
                            </p>
                        </div>

                        <div v-if="form.password">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Xác nhận mật khẩu
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Nhập lại mật khẩu"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400"
                                :class="errors.password ? 'border-red-300' : ''"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Trạng thái
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-toggle-right absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"
                                ></i>
                                <select
                                    v-model="form.status"
                                    :disabled="['pending', 'pending_edit', 'rejected_final', 'expired'].includes(props.user?.status)"
                                    class="w-full appearance-none border border-gray-200 rounded-lg pl-5 pr-8 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 bg-white disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500"
                                >
                                    <option value="active">Hoạt động</option>
                                    <option value="blocked">
                                        Ngưng hoạt động
                                    </option>
                                </select>
                                <i
                                    class="ti ti-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"
                                ></i>
                            </div>
                            <p v-if="errors.status" class="mt-1 text-xs text-red-600">{{ errors.status[0] }}</p>
                        </div>
                    </div>
                    <div
                        v-if="props.user?.status === 'pending_edit'"
                        class="mt-4 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800"
                    >
                        <p class="font-semibold">Lý do từ chối</p>
                        <p class="mt-1 whitespace-pre-line">{{ props.user.rejection_reason || 'Chưa có lý do cụ thể.' }}</p>
                        <p class="mt-2 text-xs font-medium">Số lần từ chối: {{ props.user.rejection_count || 0 }}/3</p>
                        <p v-if="props.user.resubmit_expires_at" class="mt-1 text-xs">Hạn gửi lại: {{ new Date(props.user.resubmit_expires_at).toLocaleString('vi-VN') }}</p>
                    </div>
                </div>

                <!-- SECTION: PHÂN QUYỀN -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-key text-base"></i>
                        Phân quyền &amp; đơn vị
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="order-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Vai trò
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-shield-star absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none"
                                ></i>
                                <select
                                    v-model="form.role"
                                    :class="errors.role ? 'border-red-400' : ''"
                                    class="w-full appearance-none border border-gray-200 rounded-lg pl-5 pr-8 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 bg-white"
                                >
                                    <option value="" disabled>Chọn vai trò</option>
                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.name"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select>
                                <i
                                    class="ti ti-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"
                                ></i>
                            </div>
                            <p v-if="errors.role" class="mt-1 text-xs text-red-600">{{ errors.role[0] }}</p>
                        </div>

                        <div class="order-1">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1.5"
                            >
                                Công ty
                            </label>
                            <div class="relative">
                                <i
                                    class="ti ti-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"
                                ></i>
                                <input
                                    :value="currentCompany?.name || ''"
                                    disabled
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm bg-gray-50 text-gray-500"
                                />
                            </div>

                            <input type="hidden" v-model="form.company_id" />
                        </div>

                        <div v-if="!isCompanyOwner" class="order-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Phòng ban <span class="text-red-500">*</span>
                            </label>
                            <FormSelect
                                v-model="form.department_id"
                                :options="departmentOptions"
                                placeholder="Chọn hoặc tìm phòng ban"
                                :can-clear="false"
                                :allow-create="canCreateDepartment"
                                add-new-text="Thêm phòng ban mới"
                                no-options-text="Chưa có phòng ban"
                                no-results-text="Không tìm thấy phòng ban"
                                :error="errors.department_id"
                                @add-new="openDepartmentModal"
                            />
                            <p v-if="errors.department_id" class="mt-1 text-xs text-red-600">
                                {{ errors.department_id[0] }}
                            </p>
                        </div>
                        <div v-if="!isCompanyOwner" class="order-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Chức vụ <span class="text-red-500">*</span>
                            </label>
                            <FormSelect
                                v-model="form.position_id"
                                :options="positionOptions"
                                :disabled="!form.department_id"
                                :placeholder="form.department_id ? 'Chọn hoặc tìm chức vụ' : 'Chọn phòng ban trước'"
                                :can-clear="false"
                                :allow-create="canCreatePosition && Boolean(form.department_id)"
                                add-new-text="Thêm chức vụ mới"
                                no-options-text="Chưa có chức vụ trong phòng ban"
                                no-results-text="Không tìm thấy chức vụ"
                                :error="errors.position_id"
                                @add-new="openPositionModal"
                            />
                            <p v-if="errors.position_id" class="mt-1 text-xs text-red-600">{{ errors.position_id[0] }}</p>
                        </div>
                        <div v-if="isCompanyOwner" class="order-5 sm:col-span-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                            Giám đốc điều hành toàn công ty nên không thuộc một phòng ban hoặc chức vụ phòng ban cụ thể.
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-white px-6 py-4 sm:px-8">
                <p class="hidden text-xs text-slate-400 sm:block"><span class="text-red-500">*</span> Thông tin bắt buộc</p>
                <div class="ml-auto flex gap-3">
                <button
                    type="button"
                    class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
                    @click="$emit('close')"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    <i v-else class="ti ti-device-floppy text-base"></i>
                    {{ submitting ? 'Đang lưu...' : 'Lưu thay đổi' }}
                </button>
                </div>
            </div>
        </form>
    </div>

    <Modal v-if="showDepartmentModal" @close="showDepartmentModal = false">
        <template #body>
            <form class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl" @submit.prevent="createDepartment">
                <div class="border-b border-slate-200 bg-gradient-to-r from-indigo-50 to-white px-6 py-5">
                    <h3 class="text-xl font-bold text-slate-800">Thêm phòng ban</h3>
                    <p class="mt-1 text-sm text-slate-500">Phòng ban mới sẽ được chọn tự động cho nhân sự.</p>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <label class="block text-sm font-medium text-slate-700">
                        Tên phòng ban <span class="text-red-500">*</span>
                        <input v-model.trim="departmentForm.name" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" placeholder="Ví dụ: Phòng Kinh doanh" />
                        <span v-if="quickErrors.name" class="mt-1 block text-xs text-red-600">{{ quickErrors.name[0] }}</span>
                    </label>
                    <label class="block text-sm font-medium text-slate-700">
                        Mô tả
                        <textarea v-model.trim="departmentForm.description" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100" placeholder="Mô tả ngắn về phòng ban"></textarea>
                    </label>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="showDepartmentModal = false">Hủy</button>
                    <button :disabled="quickSaving" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">{{ quickSaving ? 'Đang lưu...' : 'Thêm phòng ban' }}</button>
                </div>
            </form>
        </template>
    </Modal>

    <Modal v-if="showPositionModal" @close="showPositionModal = false">
        <template #body>
            <form class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl" @submit.prevent="createPosition">
                <div class="border-b border-slate-200 bg-gradient-to-r from-violet-50 to-white px-6 py-5">
                    <h3 class="text-xl font-bold text-slate-800">Thêm chức vụ</h3>
                    <p class="mt-1 text-sm text-slate-500">Thêm vào {{ selectedDepartmentLabel }} và chọn tự động.</p>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <label class="block text-sm font-medium text-slate-700">
                        Tên chức vụ <span class="text-red-500">*</span>
                        <input v-model.trim="positionForm.name" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-100" placeholder="Ví dụ: Trưởng nhóm" />
                        <span v-if="quickErrors.name" class="mt-1 block text-xs text-red-600">{{ quickErrors.name[0] }}</span>
                    </label>
                    <label class="block text-sm font-medium text-slate-700">
                        Mô tả
                        <textarea v-model.trim="positionForm.description" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3.5 py-2.5 focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-100" placeholder="Mô tả ngắn về chức vụ"></textarea>
                    </label>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="showPositionModal = false">Hủy</button>
                    <button :disabled="quickSaving" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700 disabled:opacity-50">{{ quickSaving ? 'Đang lưu...' : 'Thêm chức vụ' }}</button>
                </div>
            </form>
        </template>
    </Modal>
</template>

<script setup>
import { computed, ref, reactive, watch, onMounted } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";
import FormSelect from "@/components/FormSelect.vue";
import Modal from "@/components/Modal.vue";
import "vue3-toastify/dist/index.css";
const page = usePage();
const currentCompany = computed(() => props.company || page.props.auth?.user?.company || page.props.auths?.user?.company || null);
const errors = ref({});
const submitting = ref(false);
const quickSaving = ref(false);
const showDepartmentModal = ref(false);
const showPositionModal = ref(false);
const quickErrors = ref({});
const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    company: {
        type: Object,
    },
});
const isCompanyOwner = computed(() => Boolean(props.user?.is_company_owner));

const emit = defineEmits(["saved", "close"]);

const roles = ref([]);
const departments = ref([]);
const positions = ref([]);
const permissions = computed(() => page.props.auth?.permissions || []);
const canCreateDepartment = computed(() => permissions.value.includes("phong_ban.them"));
const canCreatePosition = computed(() => permissions.value.includes("chuc_vu.them"));
const departmentOptions = computed(() =>
    departments.value.map((department) => ({
        value: department.id,
        label: [department.code, department.name].filter(Boolean).join(" — "),
    })),
);
const positionOptions = computed(() =>
    positions.value.map((position) => ({
        value: position.id,
        label: [position.code, position.name].filter(Boolean).join(" — "),
    })),
);
const selectedDepartmentLabel = computed(
    () =>
        departments.value.find(
            (department) => Number(department.id) === Number(form.department_id),
        )?.name || "phòng ban đã chọn",
);

const departmentForm = reactive({ name: "", description: "", status: "active" });
const positionForm = reactive({ name: "", description: "", status: "active" });

const form = reactive({
    name: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
    status: "active",
    role: "",
    company_id: "",
    department_id: "",
    position_id: "",
});

watch(
    () => props.company,
    (value) => {
        if (value) {
            form.company_id = value.id;
        }
    },
    { immediate: true },
);
watch(
    () => props.user,
    (value) => {
        if (value) {
            Object.assign(form, {
                name: value.name || "",
                username: value.username || "",
                email: value.email || "",
                phone: value.phone || "",
                password: "",
                password_confirmation: "",
                status: value.status || "",
                role: value.roles?.[0]?.name || "",
                department_id: value.department_id || "",
                position_id: value.position_id || "",
            });
        } else {
            Object.assign(form, {
                name: "",
                username: "",
                email: "",
                phone: "",
                password: "",
                password_confirmation: "",
                status: "active",
                role: "",
                department_id: "",
                position_id: "",
            });
        }
    },
    { immediate: true },
);
const getRoles = async () => {
    try {
        const res = await axios.get("/api/users/roles");

        roles.value = [
            ...(res.data.data.system || []),
            ...(res.data.data.user || []),
        ];
    } catch (error) {
        console.error("Không load được vai trò", error);
    }
};
const getDepartments = async () => {
    try {
        const res = await axios.get('/api/departments/all');
        departments.value = res.data;
    } catch (error) {
        console.error('Không load được phòng ban', error);
    }
};
const getPositions = async (departmentId) => {
    positions.value = [];
    if (!departmentId) return;
    try {
        const res = await axios.get('/api/positions/all', { params: { department_id: departmentId } });
        positions.value = res.data;
        if (!positions.value.some(item => item.id === Number(form.position_id))) form.position_id = '';
    } catch (error) {
        console.error('Không load được chức vụ', error);
    }
};
watch(() => form.department_id, value => getPositions(value), { immediate: true });

function openDepartmentModal() {
    Object.assign(departmentForm, { name: "", description: "", status: "active" });
    quickErrors.value = {};
    showDepartmentModal.value = true;
}

function openPositionModal() {
    if (!form.department_id) {
        toast.warning("Vui lòng chọn phòng ban trước.");
        return;
    }
    Object.assign(positionForm, { name: "", description: "", status: "active" });
    quickErrors.value = {};
    showPositionModal.value = true;
}

async function createDepartment() {
    quickSaving.value = true;
    quickErrors.value = {};
    try {
        const { data } = await axios.post("/api/departments", departmentForm);
        await getDepartments();
        form.department_id = data.data.id;
        form.position_id = "";
        showDepartmentModal.value = false;
        toast.success("Đã thêm và chọn phòng ban mới.");
    } catch (error) {
        quickErrors.value = error.response?.data?.errors || {};
        if (!Object.keys(quickErrors.value).length) {
            toast.error(error.response?.data?.message || "Không thể thêm phòng ban.");
        }
    } finally {
        quickSaving.value = false;
    }
}

async function createPosition() {
    quickSaving.value = true;
    quickErrors.value = {};
    try {
        const { data } = await axios.post("/api/positions", {
            ...positionForm,
            department_id: form.department_id,
        });
        await getPositions(form.department_id);
        form.position_id = data.data.id;
        showPositionModal.value = false;
        toast.success("Đã thêm và chọn chức vụ mới.");
    } catch (error) {
        quickErrors.value = error.response?.data?.errors || {};
        if (!Object.keys(quickErrors.value).length) {
            toast.error(error.response?.data?.message || "Không thể thêm chức vụ.");
        }
    } finally {
        quickSaving.value = false;
    }
}

async function saveUser() {
    errors.value = {};
    submitting.value = true;

    try {
        let response;
        if (props.user?.id) {
            response = await axios.put(`/api/users/user/${props.user.id}`, form);
        } else {
            response = await axios.post("/api/users/user", form);
        }
        toast.success(
            response?.data?.message ||
                (props.user?.id
                    ? "Cập nhật nhân sự thành công."
                    : "Thêm nhân sự thành công."),
        );
        emit("saved");
        emit("close");
    } catch (error) {

        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            if (!Object.keys(errors.value).length) {
                errors.value = { general: [error.response.data.message || 'Dữ liệu chưa hợp lệ.'] };
            }
        } else {
            toast.error(error.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại.");
        }
    } finally {
        submitting.value = false;
    }
}
onMounted(() => {
    getRoles();
    getDepartments();
});
</script>
