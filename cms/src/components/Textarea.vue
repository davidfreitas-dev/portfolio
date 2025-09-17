<script setup lang="ts">
import { type PropType } from 'vue';

const emit = defineEmits<{
  (event: 'update:modelValue', value: string | number): void;
  (event: 'onKeyupEnter'): void;
}>();

const { disabled, label, placeholder, modelValue, error } = defineProps({
  disabled: {
    type: Boolean,
    default: false
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number] as PropType<string | number>,
    default: null
  },
  error: {
    type: String,
    default: ''
  }
});

const updateValue = (event: Event) => {
  const textarea = event.target as HTMLTextAreaElement;
  emit('update:modelValue', textarea.value);
};
</script>

<template>
  <div class="flex flex-col gap-2 relative">
    <label v-if="label" class="text-font dark:text-font-dark font-semibold">{{ label }}</label>

    <textarea
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      rows="4"
      :class="[
        'text-font dark:text-font-dark bg-transparent text-base w-full rounded-xl px-4 py-3 resize-none focus:outline-none focus:ring-2 disabled:cursor-not-allowed',
        error
          ? 'border border-danger focus:ring-danger'
          : 'border border-neutral dark:border-neutral-dark focus:ring-primary dark:focus:ring-primary'
      ]"
      @input="updateValue"
      @keyup.enter="$emit('onKeyupEnter')"
    />

    <span v-if="error" class="text-sm text-danger">{{ error }}</span>
  </div>
</template>
