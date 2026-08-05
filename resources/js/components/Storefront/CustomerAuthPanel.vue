<template>
    <section class="auth-shell" :class="{ 'is-register': mode === 'register' }">
        <div class="auth-welcome">
            <div class="auth-orb"></div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-[#c6ff3d]">
                    {{ mode === 'login' ? 'Khách hàng mới' : 'Đã là thành viên' }}
                </p>
                <h2 class="mt-7 text-4xl font-black leading-[0.95] tracking-[-0.045em] sm:text-5xl">
                    {{ mode === 'login' ? 'Chào mừng\nđến cửa hàng.' : 'Rất vui được\ngặp lại bạn.' }}
                </h2>
                <p class="mt-6 max-w-sm text-sm leading-6 text-neutral-400">
                    {{ mode === 'login'
                        ? `Tạo tài khoản tại ${storeName} để lưu địa chỉ và theo dõi đơn hàng thuận tiện hơn.`
                        : 'Đăng nhập để tiếp tục hành trình mua sắm và xem các đơn hàng của bạn.' }}
                </p>
                <button type="button" class="mt-8 rounded-full border border-white/40 px-7 py-3 text-sm font-black transition hover:border-[#c6ff3d] hover:text-[#c6ff3d]" @click="switchMode">
                    {{ mode === 'login' ? 'Tạo tài khoản' : 'Đăng nhập' }}
                </button>
            </div>
        </div>

        <div class="auth-form-wrap">
            <form v-if="mode === 'login'" class="mx-auto w-full max-w-md" @submit.prevent="login">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-neutral-400">Customer account</p>
                <h1 class="mt-3 text-4xl font-black tracking-[-0.04em]">Đăng nhập</h1>
                <p class="mt-3 text-sm text-neutral-500">Theo dõi đơn hàng và quản lý thông tin nhận hàng.</p>

                <label class="field-label">Email
                    <input v-model.trim="loginForm.email" type="email" autocomplete="email" required class="field-input" placeholder="ban@email.com" />
                </label>
                <label class="field-label">Mật khẩu
                    <span class="password-field">
                        <input v-model="loginForm.password" :type="showLoginPassword ? 'text' : 'password'" autocomplete="current-password" required class="field-input pr-16" placeholder="Nhập mật khẩu" />
                        <button type="button" class="password-toggle" @click="showLoginPassword = !showLoginPassword">{{ showLoginPassword ? 'Ẩn' : 'Hiện' }}</button>
                    </span>
                </label>
                <button class="submit-button" :disabled="submitting">{{ submitting ? 'Đang đăng nhập...' : 'Đăng nhập' }}</button>
            </form>

            <form v-else class="mx-auto w-full max-w-md" @submit.prevent="register">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-neutral-400">Create account</p>
                <h1 class="mt-3 text-4xl font-black tracking-[-0.04em]">Đăng ký</h1>
                <div class="mt-6 grid gap-x-3 sm:grid-cols-2">
                    <label class="field-label mt-0">Họ và tên<input v-model.trim="registerForm.name" required autocomplete="name" class="field-input" placeholder="Nguyễn Văn A" /></label>
                    <label class="field-label mt-0">Số điện thoại<input v-model.trim="registerForm.phone" required type="tel" inputmode="numeric" maxlength="10" pattern="0[35789][0-9]{8}" title="Nhập 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09" autocomplete="tel" class="field-input" placeholder="0912345678" /></label>
                </div>
                <label class="field-label">Email<input v-model.trim="registerForm.email" type="email" required autocomplete="email" class="field-input" placeholder="ban@email.com" /></label>
                <label class="field-label">Mật khẩu
                    <span class="password-field">
                        <input v-model="registerForm.password" :type="showRegisterPassword ? 'text' : 'password'" required minlength="8" autocomplete="new-password" class="field-input pr-16" placeholder="Tối thiểu 8 ký tự" />
                        <button type="button" class="password-toggle" @click="showRegisterPassword = !showRegisterPassword">{{ showRegisterPassword ? 'Ẩn' : 'Hiện' }}</button>
                    </span>
                </label>
                <label class="field-label">Nhập lại mật khẩu<input v-model="registerForm.password_confirmation" :type="showRegisterPassword ? 'text' : 'password'" required autocomplete="new-password" class="field-input" placeholder="Nhập lại mật khẩu" /></label>
                <p class="mt-4 text-xs leading-5 text-neutral-500">Bằng việc tạo tài khoản, bạn đồng ý với điều khoản mua hàng và chính sách bảo mật của cửa hàng.</p>
                <button class="submit-button" :disabled="submitting">{{ submitting ? 'Đang tạo tài khoản...' : 'Tạo tài khoản' }}</button>
            </form>
        </div>
    </section>
</template>

<script setup>
import axios from 'axios';
import { reactive, ref } from 'vue';
import { storefrontToast as toast } from '@/utils/storefrontToast';

const props = defineProps({ base: { type: String, required: true }, storeName: { type: String, required: true } });
const emit = defineEmits(['authenticated']);
const mode = ref('login');
const error = ref('');
const submitting = ref(false);
const showLoginPassword = ref(false);
const showRegisterPassword = ref(false);
const loginForm = reactive({ email: '', password: '' });
const registerForm = reactive({ name: '', phone: '', email: '', password: '', password_confirmation: '' });
const message = e => Object.values(e.response?.data?.errors || {}).flat()[0] || e.response?.data?.message || 'Có lỗi xảy ra. Vui lòng thử lại.';

function switchMode() {
    mode.value = mode.value === 'login' ? 'register' : 'login';
    error.value = '';
}

async function login() {
    submitting.value = true; error.value = '';
    try { await axios.post(`${props.base}/login`, loginForm); toast.success('Đăng nhập thành công.'); emit('authenticated'); }
    catch (e) { error.value = message(e); toast.error(error.value); }
    finally { submitting.value = false; }
}

async function register() {
    if (registerForm.password !== registerForm.password_confirmation) { error.value = 'Mật khẩu nhập lại chưa trùng khớp.'; toast.warning(error.value); return; }
    if (!/^0[35789][0-9]{8}$/.test(registerForm.phone)) { error.value = 'Số điện thoại phải gồm 10 chữ số và đúng đầu số di động Việt Nam.'; toast.warning(error.value); return; }
    submitting.value = true; error.value = '';
    try { await axios.post(`${props.base}/register`, registerForm); toast.success('Tạo tài khoản thành công.'); emit('authenticated'); }
    catch (e) { error.value = message(e); toast.error(error.value); }
    finally { submitting.value = false; }
}
</script>

<style scoped>
.auth-shell { overflow: hidden; border: 1px solid #e5e5e5; border-radius: 2rem; background: #fff; box-shadow: 0 24px 80px rgb(0 0 0 / 8%); }
.auth-welcome { position: relative; display: flex; overflow: hidden; padding: 2rem; color: #fff; background: #0a0a0a; transition: all .5s; }
.auth-form-wrap { display: flex; align-items: center; padding: 1.75rem; }
.auth-orb { position: absolute; right: -5rem; bottom: -7rem; width: 20rem; height: 20rem; border-radius: 999px; background: rgb(198 255 61 / 20%); filter: blur(64px); }
.field-label { display: block; margin-top: 1.25rem; color: #262626; font-size: .875rem; font-weight: 700; }
.field-input { display: block; width: 100%; margin-top: .5rem; padding: .875rem 1rem; border: 1px solid #d4d4d4; border-radius: .75rem; color: #0a0a0a; background: #fff; font-weight: 400; outline: none; transition: .2s; }
.field-input::placeholder { color: #a3a3a3; }
.field-input:focus { border-color: #0a0a0a; box-shadow: 0 0 0 2px rgb(10 10 10 / 10%); }
.password-field { position: relative; display: block; }
.password-toggle { position: absolute; top: calc(50% + .25rem); right: 1rem; transform: translateY(-50%); color: #737373; font-size: .75rem; font-weight: 900; letter-spacing: .05em; text-transform: uppercase; }
.password-toggle:hover { color: #0a0a0a; }
.submit-button { width: 100%; margin-top: 1.75rem; padding: 1rem; border-radius: .75rem; color: #fff; background: #0a0a0a; font-weight: 900; transition: .2s; }
.submit-button:hover { background: #262626; }
.submit-button:disabled { cursor: wait; opacity: .6; }
@media (min-width: 1024px) {
    .auth-shell { display: grid; min-height: 650px; grid-template-columns: 0.92fr 1.08fr; }
    .auth-welcome { padding: 3rem; border-radius: 0 7rem 7rem 0; }
    .auth-form-wrap { padding: 3rem; }
    .auth-shell.is-register { grid-template-columns: 1.08fr 0.92fr; }
    .auth-shell.is-register .auth-welcome { order: 2; border-radius: 7rem 0 0 7rem; }
    .auth-shell.is-register .auth-form-wrap { order: 1; }
}
@media (min-width: 768px) and (max-width: 1023px) {
    .auth-welcome, .auth-form-wrap { padding: 3rem; }
}
</style>
