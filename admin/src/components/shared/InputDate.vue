<script setup>
import { ref, onMounted, watch } from 'vue';

const emit = defineEmits(['update:modelValue', 'onKeyupEnter']);

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  dateFormat: {
    type: String,
    default: 'dd/MM/YYYY',
    validator: (value) => ['dd/MM/YYYY', 'MM/YYYY'].includes(value)  // Ensure it's one of the allowed formats
  }
});

const MAX_DIGITS = props.dateFormat === 'dd/MM/YYYY' ? 8 : 6;

const formattedValue = ref('');

const updateFormattedValue = () => {
  formattedValue.value = formatDate(rawValue.value);
};

watch(() => props.modelValue, (newValue) => {
  rawValue.value = String(newValue).replace(/[^\d]/g, '').slice(0, MAX_DIGITS);
  updateFormattedValue();
  emit('update:modelValue', formattedValue.value);  // Emit formatted value
});

const rawValue = ref('');

onMounted(() => {
  rawValue.value = String(props.modelValue).replace(/[^\d]/g, '').slice(0, MAX_DIGITS);
  updateFormattedValue();
  emit('update:modelValue', formattedValue.value);  // Emit formatted value
});

const formatDate = (value) => {
  const cleanValue = value.replace(/[^\d]/g, '').slice(0, MAX_DIGITS);

  if (props.dateFormat === 'dd/MM/YYYY') {
    const day = cleanValue.slice(0, 2);
    const month = cleanValue.slice(2, 4);
    const year = cleanValue.slice(4, 8);
    return [day, month, year].filter(Boolean).join('/');
  } 
  
  if (props.dateFormat === 'MM/YYYY') {
    const month = cleanValue.slice(0, 2);
    const year = cleanValue.slice(2, 6);
    return [month, year].filter(Boolean).join('/');
  }
};

const handleInput = (event) => {
  const inputValue = event.target.value;
  const numericValue = inputValue.replace(/[^\d]/g, '').slice(0, MAX_DIGITS);

  formattedValue.value = formatDate(numericValue);
  rawValue.value = numericValue;

  emit('update:modelValue', formattedValue.value);  // Emit formatted value
};
</script>

<template>
  <label class="text-font font-semibold">
    {{ label }}
  </label>
  <input
    type="text"
    :class="['text-font placeholder:text-gray-300 bg-white border text-base w-full h-[52px] rounded-lg px-4 focus:border-none focus:outline-none focus:ring-2 focus:ring-primary disabled:cursor-not-allowed', { 'mt-2 mb-5': label }]"
    :value="formattedValue"
    :placeholder="dateFormat"
    :disabled="disabled"
    :maxlength="props.dateFormat === 'dd/MM/YYYY' ? 10 : 7"
    @input="handleInput"
    @keyup.enter="$emit('onKeyupEnter')"
  >
</template>
