<template>
    <Head title="Hồ sơ cá nhân" />
    <AdminLayout>
        <div class="mx-auto max-w-2xl rounded-xl bg-white p-6 shadow-sm">
            <h1 class="mb-6 text-xl font-semibold">Hồ sơ cá nhân</h1>
            <form class="space-y-4" @submit.prevent="submit">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Họ tên</span>
                    <input v-model="form.name" class="w-full rounded-lg border px-3 py-2" required />
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Email</span>
                    <input v-model="form.email" type="email" class="w-full rounded-lg border px-3 py-2" required />
                </label>
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white" type="submit">
                    Lưu thay đổi
                </button>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({ user: Object });
const form = useForm({ name: props.user?.name ?? "", email: props.user?.email ?? "" });
const submit = () => form.patch("/profile");
</script>
