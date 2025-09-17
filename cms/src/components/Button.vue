<script setup lang="ts">
import { computed } from 'vue';
import Loader from '@/components/Loader.vue';

defineOptions({ inheritAttrs: false });

type ButtonColor = 'primary' | 'secondary' | 'success' | 'danger' | 'outline';
type ButtonSize = '' | 'small' | 'full';

const { size = '', color = 'primary', isLoading = false } = defineProps<{
  size?: ButtonSize;
  color?: ButtonColor;
  isLoading?: boolean;
}>();

const baseClasses =
  'flex items-center justify-center text-base font-semibold rounded-xl cursor-pointer transition-colors';

const classes = computed(() => [
  baseClasses,
  'focus:outline-none focus-visible:ring-2 transition-all duration-200 ease-in active:scale-95',
  {
    // Primary
    'py-3.5 px-5 bg-primary text-white hover:bg-primary-hover focus-visible:ring-primary-focus focus-visible:bg-primary active:bg-primary-pressed disabled:bg-disabled disabled:cursor-not-allowed':
      color === 'primary',
    'dark:bg-primary-dark dark:hover:bg-primary-hover-dark dark:focus-visible:ring-primary-focus-dark dark:focus-visible:bg-primary-dark dark:active:bg-primary-pressed-dark dark:disabled:bg-disabled-dark':
      color === 'primary',

    // Secondary
    'py-3.5 px-5 bg-disabled/30 hover:bg-disabled/40 text-font focus-visible:ring-secondary focus-visible:bg-disabled/30 active:bg-disabled/40 disabled:bg-disabled disabled:cursor-not-allowed':
      color === 'secondary',
    'dark:bg-disabled-dark/40 dark:hover:bg-disabled-dark/50 dark:text-font-dark dark:focus-visible:ring-secondary-dark dark:focus-visible:bg-disabled-dark/30 dark:active:bg-disabled-dark/40 dark:disabled:bg-disabled-dark':
      color === 'secondary',

    // Success
    'py-3.5 px-5 bg-success text-white hover:bg-success-hover focus-visible:ring-success-focus focus-visible:bg-success active:bg-success-pressed disabled:bg-disabled disabled:cursor-not-allowed':
      color === 'success',
    'dark:bg-success-dark dark:hover:bg-success-hover-dark dark:focus-visible:ring-success-focus-dark dark:focus-visible:bg-success-dark dark:active:bg-success-pressed-dark dark:disabled:bg-disabled-dark':
      color === 'success',

    // Danger
    'py-3.5 px-5 bg-danger text-white hover:bg-danger-hover focus-visible:ring-danger-focus focus-visible:bg-danger active:bg-danger-pressed disabled:bg-disabled disabled:cursor-not-allowed':
      color === 'danger',
    'dark:bg-danger-dark dark:hover:bg-danger-hover-dark dark:focus-visible:ring-danger-focus-dark dark:focus-visible:bg-danger-dark dark:active:bg-danger-pressed-dark dark:disabled:bg-disabled-dark':
      color === 'danger',

    // Outline
    'py-3.5 px-5 bg-transparent text-primary border-2 border-primary focus-visible:ring-primary-active':
      color === 'outline',
    'dark:text-primary-dark dark:border-primary-dark dark:focus-visible:ring-primary-focus-dark':
      color === 'outline',

    // Size modifiers
    'p-2': size === 'small',
    'w-full text-center': size === 'full',
  },
]);
</script>

<template>
  <button
    v-bind="$attrs"
    :class="classes"
    :aria-busy="isLoading"
    :disabled="isLoading"
  >
    <Loader v-if="isLoading" class="w-4 h-4" />
    <slot v-else />
  </button>
</template>
