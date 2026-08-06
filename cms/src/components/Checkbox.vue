<script setup lang="ts">
import { computed } from 'vue';

const emit = defineEmits<{
 (e: 'update:modelValue', value: boolean): void;
}>();

const props = defineProps<{
 modelValue: boolean;
 label?: string;
}>();

const model = computed({
  get() {
    return props.modelValue;
  },
  set(value: boolean) {
    emit('update:modelValue', value);
  },
});
</script>

<template>
  <label class="flex items-center gap-2 cursor-pointer text-base text-gray-700 dark:text-gray-100 select-none">
    <div class="relative flex items-center justify-center h-5.5 w-5.5">
      <input
        v-model="model"
        type="checkbox"
        class="peer absolute inset-0 h-full w-full opacity-0 cursor-pointer"
      >
      <span
        class="h-5.5 w-5.5 rounded border border-gray-300 bg-transparent dark:border-gray-600 dark:bg-gray-800 transition 
 peer-checked:bg-primary-default peer-checked:border-primary-default dark:peer-checked:bg-primary-default dark:peer-checked:border-primary-default"
      />
      <span
        class="absolute h-2.5 w-1.5 rotate-45 border-r-2 border-b-2 mb-0.5 border-white 
 opacity-0 peer-checked:opacity-100 transition"
      />
    </div>
    <span>{{ label }}</span>
  </label>
</template>
