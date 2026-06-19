<template>
    <div
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
        <div
            class="bg-white w-full max-w-5xl rounded-xl shadow-xl p-6 relative max-h-[90vh] overflow-y-auto"
        >
            <!-- CLOSE -->
            <button
                class="absolute top-4 right-4 text-2xl text-gray-500 hover:text-red-500"
                @click="$emit('close')"
            >
                ✕
            </button>

            <!-- LOADING -->
            <div v-if="loading" class="text-center py-10">
                Đang tải dữ liệu user...
            </div>

            <div v-else-if="user">
                <!-- HEADER -->
                <div class="border-b pb-4 mb-6">
                    <h2 class="text-3xl font-bold text-gray-900">
                        {{ user.name }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ user.email }} • {{ user.phone || "-" }}
                    </p>

                    <span
                        class="px-4 py-1 text-xs rounded-full font-semibold"
                        :class="statusClass(user.status)"
                    >
                        {{ statusText(user.status) }}
                    </span>
                </div>

                <!-- INFO GRID -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400">Username</p>
                        <p class="font-semibold">{{ user.username }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400">Role</p>
                        <p class="font-semibold">
                            {{
                                user.roles?.map((r) => r.name).join(", ") || "-"
                            }}
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-400">Company</p>
                        <p class="font-semibold">
                            {{ user.company_id || "-" }}
                        </p>
                    </div>
                </div>

                <!-- ACTIVITY LOG -->
                <div class="bg-white border rounded-xl p-4 shadow-sm">
                    <h3 class="font-bold mb-4">Activity Timeline</h3>

                    <div class="border-l-2 pl-4 space-y-4">
                        <div
                            v-for="log in user.logs"
                            :key="log.id"
                            class="flex gap-3"
                        >
                            <div
                                class="w-2.5 h-2.5 mt-2 rounded-full bg-blue-500"
                            ></div>

                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ log.user?.name || "System" }}
                                </p>

                                <p class="text-sm text-gray-700">
                                    <span class="font-bold">
                                        {{ log.action }}
                                    </span>
                                    - {{ log.description }}
                                </p>

                                <p class="text-xs text-gray-400">
                                    {{ formatDate(log.created_at) }}
                                </p>

                                <!-- detail changes -->
                                <div
                                    v-if="log.new_values || log.old_values"
                                    class="text-xs mt-1 text-gray-500"
                                >
                                    <div v-if="log.old_values">
                                        Old: {{ log.old_values }}
                                    </div>
                                    <div v-if="log.new_values">
                                        New: {{ log.new_values }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-10 text-gray-500">
                Không có dữ liệu user
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import axios from "axios";

const props = defineProps({
    userId: Number,
});

const emit = defineEmits(["close"]);

const user = ref(null);
const loading = ref(false);

watch(
    () => props.userId,
    async (id) => {
        if (!id) return;

        loading.value = true;
        user.value = null;

        try {
            const res = await axios.get(`/api/users/user/${id}`);
            user.value = res.data;
        } finally {
            loading.value = false;
        }
    },
    { immediate: true },
);

function formatDate(date) {
    if (!date) return "-";
    return new Date(date).toLocaleString("vi-VN");
}

function statusText(s) {
    return (
        {
            active: "Hoạt động",
            inactive: "Không hoạt động",
            blocked: "Bị khóa",
            pending: "Chờ xác nhận",
        }[s] || "-"
    );
}

function statusClass(s) {
    return (
        {
            active: "bg-green-100 text-green-700",
            inactive: "bg-gray-100 text-gray-700",
            blocked: "bg-red-100 text-red-700",
            pending: "bg-yellow-100 text-yellow-700",
        }[s] || ""
    );
}
</script>
