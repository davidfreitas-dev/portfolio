<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Number, // 1 ativo / 0 inativo
    default: 0,
  }
});

const emit = defineEmits(['update:modelValue']);

const isChecked = ref(!!props.modelValue);

watch(() => props.modelValue, (newValue) => {
  isChecked.value = !!newValue;
});

const toggleSwitch = () => {
  isChecked.value = !isChecked.value;
  emit('update:modelValue', isChecked.value ? 1 : 0);
};
</script>

<template>
  <label class="inline-flex items-center cursor-pointer">
    <input
      type="checkbox"
      class="sr-only peer"
      :checked="isChecked"
      @change="toggleSwitch"
    >
    <div class="relative w-11 h-6 bg-secondary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-pressed rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-accent after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary" />
  </label>
</template>
