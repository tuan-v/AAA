<template>
    <div class="relative bg-white rounded-xl shadow-lg w-[700px] p-6 z-50">
        <h2 class="text-xl font-bold mb-4">
            {{ user ? "Cập nhật nhân sự" : "Thêm nhân sự" }}
        </h2>

        <form @submit.prevent="saveUser">
            <div class="mb-3">
                <label>Họ tên</label>
                <input v-model="form.name" class="border p-2 w-full rounded" />
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input v-model="form.email" class="border p-2 w-full rounded" />
            </div>

            <div class="mb-3">
                <label>Mật khẩu</label>
                <input
                    type="password"
                    v-model="form.password"
                    class="border p-2 w-full rounded"
                />
            </div>

            <div class="mb-3">
                <label>Vai trò</label>

                <select
                    multiple
                    v-model="form.roles"
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

const props = defineProps({
    user: Object,
});

const emit = defineEmits(["saved", "close"]);

const roles = ref([]);

const form = reactive({
    name: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    role_id: [],
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
                role_id: value.roles ? value.roles.map((r) => r.name) : [],
            });
        } else {
            Object.assign(form, {
                name: "",
                username: "",
                email: "",
                phone: "",
                password: "",
                role_id: [],
            });
        }
    },
    { immediate: true },
);

const getRoles = async () => {
    const response = await axios.get("/api/roles");

    roles.value = response.data;
};

const saveUser = async () => {
    await axios.post("/api/users", form);

    emit("saved");
};

onMounted(() => {
    getRoles();
});
</script>
