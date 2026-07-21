<template>
    <div
        class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-2xl overflow-hidden"
    >
        <!-- HEADER -->
        <div
            class="flex justify-between items-center px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white"
        >
            <div class="flex items-center gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 leading-tight">
                        {{ props.user ? "Cập nhật nhân sự" : "Thêm nhân sự" }}
                    </h2>
                    <p class="text-sm text-gray-400 mt-0.5">
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
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
                <i class="ti ti-x text-xl">X</i>
            </button>
        </div>

        <form @submit.prevent="saveUser">
            <!-- BODY -->
            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                <!-- SECTION: THÔNG TIN CÁ NHÂN -->
                <div class="mb-6">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-id-badge-2 text-base"></i>
                        Thông tin cá nhân
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
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
                <div class="mb-6">
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-shield-lock text-base"></i>
                        Tài khoản &amp; bảo mật
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
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
                        </div>
                    </div>
                </div>

                <!-- SECTION: PHÂN QUYỀN -->
                <div>
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                    >
                        <i class="ti ti-key text-base"></i>
                        Phân quyền &amp; đơn vị
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
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
                                    :value="company?.name || ''"
                                    disabled
                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-3 py-2.5 text-sm bg-gray-50 text-gray-500"
                                />
                            </div>

                            <input type="hidden" v-model="form.company_id" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Phòng ban <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.department_id"
                                required
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100"
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Chức vụ <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.position_id" required :disabled="!form.department_id"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm disabled:bg-gray-100">
                                <option value="" disabled>Chọn chức vụ</option>
                                <option v-for="position in positions" :key="position.id" :value="position.id">
                                    {{ position.code }} — {{ position.name }}
                                </option>
                            </select>
                            <p v-if="errors.position_id" class="mt-1 text-xs text-red-600">{{ errors.position_id[0] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div
                class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50/60"
            >
                <button
                    type="button"
                    class="px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
                    @click="$emit('close')"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2"
                >
                    <i class="ti ti-device-floppy text-base"></i>
                    Lưu
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const page = usePage();
const company = page.props.auths?.user?.company;
const errors = ref({});
const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    company: {
        type: Object,
    },
});

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
        } else {
            alert("Có lỗi xảy ra");
        }
    }
}
onMounted(() => {
    getRoles();
    getDepartments();
});
</script>
