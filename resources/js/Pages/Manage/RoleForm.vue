<template>
    <div class="bg-white rounded-xl p-6 w-[600px] z-50">
        <h2 class="font-bold text-xl mb-5">
            {{ role ? "Sửa vai trò" : "Thêm vai trò" }}
        </h2>

        <form @submit.prevent="save">
            <div class="mb-3">
                <label>Tên vai trò</label>

                <input v-model="form.name" class="border p-2 w-full" />
            </div>

            <div class="mb-5">
                <label>Quyền</label>

                <div
                    v-for="permission in permissions"
                    :key="permission.id"
                    class="mb-2"
                >
                    <label>
                        <input
                            type="checkbox"
                            :value="permission.name"
                            v-model="form.permissions"
                        />

                        {{ permission.name }}
                    </label>
                </div>
            </div>

            <button class="bg-blue-500 text-white px-4 py-2 rounded">
                Lưu
            </button>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { reactive, ref, onMounted } from "vue";

const props = defineProps({
    role: Object,
});

const emit = defineEmits(["saved", "close"]);

const permissions = ref([]);

const form = reactive({
    name: "",
    permissions: [],
});

onMounted(async () => {
    const res = await axios.get("/api/permissions/all");

    permissions.value = res.data;

    if (props.role) {
        form.name = props.role.name;

        form.permissions = props.role.permissions.map((x) => x.name);
    }
});

async function save() {
    if (props.role) {
        await axios.put(`/api/roles/${props.role.id}`, form);
    } else {
        await axios.post("/api/roles", form);
    }

    emit("saved");

    emit("close");
}
</script>
