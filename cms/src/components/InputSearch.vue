<script setup lang="ts">
import Icon from '@/components/Icon.vue';

const emit = defineEmits<{
 (event: 'update:modelValue', value: string): void;
 (event: 'enter'): void;
 (event: 'blur', e: FocusEvent): void;
}>();

const { disabled, label, placeholder, modelValue } = defineProps<{
 modelValue: string;
 label?: string;
 placeholder?: string;
 disabled?: boolean;
}>();

const updateValue = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};
</script>

<template>
  <div class="flex flex-col gap-2 relative w-full">
    <label v-if="label" class="text-gray-700 dark:text-gray-100 font-semibold">{{ label }}</label>

    <div class="relative">
      <input
        type="text"
        :value="modelValue"
        :placeholder="placeholder || ''"
        :disabled="disabled"
        :class="[
          'text-gray-700 dark:text-gray-100 bg-transparent text-[14px] w-full h-[44px] rounded-lg pl-4 pr-10 focus:outline-none focus:ring-1 transition-all duration-200 disabled:cursor-not-allowed',
          'border border-gray-300 dark:border-gray-600 focus:ring-primary-default focus:border-primary-default '
        ]"
        :aria-label="label"
        @input="updateValue"
        @blur="$emit('blur', $event)"
        @keyup.enter="emit('enter')"
      >
      <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-400 dark:text-gray-400 pointer-events-none">
        <Icon name="search" class="w-5 h-5" />
      </div>
    </div>
  </div>
</template>
