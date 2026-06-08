<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-8">
            <!-- HEADER -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">Tạo công ty</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Thiết lập thông tin doanh nghiệp của bạn
                </p>
            </div>

            <form @submit.prevent="saveCompany">
                <!-- GRID -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700"
                            >Tên công ty</label
                        >
                        <input
                            v-model="form.name"
                            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            placeholder="VD: Công ty ABC"
                        />
                        <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                            {{ errors.name[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700"
                            >Mã số thuế</label
                        >
                        <input
                            v-model="form.tax_code"
                            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        />
                        <p
                            v-if="errors.tax_code"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.tax_code[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700"
                            >Email</label
                        >
                        <input
                            v-model="form.email"
                            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        />
                        <p
                            v-if="errors.email"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.email[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700"
                            >Số điện thoại</label
                        >
                        <input
                            v-model="form.phone"
                            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        />
                        <p
                            v-if="errors.phone"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.phone[0] }}
                        </p>
                    </div>
                </div>

                <!-- ADDRESS FULL -->
                <div class="mt-4">
                    <label class="text-sm font-medium text-gray-700"
                        >Địa chỉ</label
                    >
                    <input
                        v-model="form.address"
                        class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    />
                    <p v-if="errors.address" class="text-red-500 text-xs mt-1">
                        {{ errors.address[0] }}
                    </p>
                </div>

                <!-- CURRENCY -->
                <div class="mt-4">
                    <label class="text-sm font-medium text-gray-700">
                        Loại tiền tệ
                    </label>

                    <select
                        v-model="form.currency_id"
                        class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option
                            v-for="c in currencies"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.symbol }} - {{ c.name }}
                        </option>
                    </select>

                    <p
                        v-if="errors.currency_id"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ errors.currency_id[0] }}
                    </p>
                </div>

                <!-- BUTTON -->
                <div class="mt-6">
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-md"
                    >
                        Hoàn tất tạo công ty
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import axios from "axios";
const props = defineProps({ currencies: Array, defaultCurrencyId: Number });
const errors = ref({});
// giống "useForm style"
const form = reactive({
    name: "",
    tax_code: "",
    email: "",
    phone: "",
    address: "",
    currency_id: props.defaultCurrencyId || null,
});
// submit giống user form
const saveCompany = async () => {
    errors.value = {};
    try {
        await axios.post("/company", form);
        window.location.href = "/dashboard";
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            alert("Có lỗi xảy ra");
        }
    }
};
</script>
