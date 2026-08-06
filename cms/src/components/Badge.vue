<script setup lang="ts">
type colorType = 'success' | 'warning' | 'danger';

const { label, color } = defineProps<{
 label: string;
 color: colorType;
}>();

const colorClasses = {
  success: 'bg-[var(--color-success-accent)] text-[var(--color-success)] dark:bg-[var(--color-success-accent-dark)] dark:text-[var(--color-gray-100)]',
  warning: 'bg-[var(--color-warning-accent)] text-[var(--color-warning-focus)] dark:bg-[var(--color-warning-accent-dark)] dark:text-[var(--color-gray-100)]',
  danger: 'bg-[var(--color-danger-accent)] text-[var(--color-danger)] dark:bg-[var(--color-danger-accent-dark)] dark:text-[var(--color-gray-100)]',
};
</script>

<template>
  <span
    :class="[
      'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium truncate whitespace-nowrap overflow-hidden',
      colorClasses[color]
    ]"
  >
    {{ label }}
  </span>
</template>
