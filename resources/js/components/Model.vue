<template>
  <teleport to="body">
    <div class="fixed inset-0 z-999 flex items-center justify-center px-4">
      <!-- Bóng đen -->
      <div class="fixed inset-0 bg-black/30" @click="$emit('close', false)" />

      <!-- Khung trắng -->
      <div id="modal-content"
        :class="['relative z-10 w-full bg-white dark:bg-gray-800 rounded-xl flex flex-col', modalSizeClass, 'max-h-[calc(100vh-5rem)]']">
        <!-- Header -->
        <div class="modal-header flex justify-between items-center content-padding">
          <div class="flex-1">
            <p class="text-lg font-medium text-gray-800 dark:text-gray-200">{{ header?.title || 'Title' }}</p>
          </div>
          <div>
            <button @click="$emit('close')" type="button"
              class="ml-4 p-2 text-gray-500 dark:text-gray-400 border bg-gray-50 dark:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div class="h-px bg-gray-200 dark:bg-gray-600"></div>

        <!-- Content -->
        <div class="content-padding flex-1 overflow-y-auto">
          <slot />
        </div>

        <div class="h-px bg-gray-200 dark:bg-gray-600"></div>

        <!-- Footer -->
        <div class="content-padding">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  header: { type: Object, default: () => ({}) },
  size: { type: String, default: 'md' }, // sm, md, lg, xl, full
  overFlowAuto: {type: Boolean, default: true}
});

defineEmits(['close']);

const modalSizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'max-w-md';
    case 'lg':
      return 'max-w-4xl';
    case 'xl':
      return 'max-w-6xl';
    case 'full':
      return 'max-w-full';
    case 'md':
    default:
      return 'max-w-2xl';
  }
});
</script>

<style scoped>
.content-padding {
  padding-top: 1rem;
  padding-bottom: 1rem;
  padding-left: 1.25rem;
  padding-right: 1.25rem;
}

#modal-content {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.05);
  animation: modalOpen 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.dark #modal-content {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.1);
}

@keyframes modalOpen {
  from {
    transform: scale(0.9) translateY(-20px);
    opacity: 0;
  }

  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}
</style>