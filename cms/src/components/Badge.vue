<script setup lang="ts">
type colorType = 'primary' | 'success' | 'warning' | 'danger';

const { label, color } = defineProps<{
  label: string;
  color: colorType;
}>();

const colorClasses = {
  primary: 'bg-primary-accent text-primary dark:bg-primary-accent-dark dark:text-primary-dark',
  success: 'bg-success-accent text-success dark:bg-success-accent-dark dark:text-success-dark',
  warning: 'bg-warning-accent text-warning dark:bg-warning-accent-dark dark:text-warning-dark',
  danger: 'bg-danger-accent text-danger dark:bg-danger-accent-dark dark:text-danger-dark',
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
