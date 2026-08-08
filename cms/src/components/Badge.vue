<script setup lang="ts">
type colorType = 'success' | 'warning' | 'danger';

const { label, color } = defineProps<{
 label: string;
 color: colorType;
}>();

const colorClasses = {
  success: 'bg-success-accent dark:bg-success-accent-dark text-success dark:text-success-dark',
  warning: 'bg-warning-accent dark:bg-warning-accent-dark text-warning-focus dark:text-warning-dark',
  danger: 'bg-danger-accent dark:bg-danger-accent-dark text-danger dark:text-danger-dark',
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
