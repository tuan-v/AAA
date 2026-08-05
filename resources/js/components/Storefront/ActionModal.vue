<template>
    <Teleport to="body">
        <Transition name="storefront-modal">
            <div v-if="open" class="fixed inset-0 z-[100] grid place-items-center bg-black/55 p-4 backdrop-blur-sm" @mousedown.self="requestClose">
                <section class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl" role="dialog" aria-modal="true" :aria-labelledby="titleId">
                    <div class="p-6 sm:p-7">
                        <div class="flex items-start gap-4">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full" :class="toneClasses.icon">
                                <span class="text-xl font-black">{{ tone === 'danger' ? '!' : '?' }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 :id="titleId" class="text-xl font-black tracking-[-0.03em] text-slate-950">{{ title }}</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-500">{{ message }}</p>
                            </div>
                            <button type="button" :disabled="loading" class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-40" aria-label="Đóng" @click="requestClose">×</button>
                        </div>

                        <div v-if="requireReason" class="mt-5">
                            <label class="text-sm font-bold text-slate-800">Lý do <span class="text-red-600">*</span></label>
                            <div v-if="reasonOptions.length" class="mt-3 grid gap-2">
                                <label v-for="option in reasonOptions" :key="option.value" class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm transition" :class="selectedReason === option.value ? 'border-red-300 bg-red-50 font-semibold text-red-800 ring-2 ring-red-100' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'">
                                    <input v-model="selectedReason" type="radio" :value="option.value" class="h-4 w-4 accent-red-600" />
                                    <span>{{ option.label }}</span>
                                </label>
                            </div>
                            <template v-if="!reasonOptions.length || selectedReason === otherReasonValue">
                                <textarea ref="reasonInput" v-model.trim="reason" rows="3" maxlength="500" class="mt-3 w-full resize-none rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-red-400 focus:ring-4 focus:ring-red-50" :placeholder="reasonPlaceholder"></textarea>
                                <div class="mt-1.5 flex justify-between text-xs text-slate-400">
                                    <span>Tối thiểu 5 ký tự</span><span>{{ reason.length }}/500</span>
                                </div>
                            </template>
                        </div>

                        <p v-if="localError || error" class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ localError || error }}</p>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" :disabled="loading" class="rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 disabled:opacity-50" @click="requestClose">Quay lại</button>
                        <button type="button" :disabled="loading" class="rounded-full px-5 py-2.5 text-sm font-black text-white transition disabled:cursor-not-allowed disabled:opacity-50" :class="toneClasses.button" @click="confirmAction">
                            {{ loading ? loadingText : confirmText }}
                        </button>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, nextTick, ref, watch } from "vue";

const props = defineProps({
    open: Boolean,
    title: { type: String, required: true },
    message: { type: String, default: "" },
    confirmText: { type: String, default: "Xác nhận" },
    loadingText: { type: String, default: "Đang xử lý..." },
    loading: Boolean,
    error: { type: String, default: "" },
    tone: { type: String, default: "danger" },
    requireReason: Boolean,
    reasonPlaceholder: { type: String, default: "Nhập lý do..." },
    reasonOptions: { type: Array, default: () => [] },
    otherReasonValue: { type: String, default: "other" },
});
const emit = defineEmits(["close", "confirm"]);
const reason = ref("");
const selectedReason = ref("");
const localError = ref("");
const reasonInput = ref(null);
const titleId = `storefront-action-title-${Math.random().toString(36).slice(2)}`;
const toneClasses = computed(() => props.tone === "danger"
    ? { icon: "bg-red-100 text-red-700", button: "bg-red-600 hover:bg-red-700" }
    : { icon: "bg-indigo-100 text-indigo-700", button: "bg-indigo-600 hover:bg-indigo-700" });

watch(() => props.open, async (open) => {
    if (!open) return;
    reason.value = "";
    selectedReason.value = "";
    localError.value = "";
    if (props.requireReason) {
        await nextTick();
        reasonInput.value?.focus();
    }
});
watch(selectedReason, async (value) => {
    localError.value = "";
    if (value === props.otherReasonValue) {
        await nextTick();
        reasonInput.value?.focus();
    }
});

function requestClose() {
    if (!props.loading) emit("close");
}
function confirmAction() {
    let finalReason = reason.value;
    if (props.requireReason && props.reasonOptions.length) {
        if (!selectedReason.value) {
            localError.value = "Vui lòng chọn lý do.";
            return;
        }
        if (selectedReason.value !== props.otherReasonValue) {
            finalReason = props.reasonOptions.find((option) => option.value === selectedReason.value)?.label || selectedReason.value;
        }
    }
    if (props.requireReason && finalReason.length < 5) {
        localError.value = selectedReason.value === props.otherReasonValue
            ? "Vui lòng nhập lý do khác ít nhất 5 ký tự."
            : "Vui lòng chọn lý do.";
        return;
    }
    localError.value = "";
    emit("confirm", finalReason);
}
</script>

<style scoped>
.storefront-modal-enter-active,.storefront-modal-leave-active{transition:opacity .2s ease}.storefront-modal-enter-active section,.storefront-modal-leave-active section{transition:transform .2s ease,opacity .2s ease}.storefront-modal-enter-from,.storefront-modal-leave-to{opacity:0}.storefront-modal-enter-from section,.storefront-modal-leave-to section{transform:translateY(12px) scale(.98);opacity:0}
</style>
