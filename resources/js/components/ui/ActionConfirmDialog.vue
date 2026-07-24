<template>
    <Teleport to="body">
        <Transition name="action-confirm">
            <div v-if="state.visible" class="fixed inset-0 z-[100000] flex items-center justify-center p-4" @keydown.esc="cancel">
                <button class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm" aria-label="Đóng" @click="cancel"></button>
                <section class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl" role="alertdialog" aria-modal="true">
                    <div class="p-6 sm:p-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-xl font-bold" :class="tone.iconClass">{{ tone.icon }}</div>
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold text-slate-900">{{ state.title }}</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ state.message }}</p>
                            </div>
                        </div>

                        <div v-if="state.inputLabel" class="mt-5">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">{{ state.inputLabel }}</label>
                            <textarea v-model="state.inputValue" rows="3" :placeholder="state.inputPlaceholder" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:ring-2" :class="state.error ? 'border-red-400 focus:ring-red-100' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100'" autofocus></textarea>
                            <p v-if="state.error" class="mt-1.5 text-xs text-red-600">{{ state.error }}</p>
                        </div>
                        <div v-if="state.choiceLabel" class="mt-5">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">{{ state.choiceLabel }}</label>
                            <select v-model="state.choiceValue" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option v-for="option in state.choiceOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button v-if="state.mode !== 'alert'" type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100" @click="cancel">{{ state.cancelText }}</button>
                        <button type="button" class="rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm" :class="tone.buttonClass" @click="accept">{{ state.confirmText }}</button>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import { actionConfirmState as state, acceptActionConfirm as accept, cancelActionConfirm as cancel } from '@/composables/useActionConfirm';

const tone = computed(() => ({
    success: { icon: '✓', iconClass: 'bg-emerald-100 text-emerald-700', buttonClass: 'bg-emerald-600 hover:bg-emerald-700' },
    danger: { icon: '!', iconClass: 'bg-red-100 text-red-700', buttonClass: 'bg-red-600 hover:bg-red-700' },
    warning: { icon: '?', iconClass: 'bg-amber-100 text-amber-700', buttonClass: 'bg-amber-600 hover:bg-amber-700' },
})[state.tone] || { icon: '?', iconClass: 'bg-indigo-100 text-indigo-700', buttonClass: 'bg-indigo-600 hover:bg-indigo-700' });
</script>

<style scoped>
.action-confirm-enter-active, .action-confirm-leave-active { transition: opacity .18s ease; }
.action-confirm-enter-from, .action-confirm-leave-to { opacity: 0; }
</style>
