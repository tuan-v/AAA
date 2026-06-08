<template>
    <AdminLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Chi tiết nhân sự</h2>

                <button
                    class="px-4 py-2 border rounded"
                    @click="$router.back()"
                >
                    ← Quay lại
                </button>
            </div>

            <div
                v-if="user"
                class="bg-white p-6 rounded shadow grid grid-cols-2 gap-4"
            >
                <div>
                    <strong>Họ tên:</strong>
                    <p>{{ user.name }}</p>
                </div>

                <div>
                    <strong>Tên đăng nhập:</strong>
                    <p>{{ user.username }}</p>
                </div>

                <div>
                    <strong>Email:</strong>
                    <p>{{ user.email }}</p>
                </div>

                <div>
                    <strong>Số điện thoại:</strong>
                    <p>{{ user.phone }}</p>
                </div>

                <div>
                    <strong>Trạng thái:</strong>
                    <p>
                        <span
                            :class="statusClass(user.status)"
                            class="px-2 py-1 rounded"
                        >
                            {{ user.status }}
                        </span>
                    </p>
                </div>

                <div>
                    <strong>Vai trò:</strong>
                    <p>
                        <span
                            v-for="role in user.roles"
                            :key="role.id"
                            class="mr-2"
                        >
                            {{ role.name }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const user = ref(null);

const getUser = async () => {
    const res = await axios.get(`/api/users/${route.params.id}`);
    user.value = res.data;
};

function statusClass(status) {
    return {
        "bg-green-100 text-green-700": status === "active",
        "bg-red-100 text-red-700": status === "blocked",
        "bg-yellow-100 text-yellow-700": status === "pending",
        "bg-gray-100 text-gray-700": status === "inactive",
    };
}

onMounted(() => {
    getUser();
});
</script>
