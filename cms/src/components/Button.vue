<script setup lang="ts">
import { computed } from 'vue';
import Loader from '@/components/Loader.vue';

defineOptions({ inheritAttrs: false });

type ButtonVariant = 'fill' | 'outline' | 'link';
type ButtonSize = 'large' | 'medium' | 'small' | 'full';

const { size = 'medium', variant = 'fill', isLoading = false, disabled = false } = defineProps<{
 size?: ButtonSize;
 variant?: ButtonVariant;
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

    // Variants
    'bg-[var(--color-primary-default)] text-white hover:bg-[var(--color-primary-dark)] disabled:bg-gray-200 disabled:hover:bg-gray-200 disabled:text-gray-400':
 variant === 'fill',
    'bg-transparent text-[var(--color-primary-default)] border border-[var(--color-primary-default)] hover:bg-primary-bg disabled:border-gray-200 disabled:hover:bg-transparent disabled:text-gray-400':
 variant === 'outline',
    'bg-transparent text-[var(--color-primary-default)] hover:text-[var(--color-primary-dark)] hover:underline disabled:text-gray-400 disabled:no-underline disabled:hover:text-gray-400':
 variant === 'link',
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
      :color="variant === 'outline' || variant === 'link' ? 'primary' : 'white'"
      class="w-4 h-4"
    />
    <template v-else>
      <slot name="left-icon" />
      <slot />
      <slot name="right-icon" />
    </template>
  </button>
</template>
