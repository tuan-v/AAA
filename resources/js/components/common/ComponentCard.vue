<template>
  <div
    :class="[
      'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm shadow-slate-950/[0.03] transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-white/[0.03]',
      className,
    ]"
  >
    <!-- Card Header -->
    <div class="px-5 py-4 sm:px-6">
      <h3 class="text-base font-bold text-slate-900 dark:text-white/90">
        {{ title }}
      </h3>
      <p v-if="desc" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ desc }}
      </p>
    </div>

    <!-- Card Body -->
    <div class="border-t border-slate-100 bg-slate-50/30 p-4 dark:border-gray-800 dark:bg-transparent sm:p-6">
      <div class="space-y-5">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'

interface Props {
  title: string
  className?: string
  desc?: string
}

defineProps<Props>()
</script>
