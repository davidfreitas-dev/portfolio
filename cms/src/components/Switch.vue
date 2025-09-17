<script setup lang="ts">
import { computed } from 'vue';
import { Switch } from '@headlessui/vue';

const props = defineProps<{
  modelValue: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
}>();

const isChecked = computed({
  get: () => props.modelValue,
  set: (val: boolean) => emit('update:modelValue', val)
});

const onToggle = (val: boolean) => {
  isChecked.value = val;
};
</script>

<template>
  <Switch
    :model-value="isChecked"
    :class="isChecked ? 'bg-primary dark:bg-primary' : 'bg-primary-accent dark:bg-primary-accent-dark'"
    class="relative inline-flex h-[32px] w-[56px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
    @update:model-value="onToggle"
  >
    <span class="sr-only">Ativar configuração</span>
    <span
      aria-hidden="true"
      :class="isChecked ? 'translate-x-6' : 'translate-x-0'"
      class="pointer-events-none inline-block h-[28px] w-[28px] transform rounded-full bg-white dark:bg-gray-200 shadow-lg ring-0 transition duration-200 ease-in-out"
    />
  </Switch>
</template>
