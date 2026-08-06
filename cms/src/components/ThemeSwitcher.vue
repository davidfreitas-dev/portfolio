<script setup lang="ts">
import { useDark } from '@vueuse/core';
import Icon from '@/components/Icon.vue';

defineProps<{
 isExpanded: boolean;
}>();

const isDark = useDark({
  selector: 'html',
  attribute: 'class',
  valueDark: 'dark',
  valueLight: '',
});

const setTheme = (dark: boolean) => {
  isDark.value = dark;
};
</script>

<template>
  <div :class="['mb-4 mx-auto transition-all duration-300', isExpanded ? 'w-full px-3' : 'w-[48px] px-0']">
    <div
      class="flex p-1 rounded-xl bg-gray-100 dark:bg-gray-700 transition-all duration-300"
      :class="isExpanded ? 'flex-row items-center' : 'flex-col items-center space-y-1'"
    >
      <!-- Light Button -->
      <button
        type="button"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg transition-all duration-200 cursor-pointer"
        :class="[
          isExpanded ? 'w-full px-3' : 'w-full px-0',
          !isDark 
            ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-700 dark:text-gray-100' 
            : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
        ]"
        @click="setTheme(false)"
      >
        <Icon name="light_mode" class="text-[18px]!" />
        <span v-if="isExpanded" class="text-[11px] font-bold uppercase tracking-wider">Claro</span>
      </button>

      <!-- Dark Button -->
      <button
        type="button"
        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg transition-all duration-200 cursor-pointer"
        :class="[
          isExpanded ? 'w-full px-3' : 'w-full px-0',
          isDark 
            ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-700 dark:text-gray-100' 
            : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
        ]"
        @click="setTheme(true)"
      >
        <Icon name="dark_mode" class="text-[18px]!" />
        <span v-if="isExpanded" class="text-[11px] font-bold uppercase tracking-wider">Escuro</span>
      </button>
    </div>
  </div>
</template>
