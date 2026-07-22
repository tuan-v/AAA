<template>
  <div class="mb-5 flex flex-wrap items-end justify-between gap-3 border-b border-slate-200/70 pb-4 dark:border-gray-800">
    <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white/90">
      {{ title }}
    </h2>
    <nav>
      <ol class="flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-gray-800">
        <li>
          <Link
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
            href="/">
            Trang chủ
            <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </Link>
        </li>
        <li v-for="(item, index) in items" :key="index" class="flex items-center gap-1.5">
          <span v-if="index < items.length - 1" class="text-sm text-gray-500 dark:text-gray-400">
            <Link v-show="item.link"
              class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
              :href="item.link"> {{ item.text }}</Link>
            <svg class="stroke-current inline ml-1.5" width="17" height="16" viewBox="0 0 17 16" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <span v-else class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">
            {{ item.text }}
          </span>
        </li>
      </ol>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface BreadcrumbProps {
  title: string
  items?: string[]
}

defineProps<BreadcrumbProps>()
</script>
