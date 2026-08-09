<script setup lang="ts">
import { computed } from 'vue';
import Loader from '@/components/Loader.vue';

defineOptions({ inheritAttrs: false });

type ButtonVariant = 'fill' | 'outline' | 'link';
type ButtonSize = 'large' | 'medium' | 'small' | 'full';
type ButtonColor = 'primary' | 'secondary';

const { size = 'medium', variant = 'fill', color = 'primary', isLoading = false, disabled = false } = defineProps<{
 size?: ButtonSize;
 variant?: ButtonVariant;
 color?: ButtonColor;
 isLoading?: boolean;
 disabled?: boolean;
}>();

const baseClasses =
 'inline-flex items-center justify-center gap-2 font-semibold transition-colors focus:outline-none focus-visible:ring-2 active:scale-95 duration-200 ease-in cursor-pointer disabled:cursor-not-allowed';

const classes = computed(() => [
  baseClasses,
  {
    // Sizes
    'h-[52px] px-6 text-[15px] rounded-xl': size === 'large',
    'h-[44px] px-5 text-[14px] rounded-lg': size === 'medium',
    'h-[36px] px-4 text-[13px] rounded-md': size === 'small',
    'w-full text-center h-[52px] text-[15px] rounded-xl': size === 'full',

    // Primary Variants
    'bg-primary-default text-white hover:bg-primary-hover disabled:bg-gray-200 disabled:hover:bg-gray-200 disabled:text-gray-400':
      variant === 'fill' && color === 'primary',
    'bg-transparent text-primary-default border border-primary-default hover:bg-primary-bg disabled:border-gray-200 disabled:hover:bg-transparent disabled:text-gray-400':
      variant === 'outline' && color === 'primary',
    'bg-transparent text-primary-default hover:text-primary-hover hover:underline disabled:text-gray-400 disabled:no-underline disabled:hover:text-gray-400':
      variant === 'link' && color === 'primary',

    // Secondary Variants
    'bg-gray-400 dark:bg-gray-600 text-white hover:bg-gray-500 dark:hover:bg-gray-500 disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:hover:bg-gray-200 dark:disabled:hover:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600':
      variant === 'fill' && color === 'secondary',
    'bg-transparent text-gray-500 dark:text-gray-300 border border-gray-500 dark:border-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:border-gray-200 dark:disabled:border-gray-700 disabled:hover:bg-transparent disabled:text-gray-400 dark:disabled:text-gray-600':
      variant === 'outline' && color === 'secondary',
    'bg-transparent text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 hover:underline disabled:text-gray-400 dark:disabled:text-gray-600 disabled:no-underline disabled:hover:text-gray-400 dark:disabled:hover:text-gray-600':
      variant === 'link' && color === 'secondary',
  },
]);
</script>

<template>
  <button
    v-bind="$attrs"
    :class=" classes"
    :aria-busy="isLoading"
    :disabled="isLoading || disabled"
  >
    <Loader
      v-if="isLoading"
      :color="variant === 'outline' || variant === 'link' ? color : 'white'"
      class="w-4 h-4"
    />
    <template v-else>
      <slot name="left-icon" />
      <slot />
      <slot name="right-icon" />
    </template>
  </button>
</template>
