<script setup>
const emit = defineEmits(['update:modelValue', 'onKeyupEnter']);

const props = defineProps({
  disabled: {
    type: Boolean
  },
  type: { 
    type: String, 
    default: '' 
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
    type: [ String, Number ], 
    default: null 
  }
});

const updateValue = (event) => {
  emit('update:modelValue', event.target.value);
};
</script>

<template>
  <label class="text-font font-semibold">
    {{ label }}
  </label>
  <input
    :class="['text-font placeholder:text-gray-300 bg-white border text-base w-full h-[52px] rounded-lg px-4 focus:border-none focus:outline-none focus:ring-2 focus:ring-primary disabled:cursor-not-allowed', { 'mt-2 mb-5': label }]"
    :type="type"
    :value="modelValue"
    :placeholder="placeholder"
    :disabled="disabled"
    @input="updateValue"
    @keyup.enter="$emit('onKeyupEnter')"
  >
</template>