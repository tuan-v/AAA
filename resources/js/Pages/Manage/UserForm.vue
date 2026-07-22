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
                                    class="w-full appearance-none border border-gray-200 rounded-lg pl-5 pr-8 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 bg-white"
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
                        <div>
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

                        <div>
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

                        <div v-if="!isCompanyOwner">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Phòng ban <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.department_id"
                                required
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                                :class="errors.department_id ? 'border-red-400' : ''"
                            >
                                <option value="" disabled>Chọn phòng ban</option>
                                <option v-for="department in departments" :key="department.id" :value="department.id">
                                    {{ department.code }} — {{ department.name }}
                                </option>
                            </select>
                            <p v-if="errors.department_id" class="mt-1 text-xs text-red-600">
                                {{ errors.department_id[0] }}
                            </p>
                        </div>
                        <div v-if="!isCompanyOwner">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Chức vụ <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.position_id" required :disabled="!form.department_id"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm disabled:bg-gray-100" :class="errors.position_id ? 'border-red-400' : ''">
                                <option value="" disabled>Chọn chức vụ</option>
                                <option v-for="position in positions" :key="position.id" :value="position.id">
                                    {{ position.code }} — {{ position.name }}
                                </option>
                            </select>
                            <p v-if="errors.position_id" class="mt-1 text-xs text-red-600">{{ errors.position_id[0] }}</p>
                        </div>
                        <div v-if="isCompanyOwner" class="sm:col-span-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
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
</template>

<script setup>
import { computed, ref, reactive, watch, onMounted } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const page = usePage();
const currentCompany = computed(() => props.company || page.props.auth?.user?.company || page.props.auths?.user?.company || null);
const errors = ref({});
const submitting = ref(false);
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
        const res = await axios.get("/api/roles");

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
async function saveUser() {
    errors.value = {};
    submitting.value = true;

    try {
        if (props.user?.id) {
            await axios.put(`/api/users/user/${props.user.id}`, form);
        } else {
            await axios.post("/api/users/user", form);
        }
        toast.success("Lưu nhân sự thành công!", {
            position: "top-right",
            autoClose: 3000,
        });
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
