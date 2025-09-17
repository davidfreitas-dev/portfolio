<script setup lang="ts">
import { ref, computed } from 'vue';

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void;
  (event: 'enter'): void;
}>();

const props = defineProps<{
  modelValue: string;
  label?: string;
  placeholder?: string;
  floatingLabel?: boolean;
  disabled?: boolean;
}>();

const isFocused = ref(false);

const isFloating = computed(() =>
  props.floatingLabel && (isFocused.value || !!props.modelValue)
);

const labelClasses = computed(() => {
  if (!props.label) return [];

  const base = props.floatingLabel
    ? 'absolute left-4 transition-all duration-200 bg-background dark:bg-background-dark pointer-events-none z-10 text-normal'
    : 'block mb-1.5 text-font dark:text-font-dark font-semibold';

  const color = props.disabled
    ? 'text-disabled dark:text-disabled-dark'
    : props.floatingLabel
      ? isFocused.value
        ? 'text-primary dark:text-primary'
        : 'text-neutral-400'
      : '';

  const position = props.floatingLabel
    ? isFloating.value
      ? '-top-2 text-xs px-1'
      : 'top-1/2 -translate-y-1/2'
    : '';

  return [base, color, position];
});

const inputClasses = computed(() => [
  'w-full h-[52px] rounded-xl border px-4 text-base bg-transparent text-font dark:text-font-dark outline-none focus:outline-none focus:ring-2 transition-all duration-200',
  props.floatingLabel ? 'placeholder-transparent' : '',
  isFocused.value
    ? 'border-neutral dark:border-neutral-dark focus:ring-primary dark:focus:ring-primary'
    : 'border-neutral dark:border-neutral-dark',
  props.disabled ? 'cursor-not-allowed opacity-60' : ''
]);

const updateValue = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};
</script>

<template>
  <div class="relative w-full">
    <label v-if="label" :class="labelClasses">{{ label }}</label>

    <input
      type="text"
      :value="modelValue"
      :placeholder="placeholder || ''"
      :disabled="disabled"
      :class="inputClasses"
      :aria-label="label"
      @input="updateValue"
      @focus="isFocused = true"
      @blur="isFocused = false"
      @keyup.enter="emit('enter')"
    >
  </div>
</template>
