<template>
    <div
        class="relative bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 z-50"
    >
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">
                {{ props.user ? "Cập nhật nhân sự" : "Thêm nhân sự" }}
            </h2>
            <button @click="$emit('close')">✕</button>
        </div>
        <form @submit.prevent="saveUser">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Họ tên</label>
                    <input
                        v-model="form.name"
                        class="border p-2 w-full rounded"
                    />
                    <p v-if="errors.name" class="text-red-500 text-xs">
                        {{ errors.name[0] }}
                    </p>
                </div>
                <div>
                    <label>Tên đăng nhập</label>
                    <input
                        v-model="form.username"
                        class="border p-2 w-full rounded"
                    />
                    <p v-if="errors.username" class="text-red-500 text-xs">
                        {{ errors.username[0] }}
                    </p>
                </div>
                <div>
                    <label>Email</label>
                    <input
                        v-model="form.email"
                        class="border p-2 w-full rounded"
                    />
                    <p v-if="errors.email" class="text-red-500 text-xs">
                        {{ errors.email[0] }}
                    </p>
                </div>
                <div>
                    <label>Số điện thoại</label>
                    <input
                        v-model="form.phone"
                        class="border p-2 w-full rounded"
                    />
                    <p v-if="errors.phone" class="text-red-500 text-xs">
                        {{ errors.phone[0] }}
                    </p>
                </div>
                <div>
                    <label>Mật khẩu</label>
                    <input
                        type="text"
                        v-model="form.password"
                        class="border p-2 w-full rounded"
                    />
                    <p v-if="errors.password" class="text-red-500 text-xs">
                        {{ errors.password[0] }}
                    </p>
                </div>
                <div>
                    <label>Trạng thái</label>

                    <select
                        v-model="form.status"
                        class="border p-2 w-full rounded"
                    >
                        <option value="active">Hoạt động</option>
                        <option value="blocked">Ngưng hoạt động</option>
                    </select>
                </div>
                <div>
                    <label>Vai trò</label>
                    <select
                        v-model="form.role"
                        class="border p-2 w-full rounded"
                    >
                        <option
                            v-for="role in roles"
                            :key="role.id"
                            :value="role.name"
                        >
                            {{ role.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label>Công ty</label>

                    <input
                        :value="company?.name || ''"
                        disabled
                        class="border p-2 w-full rounded bg-gray-100"
                    />

                    <input type="hidden" v-model="form.company_id" />
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button
                    type="button"
                    class="px-4 py-2 border rounded"
                    @click="$emit('close')"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded"
                >
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

const form = reactive({
    name: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    status: "active",
    role: "",
    company_id: "",
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
                status: value.status || "",
                role: value.roles?.[0]?.name || "",
            });
        } else {
            Object.assign(form, {
                name: "",
                username: "",
                email: "",
                phone: "",
                password: "",
                status: "active",
                role: "",
            });
        }
    },
    { immediate: true },
);
const getRoles = async () => {
    const res = await axios.get("/api/roles");
    roles.value = res.data.data ?? res.data;
};
async function saveUser() {
    errors.value = {};

    try {
        if (props.user?.id) {
            await axios.put(`/api/users/user/${props.user.id}`, form);
        } else {
            await axios.post("/api/users/user", form);
        }

        emit("saved");
        emit("close");
    } catch (error) {
        console.log(error.response?.data);

        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert("Có lỗi xảy ra");
        }
    }
}
console.log(page.props);
onMounted(() => {
    getRoles();
});
</script>
