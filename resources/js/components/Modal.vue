<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="show"
                class="modal fixed inset-0 z-9999 flex items-center justify-center overflow-y-auto px-4 py-6"
            >
                <!-- Overlay -->
                <div
                    class="fixed inset-0 h-full w-full bg-black/60 backdrop-blur-[2px]"
                    aria-hidden="true"
                    @click="$emit('close')"
                ></div>

                <!-- Content -->
                <Transition name="modal-scale" appear>
                    <div
                        v-if="show"
                        class="relative z-10 w-full flex justify-center"
                    >
                        <slot name="body"></slot>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

const emit = defineEmits(["close"]);

// show luôn true khi component được render (v-if ở component cha),
// giữ biến này để driving Transition mượt mà thay vì tắt đột ngột.
const show = ref(true);

function handleKeydown(e) {
    if (e.key === "Escape") {
        emit("close");
    }
}

function lockScroll() {
    document.documentElement.style.overflow = "hidden";
}

function unlockScroll() {
    document.documentElement.style.overflow = "";
}

onMounted(() => {
    lockScroll();
    window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
    unlockScroll();
    window.removeEventListener("keydown", handleKeydown);
});
</script>

<style>
/* ==================== TRANSITION HIỆU ỨNG ==================== */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-scale-enter-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.modal-scale-leave-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}
.modal-scale-enter-from,
.modal-scale-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(8px);
}

/* ==================== SCROLLBAR TÙY BIẾN CHO NỘI DUNG MODAL ==================== */
form .asfy-modal-scroll {
    scrollbar-width: thin;
    /* Firefox */
    scrollbar-color: rgba(0, 0, 0, 0.3) transparent;
    /* Firefox */
}

/* Chrome, Edge, Safari */
form .asfy-modal-scroll::-webkit-scrollbar {
    width: 6px;
    /* độ thon của thanh scroll */
}

form .asfy-modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}

form .asfy-modal-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.3);
    border-radius: 999px;
}

form .asfy-modal-scroll::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.5);
}

.asfy-modal-scroll {
    scrollbar-width: thin;
    /* Firefox */
    scrollbar-color: rgba(0, 0, 0, 0.3) transparent;
    /* Firefox */
}

/* Chrome, Edge, Safari */
.asfy-modal-scroll::-webkit-scrollbar {
    width: 6px;
    /* độ thon của thanh scroll */
}

.asfy-modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.asfy-modal-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.3);
    border-radius: 999px;
}

.asfy-modal-scroll::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>
