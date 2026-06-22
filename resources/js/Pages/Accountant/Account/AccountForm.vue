<template>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-5 z-50">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold">
                {{ form.id ? "Cập nhật tài khoản" : "Thêm tài khoản" }}
            </h2>

            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-red-500"
            >
                ✕
            </button>
        </div>

        <form @submit.prevent="saveAccount">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- CODE -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Mã tài khoản
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        v-model="form.code"
                        :disabled="!!form.id"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <p v-if="errors.code" class="text-red-500 text-sm">
                        {{ errors.code[0] }}
                    </p>
                </div>

                <!-- NAME -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tên tài khoản
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        v-model="form.name"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <p v-if="errors.name" class="text-red-500 text-sm">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <!-- TYPE -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Loại tài khoản
                    </label>

                    <select
                        v-model="form.type"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option value="cash">Tiền mặt</option>

                        <option value="bank">Ngân hàng</option>

                        <option value="ewallet">Ví điện tử</option>

                        <option value="other">Khác</option>
                    </select>
                </div>

                <!-- CURRENCY -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tiền tệ
                    </label>

                    <FormSelect
                        v-model="form.currency_id"
                        :options="currencyOptions"
                        searchable
                    />

                    <p v-if="errors.currency_id" class="text-red-500 text-sm">
                        {{ errors.currency_id[0] }}
                    </p>
                </div>

                <!-- OPENING -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Số dư đầu kỳ
                    </label>

                    <input
                        v-model="form.opening_balance"
                        type="number"
                        class="w-full border rounded-lg px-3 py-2"
                    />
                </div>

                <!-- ACTIVE -->
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Trạng thái
                    </label>

                    <select
                        v-model="form.is_active"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option :value="true">Hoạt động</option>

                        <option :value="false">Khóa</option>
                    </select>
                </div>
            </div>

            <!-- BANK -->
            <div
                v-if="form.type === 'bank'"
                class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4"
            >
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Ngân hàng
                    </label>

                    <FormSelect
                        v-model="form.bank_id"
                        :options="bankOptions"
                        searchable
                    />

                    <p v-if="errors.bank_id" class="text-red-500 text-sm">
                        {{ errors.bank_id[0] }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Số tài khoản
                    </label>

                    <input
                        v-model="form.bank_account_no"
                        class="w-full border rounded-lg px-3 py-2"
                    />

                    <p
                        v-if="errors.bank_account_no"
                        class="text-red-500 text-sm"
                    >
                        {{ errors.bank_account_no[0] }}
                    </p>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 mt-5">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 border rounded-lg"
                >
                    Hủy
                </button>

                <button
                    type="submit"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    {{ form.id ? "Cập nhật" : "Lưu" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, watch, computed, onMounted } from "vue";
import { toast } from "vue3-toastify";

import FormSelect from "@/components/FormSelect.vue";

const props = defineProps({
    account: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["saved", "close"]);

const errors = ref({});

const currencies = ref([]);
const banks = ref([]);

const form = ref({
    id: null,
    code: "",
    name: "",
    type: "cash",
    currency_id: "",
    opening_balance: 0,
    bank_id: null,
    bank_account_no: "",
    is_active: true,
});

const currencyOptions = computed(() =>
    currencies.value.map((c) => ({
        value: c.id,
        label: `${c.code} - ${c.name}`,
    })),
);

const bankOptions = computed(() =>
    banks.value.map((b) => ({
        value: b.id,
        label: b.name,
    })),
);

watch(
    () => props.account,
    (a) => {
        errors.value = {};

        if (!a) {
            form.value = {
                id: null,
                code: "",
                name: "",
                type: "cash",
                currency_id: "",
                opening_balance: 0,
                bank_id: null,
                bank_account_no: "",
                is_active: true,
            };

            return;
        }

        form.value = {
            id: a.id,
            code: a.code,
            name: a.name,
            type: a.type,
            currency_id: a.currency_id,
            opening_balance: a.opening_balance,
            bank_id: a.bank_id,
            bank_account_no: a.bank_account_no,
            is_active: a.is_active,
        };
    },
    {
        immediate: true,
    },
);

async function saveAccount() {
    errors.value = {};

    try {
        if (form.value.id) {
            await axios.put(
                `/api/accountant/accounts/${form.value.id}`,
                form.value,
            );

            toast.success("Cập nhật tài khoản thành công");
        } else {
            await axios.post("/api/accountant/accounts", form.value);

            toast.success("Thêm tài khoản thành công");
        }

        emit("saved");
        emit("close");
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};

            if (error.response.data.message) {
                toast.error(error.response.data.message);
            }
        } else {
            toast.error("Có lỗi xảy ra");
        }
    }
}

async function loadData() {
    const [currencyRes, bankRes] = await Promise.all([
        axios.get("/api/accountant/currencies"),
        axios.get("/api/accountant/banks"),
    ]);

    currencies.value = currencyRes.data.data ?? currencyRes.data;

    banks.value = bankRes.data.data ?? bankRes.data;
}

onMounted(loadData);
</script>
