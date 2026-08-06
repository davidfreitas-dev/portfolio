<script setup lang="ts">
type colorType = 'success' | 'warning' | 'danger';

const { label, color } = defineProps<{
 label: string;
 color: colorType;
}>();

const colorClasses = {
  success: 'bg-success-accent text-success dark:bg-success-accent-dark dark:text-gray-100',
  warning: 'bg-warning-accent text-warning-focus dark:bg-warning-accent-dark dark:text-gray-100',
  danger: 'bg-danger-accent text-danger dark:bg-danger-accent-dark dark:text-gray-100',
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
