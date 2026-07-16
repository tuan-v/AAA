<template>
    <div class="bg-white rounded-xl w-[640px] max-h-[85vh] flex flex-col z-50">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b">
            <h2 class="font-bold text-xl">
                {{ role ? "Sửa vai trò" : "Thêm vai trò" }}
            </h2>
            <button
                @click="$emit('close')"
                type="button"
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form @submit.prevent="save" class="flex flex-col flex-1 min-h-0">
            <!-- Body (scrollable) -->
            <div class="px-6 py-4 overflow-y-auto flex-1 min-h-0">
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1"
                        >Tên vai trò</label
                    >
                    <input
                        v-model="form.name"
                        class="border p-2 w-full rounded"
                    />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Mô tả</label>
                    <input
                        v-model="form.description"
                        class="border p-2 w-full rounded"
                    />
                    <p
                        v-if="errors.description"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ errors.description[0] }}
                    </p>
                </div>

                <!-- Quyền -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium">Quyền</label>
                        <span class="text-xs text-gray-500">
                            Đã chọn {{ form.permissions.length }}/{{
                                permissions.length
                            }}
                        </span>
                    </div>

                    <!-- Search + select all tổng -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="relative flex-1">
                            <i
                                class="ti ti-search absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm"
                            ></i>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Tìm quyền..."
                                class="border p-2 pl-7 w-full rounded text-sm"
                            />
                        </div>
                        <button
                            type="button"
                            @click="toggleAll"
                            class="text-xs px-3 py-2 border rounded whitespace-nowrap hover:bg-gray-50"
                        >
                            {{
                                isAllSelected ? "Bỏ chọn tất cả" : "Chọn tất cả"
                            }}
                        </button>
                    </div>

                    <p
                        v-if="errors.permissions"
                        class="text-red-500 text-xs mb-2"
                    >
                        {{ errors.permissions[0] }}
                    </p>

                    <!-- Danh sách nhóm -->
                    <div
                        class="border rounded-lg divide-y max-h-[320px] overflow-y-auto"
                    >
                        <div
                            v-for="group in filteredGroups"
                            :key="group.name"
                            class="p-3"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span
                                    class="text-sm font-semibold text-gray-700 capitalize"
                                >
                                    {{ group.name }}
                                </span>
                                <button
                                    type="button"
                                    @click="toggleGroup(group)"
                                    class="text-xs text-blue-600 hover:underline"
                                >
                                    {{
                                        isGroupSelected(group)
                                            ? "Bỏ chọn nhóm"
                                            : "Chọn nhóm"
                                    }}
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
                                <label
                                    v-for="permission in group.items"
                                    :key="permission.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="permission.name"
                                        v-model="form.permissions"
                                    />
                                    {{ permission.name }}
                                </label>
                            </div>
                        </div>

                        <div
                            v-if="filteredGroups.length === 0"
                            class="p-4 text-sm text-gray-400 text-center"
                        >
                            Không tìm thấy quyền phù hợp
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t flex justify-end gap-2">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 rounded border text-gray-600 hover:bg-gray-50"
                >
                    Hủy
                </button>
                <button
                    :disabled="saving"
                    class="bg-blue-500 text-white px-4 py-2 rounded disabled:opacity-50"
                >
                    {{ saving ? "Đang lưu..." : "Lưu" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { reactive, ref, computed, onMounted } from "vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const errors = ref({});
const saving = ref(false);
const search = ref("");

const props = defineProps({
    role: Object,
});

const emit = defineEmits(["saved", "close"]);

const permissions = ref([]);

const form = reactive({
    name: "",
    description: "",
    permissions: [],
});

// Nhóm quyền theo tiền tố trước dấu "." (vd: "users.view" -> nhóm "users")
// Nếu quyền không có dấu "." thì gom vào nhóm "Khác"
const groupedPermissions = computed(() => {
    const groups = {};

    for (const permission of permissions.value) {
        const [prefix] = permission.name.includes(".")
            ? permission.name.split(".")
            : ["Khác"];

        if (!groups[prefix]) {
            groups[prefix] = [];
        }

        groups[prefix].push(permission);
    }

    return Object.entries(groups).map(([name, items]) => ({
        name,
        items,
    }));
});

const filteredGroups = computed(() => {
    if (!search.value.trim()) {
        return groupedPermissions.value;
    }

    const keyword = search.value.trim().toLowerCase();

    return groupedPermissions.value
        .map((group) => ({
            name: group.name,
            items: group.items.filter((p) =>
                p.name.toLowerCase().includes(keyword),
            ),
        }))
        .filter((group) => group.items.length > 0);
});

const isAllSelected = computed(
    () =>
        permissions.value.length > 0 &&
        form.permissions.length === permissions.value.length,
);

function isGroupSelected(group) {
    return group.items.every((p) => form.permissions.includes(p.name));
}

function toggleGroup(group) {
    const groupNames = group.items.map((p) => p.name);

    if (isGroupSelected(group)) {
        form.permissions = form.permissions.filter(
            (name) => !groupNames.includes(name),
        );
    } else {
        const merged = new Set([...form.permissions, ...groupNames]);
        form.permissions = Array.from(merged);
    }
}

function toggleAll() {
    if (isAllSelected.value) {
        form.permissions = [];
    } else {
        form.permissions = permissions.value.map((p) => p.name);
    }
}

onMounted(async () => {
    const res = await axios.get("/api/permissions/all");

    permissions.value = res.data;

    if (props.role) {
        form.name = props.role.name;
        form.description = props.role.description;
        form.permissions = props.role.permissions.map((x) => x.name);
    }
});

async function save() {
    errors.value = {};
    saving.value = true;

    try {
        if (props.role?.id) {
            await axios.put(`/api/roles/${props.role.id}`, form);
        } else {
            await axios.post("/api/roles", form);
        }

        toast.success("Lưu vai trò thành công!", {
            position: "top-right",
            autoClose: 3000,
        });

        emit("saved");
        emit("close");
    } catch (error) {
        if (error.response && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error(error);
        }
    } finally {
        saving.value = false;
    }
}
</script>
