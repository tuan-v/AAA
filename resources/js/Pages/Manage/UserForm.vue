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
                </div>
                <div>
                    <label>Tên đăng nhập</label>
                    <input
                        v-model="form.username"
                        class="border p-2 w-full rounded"
                    />
                </div>
                <div>
                    <label>Email</label>
                    <input
                        v-model="form.email"
                        class="border p-2 w-full rounded"
                    />
                </div>
                <div>
                    <label>Số điện thoại</label>
                    <input
                        v-model="form.phone"
                        class="border p-2 w-full rounded"
                    />
                </div>
                <div>
                    <label>Mật khẩu</label>
                    <input
                        type="text"
                        v-model="form.password"
                        class="border p-2 w-full rounded"
                    />
                </div>
                <div>
                    <label>Trạng thái</label>

                    <select
                        v-model="form.status"
                        class="border p-2 w-full rounded"
                    >
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Ngưng hoạt động</option>
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
import pagination from "@/components/Pagination.vue";
const props = defineProps({
    user: {
        type: Object,
        default: null,
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
});

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
                roles: "",
            });
        }
    },
    { immediate: true },
);
const getRoles = async () => {
    const res = await axios.get("/api/roles");
    roles.value = res.data.data ?? res.data;
};
function saveUser() {
    if (props.user?.id) {
        axios.put(`/api/users/user/${props.user.id}`, form).then(() => {
            emit("saved");
            emit("close");
        });
    } else {
        axios.post("/api/users/user", form).then(() => {
            emit("saved");
            emit("close");
        });
    }
}

onMounted(() => {
    getRoles();
});
</script>
